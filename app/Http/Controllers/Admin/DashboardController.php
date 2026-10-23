<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostOrder;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\Setting;
use App\Models\SmsOrder;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $start = $today->copy()->subDays(6)->startOfDay();

        $totalUsers = User::count();
        $todayUsers = User::whereDate('created_at', $today)->count();
        $newUsers7d = User::where('created_at', '>=', $start)->count();
        $todayLogins = User::whereDate('last_login_at', $today)->count();

        $verifiedUsers = User::where('account_status', 'active')->count();
        $pendingUsers = User::where('account_status', 'pending')->count();
        $blockedUsers = User::where('account_status', 'blocked')->count();

        $openTickets = SupportTicket::where('status', 'open')->count();
        $todayTickets = SupportTicket::whereDate('created_at', $today)->count();

        $todayLogsCount = LogOrder::where('status', 'completed')->whereDate('created_at', $today)->count();
        $todayLogsItems = (int) LogOrder::where('status', 'completed')->whereDate('created_at', $today)->sum('total_items');
        $todayLogsUsd = (float) LogOrder::where('status', 'completed')->whereDate('created_at', $today)->sum('total_amount');

        $todaySmsCount = SmsOrder::whereDate('created_at', $today)->count();
        $todaySmsUsd = (float) SmsOrder::whereDate('created_at', $today)->sum('price');

        $todayBoostCount = BoostOrder::whereDate('created_at', $today)->count();
        $todayBoostUsd = (float) BoostOrder::whereDate('created_at', $today)->sum('charge');

        $todayFundingCount = FundingTransaction::where('status', 'approved')->whereDate('approved_at', $today)->count();
        $todayFundingUsd = (float) FundingTransaction::where('status', 'approved')->whereDate('approved_at', $today)->sum('amount_usd');
        $pendingFundingCount = FundingTransaction::whereNull('approved_at')->whereNull('rejected_at')->count();

        $totalRevenueUsd = $todayFundingUsd;

        // Admin Net Gain / Profit Breakdown
        $smsMarkup = (float) (Setting::where('key', 'sms_markup_percent')->value('value') ?? 20);
        $boostMarkup = (float) (Setting::where('key', 'boost_markup_percent')->value('value') ?? 20);

        $smsMultiplier = $smsMarkup / (100 + $smsMarkup);
        $boostMultiplier = $boostMarkup / (100 + $boostMarkup);

        $todaySmsGain = round($todaySmsUsd * $smsMultiplier, 2);
        $todayBoostGain = round($todayBoostUsd * $boostMultiplier, 2);
        $todayLogsGain = round($todayLogsUsd, 2);
        $todayTotalGain = round($todaySmsGain + $todayBoostGain + $todayLogsGain, 2);

        $allTimeSmsUsd = (float) SmsOrder::whereIn('status', ['RECEIVED', 'FINISHED', 'COMPLETED'])->sum('price');
        $allTimeBoostUsd = (float) BoostOrder::whereIn('status', ['Completed', 'completed', 'FINISHED'])->sum('charge');
        $allTimeLogsUsd = (float) LogOrder::where('status', 'completed')->sum('total_amount');

        $totalSmsGain = round($allTimeSmsUsd * $smsMultiplier, 2);
        $totalBoostGain = round($allTimeBoostUsd * $boostMultiplier, 2);
        $totalLogsGain = round($allTimeLogsUsd, 2);
        $totalAllTimeGain = round($totalSmsGain + $totalBoostGain + $totalLogsGain, 2);

        // Comprehensive Total Spent metrics (SMS + Logs + Boosting)
        $allTimeSpentUsd = round($allTimeSmsUsd + $allTimeBoostUsd + $allTimeLogsUsd, 2);
        $todaySpentUsd = round($todaySmsUsd + $todayBoostUsd + $todayLogsUsd, 2);

        // All-Time Funding Confirmed
        $allTimeFundingUsd = (float) FundingTransaction::where('status', 'approved')->sum('amount_usd');
        $allTimeFundingCount = (int) FundingTransaction::where('status', 'approved')->count();

        // Order Volumes
        $allTimeSmsCount = (int) SmsOrder::whereIn('status', ['RECEIVED', 'FINISHED', 'COMPLETED'])->count();
        $allTimeBoostCount = (int) BoostOrder::whereIn('status', ['Completed', 'completed', 'FINISHED'])->count();
        $allTimeLogsCount = (int) LogOrder::where('status', 'completed')->count();
        $allTimeOrdersCount = $allTimeSmsCount + $allTimeBoostCount + $allTimeLogsCount;
        $todayOrdersCount = (int) $todaySmsCount + (int) $todayBoostCount + (int) $todayLogsCount;

        // Profit Margins %
        $allTimeProfitMargin = $allTimeSpentUsd > 0 ? round(($totalAllTimeGain / $allTimeSpentUsd) * 100, 1) : 0;
        $todayProfitMargin = $todaySpentUsd > 0 ? round(($todayTotalGain / $todaySpentUsd) * 100, 1) : 0;

        // Quick Alert Counters
        $pendingOrdersCount = (int) LogOrder::where('status', 'pending')->count() 
                            + (int) SmsOrder::whereIn('status', ['PENDING', 'WAITING', 'pending'])->count() 
                            + (int) BoostOrder::whereIn('status', ['pending', 'processing'])->count();
        $pendingReportsCount = (int) \App\Models\OrderReport::where('status', 'pending')->count();

        $seriesDates = collect(range(6, 0))->map(fn ($i) => $today->copy()->subDays($i)->toDateString());

        $registrationsByDay = User::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('c', 'd');

        $logSalesByDay = LogOrder::selectRaw('DATE(created_at) as d, SUM(total_amount) as a, SUM(total_items) as i')
            ->where('status', 'completed')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $smsSalesByDay = SmsOrder::selectRaw('DATE(created_at) as d, SUM(price) as a')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('a', 'd');

        $boostSalesByDay = BoostOrder::selectRaw('DATE(created_at) as d, SUM(charge) as a')
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->pluck('a', 'd');

        $fundingByDay = FundingTransaction::selectRaw('DATE(approved_at) as d, SUM(amount_usd) as a')
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $start)
            ->groupBy('d')
            ->pluck('a', 'd');

        $series = $seriesDates->map(function ($d) use ($registrationsByDay, $logSalesByDay, $smsSalesByDay, $boostSalesByDay, $fundingByDay) {
            $logsRow = $logSalesByDay->get($d);

            $logsUsd = (float) ($logsRow->a ?? 0);
            $smsUsd = (float) ($smsSalesByDay[$d] ?? 0);
            $boostUsd = (float) ($boostSalesByDay[$d] ?? 0);

            return [
                'date' => $d,
                'registrations' => (int) ($registrationsByDay[$d] ?? 0),
                'logs_usd' => $logsUsd,
                'logs_items' => (int) ($logsRow->i ?? 0),
                'sms_usd' => $smsUsd,
                'boost_usd' => $boostUsd,
                'funding_usd' => (float) ($fundingByDay[$d] ?? 0),
                'sales_usd' => $logsUsd + $smsUsd + $boostUsd,
            ];
        })->values();

        $latestUsers = User::query()
            ->latest()
            ->paginate(10, ['id', 'name', 'email', 'account_status', 'created_at', 'last_login_at'], 'users_page');
        $latestUsers->appends($request->query());

        $funding = DB::table('funding_transactions')
            ->join('users', 'users.id', '=', 'funding_transactions.user_id')
            ->selectRaw("'funding' as type")
            ->addSelect([
                'funding_transactions.id as id',
                'funding_transactions.user_id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'funding_transactions.amount_usd as amount_usd',
                'funding_transactions.status as status',
                'funding_transactions.created_at as created_at',
            ])
            ->selectRaw('NULL as meta');

        $logs = DB::table('log_orders')
            ->join('users', 'users.id', '=', 'log_orders.user_id')
            ->selectRaw("'logs' as type")
            ->addSelect([
                'log_orders.id as id',
                'log_orders.user_id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'log_orders.total_amount as amount_usd',
                'log_orders.status as status',
                'log_orders.created_at as created_at',
            ])
            ->selectRaw("CONCAT(COALESCE(log_orders.total_items, 0), ' items') as meta");

        $sms = DB::table('sms_orders')
            ->join('users', 'users.id', '=', 'sms_orders.user_id')
            ->selectRaw("'sms' as type")
            ->addSelect([
                'sms_orders.id as id',
                'sms_orders.user_id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'sms_orders.price as amount_usd',
                'sms_orders.status as status',
                'sms_orders.created_at as created_at',
            ])
            ->selectRaw('sms_orders.phone_number as meta');

        $boost = DB::table('boost_orders')
            ->join('users', 'users.id', '=', 'boost_orders.user_id')
            ->leftJoin('boost_services', 'boost_services.id', '=', 'boost_orders.boost_service_id')
            ->selectRaw("'boost' as type")
            ->addSelect([
                'boost_orders.id as id',
                'boost_orders.user_id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'boost_orders.charge as amount_usd',
                'boost_orders.status as status',
                'boost_orders.created_at as created_at',
            ])
            ->selectRaw('boost_services.name as meta');

        $activityUnion = $funding
            ->unionAll($logs)
            ->unionAll($sms)
            ->unionAll($boost);

        $latestActivity = DB::query()
            ->fromSub($activityUnion, 'a')
            ->orderByDesc('created_at')
            ->paginate(12, ['*'], 'activity_page');
        $latestActivity->appends($request->query());

        return view('admin.dashboard', compact(
            'totalUsers',
            'todayUsers',
            'newUsers7d',
            'todayLogins',
            'verifiedUsers',
            'pendingUsers',
            'blockedUsers',
            'openTickets',
            'todayTickets',
            'todayLogsCount',
            'todayLogsItems',
            'todayLogsUsd',
            'todaySmsCount',
            'todaySmsUsd',
            'todayBoostCount',
            'todayBoostUsd',
            'todayFundingCount',
            'todayFundingUsd',
            'pendingFundingCount',
            'totalRevenueUsd',
            'todaySmsGain',
            'todayBoostGain',
            'todayLogsGain',
            'todayTotalGain',
            'totalSmsGain',
            'totalBoostGain',
            'totalLogsGain',
            'totalAllTimeGain',
            'allTimeSpentUsd',
            'todaySpentUsd',
            'allTimeFundingUsd',
            'allTimeFundingCount',
            'allTimeSmsCount',
            'allTimeBoostCount',
            'allTimeLogsCount',
            'allTimeOrdersCount',
            'todayOrdersCount',
            'allTimeProfitMargin',
            'todayProfitMargin',
            'pendingOrdersCount',
            'pendingReportsCount',
            'allTimeSmsUsd',
            'allTimeBoostUsd',
            'allTimeLogsUsd',
            'series',
            'latestUsers',
            'latestActivity',
        ));
    }
}
