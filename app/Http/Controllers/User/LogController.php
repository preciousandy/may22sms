<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\LogPurchaseEmail;
use App\Models\LogCart;
use App\Models\LogItem;
use App\Models\LogOrder;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\PromoService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LogController extends Controller
{
    public function index()
    {
        $logItems = LogItem::where('is_active', true)
            ->withCount(['credentials as available_stock' => function ($query) {
                $query->where('is_sold', false);
            }])
            ->orderBy('platform')
            ->get()
            ->groupBy('platform');

        $categoryVideos = \App\Models\LogCategoryVideo::where('is_active', true)
            ->latest()
            ->get()
            ->groupBy('platform');

        $cartCount = LogCart::where('user_id', auth()->id())->count();
        $orders = auth()->user()->logOrders()->with('credentials.item')->latest()->paginate(10)->withQueryString();

        return view('user.logs', compact('logItems', 'orders', 'cartCount', 'categoryVideos'));
    }


    public function cart()
    {
        $cartItems = LogCart::where('user_id', auth()->id())
            ->with('item')
            ->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->item->price;
        });

        return view('user.logs-cart', compact('cartItems', 'total'));
    }

    public function addToCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = LogItem::where('is_active', true)->findOrFail($id);
        if ((bool) ($item->is_out_of_stock ?? false)) {
            return back()->with('error', 'This item is currently out of stock.');
        }
        $availableStock = $item->credentials()->where('is_sold', false)->count();

        if ($request->quantity > $availableStock) {
            return back()->with('error', "Only {$availableStock} accounts available in stock.");
        }

        $cart = LogCart::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'log_item_id' => $item->id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return back()->with('success', "Added {$request->quantity} {$item->title} to cart.");
    }

    public function removeFromCart($id)
    {
        LogCart::where('user_id', auth()->id())
            ->where('log_item_id', $id)
            ->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    public function checkout(Request $request, WalletService $wallet)
    {
        $request->validate([
            'promo_code' => 'nullable|string|max:40',
        ]);

        $userId = auth()->id();
        $lock = \Illuminate\Support\Facades\Cache::lock("purchase:user:{$userId}", 300);
        if (! $lock->get()) {
            return back()->with('error', 'You have a purchase in progress. Please wait for it to complete.');
        }

        try {
            $user = auth()->user();
            $cartItems = LogCart::where('user_id', $user->id)->with('item')->get();

            if ($cartItems->isEmpty()) {
                return back()->with('error', 'Your cart is empty.');
            }

            $total = 0;
            foreach ($cartItems as $cartItem) {
                if ((bool) ($cartItem->item->is_out_of_stock ?? false)) {
                    return back()->with('error', "{$cartItem->item->title} is currently out of stock.");
                }

                $availableStock = $cartItem->item->credentials()->where('is_sold', false)->count();
                if ($cartItem->quantity > $availableStock) {
                    return back()->with('error', "Not enough stock for {$cartItem->item->title}. Only {$availableStock} available.");
                }

                $total += $cartItem->quantity * $cartItem->item->price;
            }

            $reason = null;
            $promo = app(PromoService::class)->resolveEligiblePromo($request->promo_code, $user, 'logs', $reason);
            if (! $promo && trim((string) $request->promo_code) !== '') {
                return back()->with('error', $reason ?: 'Invalid promo code.');
            }

            $promoDiscountTotal = 0.0;
            if ($promo) {
                $promoDiscountTotal = app(PromoService::class)->calculateDiscountUsd($promo, (float) $total);
            }

            $netTotal = max(0, (float) $total - (float) $promoDiscountTotal);
            if ($user->walletBalance < $netTotal) {
                return back()->with('error', 'Insufficient funds. You need $'.number_format($netTotal, 2));
            }

            DB::beginTransaction();

            try {
                $firstOrderId = null;
                $remainingDiscount = (float) $promoDiscountTotal;
                $totalFloat = (float) $total;

                foreach ($cartItems as $cartItem) {
                    $itemTotal = (float) ($cartItem->quantity * $cartItem->item->price);

                    $discountForThisOrder = 0.0;
                    if ($promo && $promoDiscountTotal > 0 && $remainingDiscount > 0 && $totalFloat > 0) {
                        $discountForThisOrder = round(((float) $promoDiscountTotal) * ($itemTotal / $totalFloat), 2);
                        $discountForThisOrder = min($itemTotal, min($remainingDiscount, $discountForThisOrder));
                        $remainingDiscount = round($remainingDiscount - $discountForThisOrder, 2);
                    }

                    $netItemTotal = round($itemTotal - $discountForThisOrder, 2);

                    $order = LogOrder::create([
                        'user_id' => $user->id,
                        'promo_code_id' => $promo ? $promo->id : null,
                        'promo_discount_usd' => $discountForThisOrder,
                        'total_amount' => $netItemTotal,
                        'total_items' => $cartItem->quantity,
                        'status' => 'completed',
                    ]);

                    if ($firstOrderId === null) {
                        $firstOrderId = $order->id;
                    }

                    if ($netItemTotal > 0) {
                        $wallet->debit($user, $netItemTotal, 'logs_debit', 'LOGORDER-'.(string) $order->id, [
                            'log_order_id' => $order->id,
                            'log_item_id' => $cartItem->item->id,
                            'quantity' => (int) $cartItem->quantity,
                            'promo_code' => $promo ? $promo->code : null,
                            'promo_discount_usd' => $discountForThisOrder,
                        ]);
                    }

                    $credentials = $cartItem->item->credentials()
                        ->where('is_sold', false)
                        ->limit($cartItem->quantity)
                        ->lockForUpdate()
                        ->get();

                    foreach ($credentials as $cred) {
                        $cred->is_sold = true;
                        $cred->log_order_id = $order->id;
                        $cred->user_id = $user->id;
                        $cred->save();
                    }
                }

                LogCart::where('user_id', $user->id)->delete();

                if ($promo && $promoDiscountTotal > 0 && $firstOrderId !== null) {
                    app(PromoService::class)->redeemForOrder($user, $promo, 'logs', LogOrder::class, (string) $firstOrderId, (float) $promoDiscountTotal);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->with('error', 'An error occurred during checkout.');
            }

            $user->notify(new SimpleDatabaseNotification(
                'Logs purchase completed',
                'Your logs purchase has been completed.',
                '/orders?type=logs'
            ));

            $checkoutOrders = LogOrder::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            foreach ($checkoutOrders as $order) {
                try {
                    Mail::to($user->email)->send(new LogPurchaseEmail($user, $order));
                } catch (\Exception $e) {
                    Log::error('Log Purchase Email failed to send: '.$e->getMessage());
                }
            }

            return redirect()->route('logs.cart')->with('success', 'Successfully purchased all items. Total: $'.number_format($netTotal, 2));
        } finally {
            $lock->release();
        }
    }

    public function buy(Request $request, $id, WalletService $wallet)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'promo_code' => 'nullable|string|max:40',
        ]);

        $userId = auth()->id();
        $lock = \Illuminate\Support\Facades\Cache::lock("purchase:user:{$userId}", 300);
        if (! $lock->get()) {
            return back()->with('error', 'You have a purchase in progress. Please wait for it to complete.');
        }

        try {
            $qty = $request->qty;
            $user = auth()->user();

            $item = LogItem::where('is_active', true)->findOrFail($id);
            if ((bool) ($item->is_out_of_stock ?? false)) {
                return back()->with('error', 'This item is currently out of stock.');
            }

            $availableStock = $item->credentials()->where('is_sold', false)->count();
            if ($qty > $availableStock) {
                return back()->with('error', "Only {$availableStock} accounts available in stock.");
            }

            $totalPrice = $item->price * $qty;

            $reason = null;
            $promo = app(PromoService::class)->resolveEligiblePromo($request->promo_code, $user, 'logs', $reason);
            if (! $promo && trim((string) $request->promo_code) !== '') {
                return back()->with('error', $reason ?: 'Invalid promo code.');
            }

            $discount = $promo ? app(PromoService::class)->calculateDiscountUsd($promo, (float) $totalPrice) : 0.0;
            $netTotal = round((float) $totalPrice - (float) $discount, 2);
            if ($user->walletBalance < $netTotal) {
                return back()->with('error', 'Insufficient funds. You need $'.number_format($netTotal, 2));
            }

            DB::beginTransaction();

            try {
                $order = LogOrder::create([
                    'user_id' => $user->id,
                    'promo_code_id' => $promo ? $promo->id : null,
                    'promo_discount_usd' => (float) $discount,
                    'total_amount' => $netTotal,
                    'total_items' => $qty,
                    'status' => 'completed',
                ]);

                if ($promo && $discount > 0) {
                    app(PromoService::class)->redeemForOrder($user, $promo, 'logs', LogOrder::class, (string) $order->id, (float) $discount);
                }

                if ($netTotal > 0) {
                    $wallet->debit($user, (float) $netTotal, 'logs_debit', 'LOGORDER-'.(string) $order->id, [
                        'log_order_id' => $order->id,
                        'log_item_id' => $item->id,
                        'quantity' => (int) $qty,
                        'promo_code' => $promo ? $promo->code : null,
                        'promo_discount_usd' => (float) $discount,
                    ]);
                }

                $credentials = $item->credentials()->where('is_sold', false)->limit($qty)->lockForUpdate()->get();

                foreach ($credentials as $cred) {
                    $cred->is_sold = true;
                    $cred->log_order_id = $order->id;
                    $cred->user_id = $user->id;
                    $cred->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                return back()->with('error', 'An error occurred during purchase.');
            }

            $user->notify(new SimpleDatabaseNotification(
                'Logs purchase completed',
                'Your logs purchase has been completed.',
                '/orders?type=logs'
            ));

            try {
                Mail::to($user->email)->send(new LogPurchaseEmail($user, $order));
            } catch (\Exception $e) {
                Log::error('Log Purchase Email failed to send: '.$e->getMessage());
            }

            return back()->with('success', "Successfully purchased {$qty} {$item->title} accounts.");
        } finally {
            $lock->release();
        }
    }
}
