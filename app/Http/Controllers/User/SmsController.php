<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\SmsPurchaseEmail;
use App\Models\Setting;
use App\Models\SmsCountry;
use App\Models\SmsOrder;
use App\Models\WalletTransaction;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\FiveSimService;
use App\Services\DaisySmsService;
use App\Services\HeroSmsService;
use App\Services\PromoService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmsController extends Controller
{
    protected FiveSimService $fiveSim;
    protected DaisySmsService $daisySms;
    protected HeroSmsService $heroSms;
    protected string $activeProvider;

    public function __construct(FiveSimService $fiveSim, DaisySmsService $daisySms, HeroSmsService $heroSms)
    {
        $this->fiveSim = $fiveSim;
        $this->daisySms = $daisySms;
        $this->heroSms = $heroSms;
        $this->activeProvider = (string) (Setting::where('key', 'sms_provider')->value('value') ?? 'herosms');
    }

    private function resolveSmsProvider(?SmsOrder $order = null, ?string $requestedProvider = null): string
    {
        $provider = strtolower((string) ($requestedProvider ?: ($order?->provider ?: $this->activeProvider)));

        if (in_array($provider, ['herosms', 'hero'], true)) {
            return 'herosms';
        }
        if (in_array($provider, ['daisysms', 'daisy'], true)) {
            return 'daisysms';
        }

        return '5sim';
    }

    private function resolveSmsService(?SmsOrder $order = null, ?string $requestedProvider = null)
    {
        $p = $this->resolveSmsProvider($order, $requestedProvider);

        if ($p === 'daisysms') {
            return $this->daisySms;
        }
        if ($p === 'herosms') {
            return $this->heroSms;
        }

        return $this->fiveSim;
    }

    private function resolveSmsMarkupPercent(string $country, string $product): float
    {
        $global = (float) (Setting::where('key', 'sms_markup_percent')->value('value') ?? 20);

        $cKey = strtolower(trim($country));
        $pKey = strtolower(trim($product));

        // 1. Resolve Country Markup (use country override if set, else fallback to global)
        $countryMarkup = null;
        $countryRaw = (string) (Setting::where('key', 'sms_markup_by_country')->value('value') ?? '');
        if ($countryRaw !== '') {
            $decoded = json_decode($countryRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $k => $v) {
                    if (strtolower(trim((string) $k)) === $cKey && is_numeric($v)) {
                        $countryMarkup = (float) $v;
                        break;
                    }
                }
            }
        }
        $baseMarkup = $countryMarkup !== null ? $countryMarkup : $global;

        // 2. Resolve Service Markup (additional percentage for specific service)
        $serviceMarkup = 0.0;
        $serviceRaw = (string) (Setting::where('key', 'sms_markup_by_service')->value('value') ?? '');
        if ($serviceRaw !== '') {
            $decoded = json_decode($serviceRaw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $k => $v) {
                    if (strtolower(trim((string) $k)) === $pKey && is_numeric($v)) {
                        $serviceMarkup = (float) $v;
                        break;
                    }
                }
            }
        }

        $totalMarkup = $baseMarkup + $serviceMarkup;

        return $totalMarkup < 0 ? 0.0 : $totalMarkup;
    }

    private function smsPremiumEnabled(): bool
    {
        return (int) (Setting::where('key', 'sms_premium_enabled')->value('value') ?? 1) === 1;
    }

    private function smsPremiumMarkupPercent(): float
    {
        $v = (float) (Setting::where('key', 'sms_premium_markup_percent')->value('value') ?? 40);
        return $v < 0 ? 0.0 : $v;
    }

    public function index()
    {
        $orders = auth()->user()->smsOrders()->latest()->paginate(10)->withQueryString();
        $countries = SmsCountry::where('is_active', true)->orderBy('name')->get();

        $herosmsEnabled = (int) (Setting::where('key', 'sms_provider_herosms_enabled')->value('value') ?? 1) === 1;
        $daisysmsEnabled = (int) (Setting::where('key', 'sms_provider_daisysms_enabled')->value('value') ?? 1) === 1;
        $fivesimEnabled = (int) (Setting::where('key', 'sms_provider_5sim_enabled')->value('value') ?? 1) === 1;

        $premiumEnabled = $this->smsPremiumEnabled();
        $premiumMarkupPercent = $this->smsPremiumMarkupPercent();
        $activeProvider = $this->activeProvider;

        return view('user.sms', compact(
            'orders',
            'countries',
            'premiumEnabled',
            'premiumMarkupPercent',
            'herosmsEnabled',
            'daisysmsEnabled',
            'fivesimEnabled',
            'activeProvider'
        ));
    }

    private function applyMinPriceFloor(float $costInUsd, string $country = '', string $product = ''): float
    {
        $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
        if ($ngnPerUsd <= 0) {
            $ngnPerUsd = 1500.0;
        }

        $c = strtolower(trim($country));
        $p = strtolower(trim($product));

        // Fixed price override for Singapore WhatsApp: 20,000 NGN
        if (($c === 'singapore' || $c === 'sg') && ($p === 'whatsapp' || $p === 'wa')) {
            return round(20000.0 / $ngnPerUsd, 2);
        }

        $costInNgn = $costInUsd * $ngnPerUsd;

        if ($costInNgn < 1000.0) {
            return round(1500.0 / $ngnPerUsd, 2);
        }

        return round($costInUsd, 2);
    }

    public function getServices(Request $request)
    {
        $requestedProvider = $request->input('provider');
        $smsProvider = $this->resolveSmsProvider(null, $requestedProvider);
        $country = strtolower((string) $request->input('country', 'usa'));

        if ($smsProvider === 'daisysms') {
            $country = 'usa';
        }

        $prices = $this->resolveSmsService(null, $smsProvider)->getPrices($country);
        $premiumEnabled = $this->smsPremiumEnabled();
        $premiumMultiplier = 1 + ($this->smsPremiumMarkupPercent() / 100);

        if (isset($prices[$country])) {
            $countryData = $prices[$country];

            // Apply markup & minimum 1500 NGN floor (and Singapore WhatsApp 20,000 NGN override)
            foreach ($countryData as $service => &$operators) {
                $markupPercent = $this->resolveSmsMarkupPercent((string) $country, (string) $service);
                $multiplier = 1 + ($markupPercent / 100);
                foreach ($operators as &$details) {
                    if (isset($details['cost'])) {
                        // Round up to 2 decimal places & apply price rules
                        $baseCost = round(((float) $details['cost']) * $multiplier, 2);
                        $details['cost'] = $this->applyMinPriceFloor($baseCost, (string) $country, (string) $service);
                        if ($premiumEnabled) {
                            $premCost = round(((float) $details['cost']) * $premiumMultiplier, 2);
                            $details['premium_cost'] = $this->applyMinPriceFloor($premCost, (string) $country, (string) $service);
                        }
                    }
                }
            }

            return response()->json($countryData);
        }

        return response()->json([]);
    }

    public function getPriceTiers(Request $request)
    {
        $smsProvider = $this->resolveSmsProvider(null, $request->input('provider'));
        if ($smsProvider !== 'herosms') {
            return response()->json([]);
        }

        $country = strtolower((string) $request->input('country', 'usa'));
        $service = strtolower(trim((string) $request->input('service', '')));
        if ($service === '') {
            return response()->json([]);
        }

        $prices = $this->heroSms->getPrices($country);
        $baseEntry = $prices[$country][$service]['any'] ?? null;
        if (!is_array($baseEntry) || !isset($baseEntry['cost'])) {
            return response()->json([]);
        }

        $markupPercent = $this->resolveSmsMarkupPercent($country, $service);
        $multiplier = 1 + ($markupPercent / 100);
        $baseProviderCost = (float) $baseEntry['cost'];
        $baseUserCost = $this->applyMinPriceFloor(round($baseProviderCost * $multiplier, 2), $country, $service);

        $tiers = [
            [
                'id' => 'standard',
                'label' => 'Standard',
                'max_price' => null,
                'provider_cost' => $baseProviderCost,
                'cost' => $baseUserCost,
                'count' => (int) ($baseEntry['count'] ?? 0),
            ],
        ];

        $rawTiers = $this->heroSms->getPriceTiers($country, $service);
        $higherCandidates = [];
        $lastUserCost = $baseUserCost;

        foreach ($rawTiers as $tier) {
            $p = (float) ($tier['price'] ?? 0);
            if ($p <= $baseProviderCost) {
                continue;
            }
            $userCost = $this->applyMinPriceFloor(round($p * $multiplier, 2), $country, $service);
            if ($userCost <= $lastUserCost) {
                continue;
            }
            $higherCandidates[] = [
                'provider_cost' => $p,
                'cost' => $userCost,
                'count' => (int) ($tier['count'] ?? 0),
            ];
            $lastUserCost = $userCost;
        }

        if (!empty($higherCandidates)) {
            $picked = [];
            $total = count($higherCandidates);
            if ($total <= 4) {
                $picked = $higherCandidates;
            } else {
                $indices = array_unique([
                    0,
                    (int) floor(($total - 1) / 3),
                    (int) floor((2 * ($total - 1)) / 3),
                    $total - 1,
                ]);
                foreach ($indices as $idx) {
                    $picked[] = $higherCandidates[$idx];
                }
            }

            $labels = ['Fast Priority (Default)', 'High Priority', 'Express Priority', 'Instant / Max Priority'];
            foreach ($picked as $i => $item) {
                $tiers[] = [
                    'id' => (string) $item['provider_cost'],
                    'label' => $labels[$i] ?? ('Priority Tier ' . ($i + 1)),
                    'max_price' => $item['provider_cost'],
                    'provider_cost' => $item['provider_cost'],
                    'cost' => $item['cost'],
                    'count' => $item['count'],
                ];
            }
        }

        return response()->json($tiers);
    }

    public function buyNumber(Request $request, WalletService $wallet)
    {
        $serviceManager = app(\App\Services\ServiceManager::class);
        if (!$serviceManager->isAvailable('sms_verify')) {
            return response()->json([
                'success' => false,
                'message' => $serviceManager->getMaintenanceMessage('sms_verify'),
            ], 403);
        }

        $request->validate([
            'provider' => 'nullable|string',
            'country' => 'required|string',
            'product' => 'required|string',
            'operator' => 'required|string',
            'price' => 'required|numeric',
            'max_price' => 'nullable|numeric|min:0',
            'promo_code' => 'nullable|string|max:40',
            'is_premium' => 'nullable|boolean',
        ]);

        $userId = auth()->id();
        $lock = \Illuminate\Support\Facades\Cache::lock("purchase:user:{$userId}", 300);
        if (! $lock->get()) {
            return response()->json(['success' => false, 'message' => 'You have a purchase in progress. Please wait for it to complete.'], 400);
        }

        try {
            $user = auth()->user();
            $requestedProvider = $request->input('provider');
            $smsProvider = $this->resolveSmsProvider(null, $requestedProvider);
            $smsService = $this->resolveSmsService(null, $smsProvider);

            $country = strtolower((string) $request->country);
            if ($smsProvider === 'daisysms') {
                $country = 'usa';
            }
        $product = (string) $request->product;
        $operator = (string) $request->operator;
        $maxPrice = ($smsProvider === 'herosms' && $request->filled('max_price') && (float) $request->input('max_price') > 0)
            ? (float) $request->input('max_price')
            : null;

        $premiumEnabled = $this->smsPremiumEnabled();
        $premiumPercent = $this->smsPremiumMarkupPercent();
        $isPremium = $premiumEnabled && (bool) $request->boolean('is_premium');

        $price = null;
        try {
            $prices = $smsService->getPrices($country);
            $providerCost = $prices[$country][$product][$operator]['cost']
                ?? $prices[$country][$product]['any']['cost']
                ?? null;
            if ($maxPrice !== null) {
                $providerCost = is_numeric($providerCost) ? max((float) $providerCost, $maxPrice) : $maxPrice;
            }
            if (is_numeric($providerCost)) {
                $markupPercent = $this->resolveSmsMarkupPercent($country, $product);
                $estimated = round(((float) $providerCost) * (1 + ($markupPercent / 100)), 2);
                if ($isPremium) {
                    $estimated = round($estimated * (1 + ($premiumPercent / 100)), 2);
                }
                $price = $this->applyMinPriceFloor($estimated, $country, $product);
            }
        } catch (\Throwable $e) {
        }

        if ($price === null) {
            $price = $this->applyMinPriceFloor((float) $request->price, $country, $product);
        }

        $reason = null;
        $promo = app(PromoService::class)->resolveEligiblePromo($request->promo_code, $user, 'sms', $reason);
        if (! $promo && trim((string) $request->promo_code) !== '') {
            return response()->json(['success' => false, 'message' => $reason ?: 'Invalid promo code.'], 400);
        }
        $discountEstimate = $promo ? app(PromoService::class)->calculateDiscountUsd($promo, (float) $price) : 0.0;
        $netEstimate = round((float) $price - (float) $discountEstimate, 2);

        if ($user->walletBalance < $netEstimate) {
            return response()->json(['success' => false, 'message' => 'Insufficient wallet balance.'], 400);
        }

        $response = $smsProvider === 'herosms'
            ? $smsService->buyNumber($country, $operator, $product, $maxPrice)
            : $smsService->buyNumber($country, $operator, $product);

        if (isset($response['id']) && isset($response['phone'])) {
            $markupPercent = $this->resolveSmsMarkupPercent($country, $product);
            $multiplier = 1 + ($markupPercent / 100);
            $finalPrice = round(((float) $response['price']) * $multiplier, 2);
            if ($isPremium) {
                $finalPrice = round($finalPrice * (1 + ($premiumPercent / 100)), 2);
            }
            $finalPrice = $this->applyMinPriceFloor($finalPrice, $country, $product);
            $discount = $promo ? app(PromoService::class)->calculateDiscountUsd($promo, (float) $finalPrice) : 0.0;
            $netPrice = round((float) $finalPrice - (float) $discount, 2);

            if ($user->walletBalance < $netPrice) {
                return response()->json(['success' => false, 'message' => 'Insufficient wallet balance.'], 400);
            }

            $order = null;
            DB::beginTransaction();
            try {
                $order = SmsOrder::create([
                    'user_id' => $user->id,
                    'promo_code_id' => $promo ? $promo->id : null,
                    'provider' => $smsProvider,
                    'order_id' => $response['id'],
                    'phone_number' => $response['phone'],
                    'country' => $request->country,
                    'operator' => $response['operator'] ?? $request->operator,
                    'product' => $response['product'] ?? $request->product,
                    'price' => $netPrice,
                    'promo_discount_usd' => (float) $discount,
                    'status' => 'PENDING',
                ]);

                if ($promo && $discount > 0) {
                    app(PromoService::class)->redeemForOrder($user, $promo, 'sms', SmsOrder::class, (string) $order->id, (float) $discount);
                }

                if ($netPrice > 0) {
                    $wallet->debit($user, (float) $netPrice, 'sms_debit', 'SMSORDER-'.(string) $order->id, [
                        'sms_order_id' => $order->id,
                        'country' => (string) $request->country,
                        'operator' => (string) ($response['operator'] ?? $request->operator),
                        'product' => (string) ($response['product'] ?? $request->product),
                        'is_premium' => $isPremium,
                        'premium_markup_percent' => $isPremium ? (float) $premiumPercent : 0.0,
                        'promo_code' => $promo ? $promo->code : null,
                        'promo_discount_usd' => (float) $discount,
                    ]);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $msg = $e->getMessage() === 'insufficient_funds' ? 'Insufficient wallet balance.' : 'Could not complete purchase.';
                $code = $e->getMessage() === 'insufficient_funds' ? 400 : 500;

                return response()->json(['success' => false, 'message' => $msg], $code);
            }

            $user->notify(new SimpleDatabaseNotification(
                'SMS number purchased',
                'Your SMS number purchase was successful.',
                '/orders?type=sms'
            ));

            try {
                Mail::to($user->email)->send(new SmsPurchaseEmail($user, $order));
            } catch (\Exception $e) {
                Log::error('SMS Purchase Email failed to send: '.$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'order' => $order,
                'message' => 'Number purchased successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['error'] ?? 'Failed to purchase number from provider.',
        ], 400);
        } finally {
            $lock->release();
        }
    }

    public function checkSms(Request $request, $id)
    {
        $order = SmsOrder::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $isTerminal = in_array(strtoupper($order->status), ['RECEIVED', 'COMPLETED', 'CANCELED', 'CANCELLED', 'TIMEOUT', 'REFUNDED'], true);
        if ($isTerminal) {
            $canonicalStatus = (strtoupper($order->status) === 'COMPLETED' || strtoupper($order->status) === 'RECEIVED') ? 'RECEIVED' : (strtoupper($order->status) === 'CANCELLED' ? 'CANCELED' : strtoupper($order->status));

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => $canonicalStatus,
                    'sms_code' => $order->sms_code,
                ]);
            }

            return back()->with('info', 'This order is already '.$order->status);
        }

        $smsService = $this->resolveSmsService($order);
        $statusData = $smsService->checkSms($order->order_id);

        if (! $statusData) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'PENDING']);
            }

            return back()->withErrors(['error' => 'Failed to check order status with provider.']);
        }

        $newStatus = strtoupper((string) ($statusData['status'] ?? ''));

        if ($newStatus === 'FINISHED' || $newStatus === 'RECEIVED') {
            $sms = $statusData['sms'] ?? null;
            $extractedCode = null;

            if (is_array($sms) && ! empty($sms)) {
                $first = $sms[0] ?? [];
                $codeCandidate = $first['code'] ?? $first['text'] ?? null;
                if (is_string($codeCandidate) && $codeCandidate !== '') {
                    if (preg_match('/(\d{3,8})/', $codeCandidate, $m)) {
                        $extractedCode = $m[1];
                    } else {
                        $extractedCode = trim($codeCandidate);
                    }
                }
            }

            if ($extractedCode === null && ! empty($statusData['sms_code'])) {
                if (preg_match('/(\d{3,8})/', (string) $statusData['sms_code'], $m2)) {
                    $extractedCode = $m2[1];
                } else {
                    $extractedCode = trim((string) $statusData['sms_code']);
                }
            }

            if ($extractedCode === null) {
                Log::warning("SMS order {$order->id} reported RECEIVED but no code extracted", ['statusData' => $statusData]);
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'PENDING']);
                }
                return back()->with('info', 'Still waiting for SMS. Please check again in a few seconds.');
            }

            $order->sms_code = $extractedCode;
            $order->status = 'RECEIVED';
            $order->save();

            try {
                if (method_exists($smsService, 'finishOrder')) {
                    $smsService->finishOrder($order->order_id);
                }
            } catch (\Throwable $e) {
                Log::warning("finishOrder failed for sms_order={$order->id}: ".$e->getMessage());
            }

            $user = auth()->user();
            $user->notify(new SimpleDatabaseNotification(
                'SMS code received',
                "Your verification code for {$order->product} is ready.",
                '/sms-verify'
            ));

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'RECEIVED',
                    'sms_code' => $order->sms_code,
                ]);
            }

            return back()->with('success', 'SMS Code Received!');
        } elseif ($newStatus === 'CANCELED' || $newStatus === 'CANCELLED' || $newStatus === 'TIMEOUT') {
            return $this->performSmsCancelAndRefund($order, $newStatus, $request->wantsJson());
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'PENDING']);
        }

        return back()->with('info', 'Still waiting for SMS. Please check again in a few seconds.');
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = SmsOrder::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $stillPending = in_array(strtoupper($order->status), ['PENDING', 'WAITING'], true);
        if (! $stillPending) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This order cannot be canceled.']);
            }
            return back()->with('error', 'This order cannot be canceled.');
        }

        $createdAt = $order->created_at;
        $secondsElapsed = $createdAt ? $createdAt->diffInSeconds(now()) : 999;

        if ($secondsElapsed < 120) {
            $waitLeft = 120 - $secondsElapsed;
            $msg = "Please wait {$waitLeft} more second(s) before canceling to allow SMS delivery.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $eligible = $createdAt && $createdAt->diffInMinutes(now()) <= 20;
        if (! $eligible) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cancel window has expired.']);
            }
            return back()->with('error', 'Cancel window has expired.');
        }

        $providerCanceled = false;
        try {
            $smsService = $this->resolveSmsService($order);
            if (method_exists($smsService, 'cancelOrder')) {
                $r = $smsService->cancelOrder($order->order_id);
                $providerCanceled = is_array($r) && ! empty($r['success']);
            }
        } catch (\Throwable $e) {
            Log::warning("User-initiated cancel SMS {$order->id} provider call failed: ".$e->getMessage());
        }

        $result = $this->performSmsCancelAndRefund($order, 'CANCELED', $request->wantsJson(), true, $providerCanceled);

        return $result;
    }

    private function performSmsCancelAndRefund(SmsOrder $order, string $reportedStatus, bool $wantsJson, bool $userInitiated = false, bool $providerCanceled = false)
    {
        $alreadyCanceled = in_array(strtoupper($order->status), ['CANCELED', 'CANCELLED', 'TIMEOUT', 'REFUNDED'], true);
        if (! $alreadyCanceled) {
            $order->status = 'CANCELED';
            $order->save();
        }

        $refunded = false;
        $refundReference = 'SMSREFUND-'.(string) $order->id;
        $refundExists = WalletTransaction::where('user_id', $order->user_id)
            ->where('type', 'sms_refund')
            ->where('reference', $refundReference)
            ->exists();

        if (! $refundExists && (float) $order->price > 0) {
            try {
                $user = $order->user;
                if ($user) {
                    $wallet = app(WalletService::class);
                    $wallet->credit($user, (float) $order->price, 'sms_refund', $refundReference, [
                        'sms_order_id' => $order->id,
                        'user_initiated' => $userInitiated,
                        'provider_canceled' => $providerCanceled,
                        'provider' => $this->resolveSmsProvider($order),
                        'reported_status' => $reportedStatus,
                    ]);
                    $refunded = true;

                    $user->notify(new SimpleDatabaseNotification(
                        'SMS order refunded',
                        'Your SMS order was canceled and your funds were refunded.',
                        '/orders?type=sms'
                    ));
                }
            } catch (\Throwable $e) {
                Log::error("SMS refund failed for order {$order->id}: ".$e->getMessage());
            }
        }

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'status' => 'CANCELED',
                'refunded' => $refunded,
            ]);
        }

        return back()->with('error', 'Order canceled. Funds refunded.');
    }
}
