<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\BoostOrderEmail;
use App\Models\BoostOrder;
use App\Models\BoostService;
use App\Models\Favorite;
use App\Models\Setting;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\JustAnotherPanelService;
use App\Services\PromoService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BoostController extends Controller
{
    private function resolveMarkupForCategory(string $category): float
    {
        $global = (float) (Setting::where('key', 'boost_markup_percent')->value('value') ?? 20);
        $raw = (string) (Setting::where('key', 'boost_markup_by_category')->value('value') ?? '');
        if ($raw === '') {
            return $global;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $global;
        }

        if (array_key_exists($category, $decoded) && is_numeric($decoded[$category])) {
            $v = (float) $decoded[$category];
            return $v < 0 ? 0.0 : $v;
        }

        return $global;
    }

    public function index()
    {
        $markup = (float) (Setting::where('key', 'boost_markup_percent')->value('value') ?? 20);

        $services = BoostService::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($service) use ($markup) {
                $category = (string) ($service->category ?: 'Other');
                $effectiveMarkup = $this->resolveMarkupForCategory($category);
                return [
                    'id' => $service->id,
                    'jap_service_id' => $service->jap_service_id,
                    'name' => $service->name,
                    'category' => $service->category ?: 'Other',
                    'type' => $service->type,
                    'min' => (int) $service->min,
                    'max' => (int) $service->max,
                    'rate' => (float) $service->finalRate($effectiveMarkup),
                ];
            });

        $favoriteBoostIds = Favorite::where('user_id', auth()->id())
            ->where('type', 'boost')
            ->pluck('key')
            ->map(fn ($v) => (int) $v)
            ->values();

        $orders = auth()->user()->boostOrders()->with('service')->latest()->paginate(10)->withQueryString();

        return view('user.boosting', compact('services', 'orders', 'markup', 'favoriteBoostIds'));
    }

    public function placeOrder(Request $request, JustAnotherPanelService $jap, WalletService $wallet)
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('boosting')) {
            $msg = $serviceManager->getMaintenanceMessage('boosting');
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'boost_service_id' => 'required|integer|exists:boost_services,id',
            'link' => 'required|url|max:2048',
            'quantity' => 'required|integer|min:1',
            'promo_code' => 'nullable|string|max:40',
        ]);

        $userId = auth()->id();
        $lock = \Illuminate\Support\Facades\Cache::lock("purchase:user:{$userId}", 300);
        if (! $lock->get()) {
            return back()->with('error', 'You have a purchase in progress. Please wait for it to complete.');
        }

        try {
            $user = auth()->user();
            $service = BoostService::where('is_active', true)->findOrFail($request->boost_service_id);

            $quantity = (int) $request->quantity;
            if ($service->min > 0 && $quantity < (int) $service->min) {
                return back()->with('error', "Minimum quantity for this service is {$service->min}.");
            }
            if ($service->max > 0 && $quantity > (int) $service->max) {
                return back()->with('error', "Maximum quantity for this service is {$service->max}.");
            }

            $category = (string) ($service->category ?: 'Other');
            $markup = $this->resolveMarkupForCategory($category);
            $finalRate = $service->finalRate($markup);
            $charge = ($quantity / 1000) * $finalRate;

            $reason = null;
            $promo = app(PromoService::class)->resolveEligiblePromo($request->promo_code, $user, 'boost', $reason);
            if (! $promo && trim((string) $request->promo_code) !== '') {
                return back()->with('error', $reason ?: 'Invalid promo code.');
            }

            $discount = $promo ? app(PromoService::class)->calculateDiscountUsd($promo, (float) $charge) : 0.0;
            $netCharge = round((float) $charge - (float) $discount, 2);
            if ($user->walletBalance < $netCharge) {
                return back()->with('error', 'Insufficient funds. You need $'.number_format($netCharge, 2));
            }

            $apiResult = $jap->addOrder($service->jap_service_id, $request->link, $quantity);
            if (! is_array($apiResult) || isset($apiResult['error']) || ! isset($apiResult['order'])) {
                $errorMessage = is_array($apiResult) && isset($apiResult['error']) ? (string) $apiResult['error'] : 'Provider returned an unexpected response.';
                $errorType = is_array($apiResult) && isset($apiResult['error_type']) ? (string) $apiResult['error_type'] : 'other';

                $isServiceError = in_array($errorType, ['service', 'http', 'unknown', 'exception'], true);
                $deactivated = $service->recordFailure($errorMessage, $isServiceError);

                $userMsg = $this->buildUserErrorMessage($errorType, $errorMessage, $deactivated, $service);
                Log::warning("Boost order failed user_id={$user->id} service_id={$service->id} type={$errorType} deactivated=".($deactivated ? 'yes' : 'no').' msg='.$errorMessage);

                return back()->with('error', $userMsg);
            }

            $order = null;

            DB::transaction(function () use ($jap, $request, $quantity, $service, $user, $wallet, $netCharge, $promo, $discount, &$order, $apiResult) {
                if ($netCharge > 0) {
                    $wallet->debit($user, (float) $netCharge, 'boost_debit', 'BOOST-'.(string) $apiResult['order'], [
                        'boost_service_id' => $service->id,
                        'quantity' => $quantity,
                        'link' => (string) $request->link,
                        'promo_code' => $promo ? $promo->code : null,
                        'promo_discount_usd' => (float) $discount,
                    ]);
                }

                $order = BoostOrder::create([
                    'user_id' => $user->id,
                    'boost_service_id' => $service->id,
                    'promo_code_id' => $promo ? $promo->id : null,
                    'jap_order_id' => (int) $apiResult['order'],
                    'link' => $request->link,
                    'quantity' => $quantity,
                    'charge' => $netCharge,
                    'promo_discount_usd' => (float) $discount,
                    'status' => 'processing',
                ]);

                if ($promo && $discount > 0) {
                    app(PromoService::class)->redeemForOrder($user, $promo, 'boost', BoostOrder::class, (string) $order->id, (float) $discount);
                }
            });

            $service->recordSuccess();

            $user->notify(new SimpleDatabaseNotification(
                'Boost order placed',
                'Your boost order is now processing.',
                '/orders?type=boost'
            ));

            try {
                Mail::to($user->email)->send(new BoostOrderEmail($user, $order));
            } catch (\Exception $e) {
                Log::error('Boost Order Email failed to send: '.$e->getMessage());
            }

            return back()->with('success', 'Boost order placed successfully.');
        } catch (\Throwable $e) {
            Log::error('Unexpected boost placeOrder error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'An error occurred while placing your order. Please try again in a moment.');
        } finally {
            $lock->release();
        }
    }

    private function buildUserErrorMessage(string $errorType, string $providerError, bool $deactivated, BoostService $service): string
    {
        switch ($errorType) {
            case 'service':
                if ($deactivated) {
                    return "This service ({$service->name}) is temporarily unavailable and has been removed from our catalog. Our team has been notified. Please choose a different service.";
                }

                return "This service ({$service->name}) is currently unavailable at our provider. Please try again later or choose a different service.";
            case 'link':
                return "The link you provided could not be used for this service. Double-check that it is a valid public link (e.g., public Instagram post/TikTok video) and try again. Provider message: ".e($providerError);
            case 'quantity':
                return "The quantity you selected is not accepted for this service. Please adjust it within the shown Min/Max range and try again. Provider message: ".e($providerError);
            case 'balance':
                return 'Our provider is experiencing an internal issue. This is not related to your wallet balance. Please try again in a few minutes.';
            case 'connection':
            case 'http':
                if ($deactivated) {
                    return "Our service provider is experiencing issues with this specific service right now. It has been temporarily hidden. Please choose a different service or try again later.";
                }

                return 'Our service provider is temporarily unavailable. Please try again in a few minutes.';
            case 'exception':
                if ($deactivated) {
                    return "This service is temporarily unavailable. It has been hidden from our catalog and our team is investigating. Please choose a different service.";
                }

                return 'We could not reach our service provider right now. Please try again shortly.';
            default:
                if ($deactivated) {
                    return "This service had too many recent failures and has been temporarily removed. Please choose a different service or try again later.";
                }

                return 'We were unable to place your order at this time. Please try again in a moment.';
        }
    }

    public function checkOrder(JustAnotherPanelService $jap, $id)
    {
        $order = BoostOrder::where('user_id', auth()->id())->findOrFail($id);

        if (! $order->jap_order_id) {
            return response()->json(['error' => 'Order not linked to provider'], 400);
        }

        $apiResult = $jap->getOrderStatus($order->jap_order_id);
        if (! is_array($apiResult) || ! isset($apiResult['status'])) {
            return response()->json(['error' => 'Failed to fetch status'], 400);
        }

        $order->status = (string) $apiResult['status'];
        $order->charge = isset($apiResult['charge']) ? (float) $apiResult['charge'] : $order->charge;
        $order->start_count = isset($apiResult['start_count']) ? (int) $apiResult['start_count'] : $order->start_count;
        $order->remains = isset($apiResult['remains']) ? (int) $apiResult['remains'] : $order->remains;
        $order->save();

        $remains = $order->remains;
        $progress = null;
        if ($remains !== null && $order->quantity > 0) {
            $progress = max(0, min(100, (int) round((($order->quantity - $remains) / $order->quantity) * 100)));
        }

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'start_count' => $order->start_count,
            'remains' => $order->remains,
            'progress' => $progress,
        ]);
    }
}
