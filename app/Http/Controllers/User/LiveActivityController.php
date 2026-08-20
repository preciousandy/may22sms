<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\SmsOrder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LiveActivityController extends Controller
{
    public function getActivities(): JsonResponse
    {
        $activities = [];

        // 1. Fetch real recent SMS orders
        try {
            $smsOrders = SmsOrder::with('user:id,name,email,country')
                ->latest()
                ->take(8)
                ->get();

            foreach ($smsOrders as $order) {
                $userName = $this->maskUserName($order->user->name ?? 'User #' . $order->user_id);
                $location = $this->resolveLocation($order->user->country ?? null);
                $serviceName = ucfirst($order->product ?? 'SMS Number');
                $countryName = ucfirst($order->country ?? 'Global');

                $activities[] = [
                    'id' => 'sms_' . $order->id,
                    'type' => 'sms',
                    'icon' => '📱',
                    'bg_color' => 'bg-teal-50 text-teal-600 border-teal-200',
                    'title' => "{$userName} from {$location}",
                    'message' => "Purchased {$countryName} {$serviceName} Number",
                    'time' => $this->formatTime($order->created_at),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore if error
        }

        // 2. Fetch real recent Funding transactions
        try {
            $fundings = FundingTransaction::with('user:id,name,email,country')
                ->where('status', 'approved')
                ->latest()
                ->take(6)
                ->get();

            foreach ($fundings as $funding) {
                $userName = $this->maskUserName($funding->user->name ?? 'User #' . $funding->user_id);
                $location = $this->resolveLocation($funding->user->country ?? null);
                $amountNgn = number_format((float) ($funding->amount_ngn ?? ($funding->amount_usd * 1500)));

                $activities[] = [
                    'id' => 'fund_' . $funding->id,
                    'type' => 'funding',
                    'icon' => '💳',
                    'bg_color' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    'title' => "{$userName} from {$location}",
                    'message' => "Funded wallet with ₦{$amountNgn}",
                    'time' => $this->formatTime($funding->created_at),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 3. Fetch real recent Log orders
        try {
            $logOrders = LogOrder::with(['user:id,name,email,country', 'category:id,name'])
                ->latest()
                ->take(6)
                ->get();

            foreach ($logOrders as $order) {
                $userName = $this->maskUserName($order->user->name ?? 'User #' . $order->user_id);
                $location = $this->resolveLocation($order->user->country ?? null);
                $catName = $order->category->name ?? 'Social Media Logs';

                $activities[] = [
                    'id' => 'log_' . $order->id,
                    'type' => 'logs',
                    'icon' => '🛒',
                    'bg_color' => 'bg-purple-50 text-purple-600 border-purple-200',
                    'title' => "{$userName} from {$location}",
                    'message' => "Purchased {$order->quantity}x {$catName}",
                    'time' => $this->formatTime($order->created_at),
                ];
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 4. Fallback / Additional Realistic Pool if database records are few
        if (count($activities) < 15) {
            $activities = array_merge($activities, $this->generateFallbackActivities());
        }

        // Shuffle slightly so users see dynamic popup order
        shuffle($activities);

        return response()->json([
            'success' => true,
            'activities' => array_slice($activities, 0, 20),
        ]);
    }

    private function maskUserName(?string $name): string
    {
        if (!$name || trim($name) === '') {
            return 'User #' . rand(100, 999);
        }

        $parts = explode(' ', trim($name));
        $first = ucfirst(strtolower($parts[0]));

        if (count($parts) > 1 && strlen($parts[1]) > 0) {
            $lastInitial = strtoupper(substr($parts[1], 0, 1));
            return "{$first} {$lastInitial}.";
        }

        return $first;
    }

    private function resolveLocation(?string $country): string
    {
        $locations = [
            'Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Kano', 'Enugu', 
            'Benin City', 'Asaba', 'Kaduna', 'Owerri', 'London', 'Accra'
        ];

        if ($country && strtolower($country) !== 'nigeria' && strtolower($country) !== 'ng') {
            return ucfirst($country);
        }

        return $locations[array_rand($locations)];
    }

    private function formatTime($date): string
    {
        if (!$date) return 'Just now';
        return Carbon::parse($date)->diffForHumans(null, true, true) . ' ago';
    }

    private function generateFallbackActivities(): array
    {
        $names = ['Tunde A.', 'Chidi K.', 'Blessing O.', 'Emeka M.', 'Fatima S.', 'David B.', 'Grace U.', 'Amina R.', 'Samuel T.', 'Victor E.', 'Ngozi P.', 'Ibrahim Y.', 'Daniel H.', 'Joy C.'];
        $locations = ['Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Enugu', 'Kano', 'Benin City', 'Asaba', 'London', 'Accra'];

        $templates = [
            ['type' => 'sms', 'icon' => '📱', 'bg' => 'bg-teal-50 text-teal-600 border-teal-200', 'msg' => 'Purchased Singapore WhatsApp Number'],
            ['type' => 'sms', 'icon' => '📱', 'bg' => 'bg-teal-50 text-teal-600 border-teal-200', 'msg' => 'Purchased USA Telegram Number'],
            ['type' => 'sms', 'icon' => '📱', 'bg' => 'bg-teal-50 text-teal-600 border-teal-200', 'msg' => 'Received OTP Code for WhatsApp'],
            ['type' => 'sms', 'icon' => '📱', 'bg' => 'bg-teal-50 text-teal-600 border-teal-200', 'msg' => 'Purchased UK TikTok Verification Number'],
            ['type' => 'funding', 'icon' => '💳', 'bg' => 'bg-emerald-50 text-emerald-600 border-emerald-200', 'msg' => 'Funded wallet with ₦10,000'],
            ['type' => 'funding', 'icon' => '💳', 'bg' => 'bg-emerald-50 text-emerald-600 border-emerald-200', 'msg' => 'Funded wallet with ₦25,000 via Paystack'],
            ['type' => 'logs', 'icon' => '🛒', 'bg' => 'bg-purple-50 text-purple-600 border-purple-200', 'msg' => 'Purchased Facebook Aged Account Log'],
            ['type' => 'logs', 'icon' => '🛒', 'bg' => 'bg-purple-50 text-purple-600 border-purple-200', 'msg' => 'Purchased 3x Instagram Verified Logs'],
            ['type' => 'boost', 'icon' => '🚀', 'bg' => 'bg-amber-50 text-amber-600 border-amber-200', 'msg' => 'Ordered 1,000 Instagram Followers'],
        ];

        $fallbacks = [];
        for ($i = 1; $i <= 10; $i++) {
            $tmpl = $templates[array_rand($templates)];
            $name = $names[array_rand($names)];
            $loc = $locations[array_rand($locations)];
            $mins = rand(1, 45);

            $fallbacks[] = [
                'id' => 'fb_' . $i,
                'type' => $tmpl['type'],
                'icon' => $tmpl['icon'],
                'bg_color' => $tmpl['bg'],
                'title' => "{$name} from {$loc}",
                'message' => $tmpl['msg'],
                'time' => "{$mins}m ago",
            ];
        }

        return $fallbacks;
    }
}
