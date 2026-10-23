<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\UserStatusEmail;
use App\Models\Favorite;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\Setting;
use App\Models\SmsOrder;
use App\Models\SupportTicket;
use App\Models\SystemTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function allowedStaffPermissions(): array
    {
        return [
            'users',
            'transactions',
            'orders',
            'logs',
            'boosting',
            'funding',
            'tickets',
            'settings',
            'promos',
        ];
    }

    private function buildUsersQuery(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $usersQuery = User::query()->orderByDesc('id');

        if ($q !== '') {
            $usersQuery->where(function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone_number', 'like', $like);

                if (is_numeric($q)) {
                    $query->orWhere('id', (int) $q);
                }
            });
        }

        if ($status !== 'all') {
            $usersQuery->where('account_status', $status);
        }

        return [$usersQuery, $q, $status];
    }

    public function index(Request $request)
    {
        [$usersQuery, $q, $status] = $this->buildUsersQuery($request);
        $users = $usersQuery->paginate(20)->appends($request->query());

        $counts = [
            'total' => User::count(),
            'active' => User::where('account_status', 'active')->count(),
            'pending' => User::where('account_status', 'pending')->count(),
            'blocked' => User::where('account_status', 'blocked')->count(),
        ];

        return view('admin.users.index', compact('users', 'q', 'status', 'counts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'country' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:30',
            'account_status' => 'required|in:active,pending,blocked',
            'is_support' => 'nullable|in:1',
            'is_editor' => 'nullable|in:1',
            'staff_permissions' => 'nullable|array',
            'staff_permissions.*' => 'in:' . implode(',', $this->allowedStaffPermissions()),
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'country' => $validated['country'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'account_status' => $validated['account_status'],
            'walletBalance' => 0,
            'two_factor_enabled' => false,
            'is_admin' => false,
            'is_support' => $request->has('is_support'),
            'is_editor' => $request->has('is_editor'),
            'staff_permissions' => array_values(array_unique($validated['staff_permissions'] ?? [])),
            'password' => Hash::make($validated['password']),
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $user->id,
            'type' => 'user',
            'action' => 'create',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'after' => [
                'name' => $user->name,
                'email' => $user->email,
                'account_status' => $user->account_status,
                'is_support' => (bool) ($user->is_support ?? false),
                'is_editor' => (bool) ($user->is_editor ?? false),
                'staff_permissions' => (array) ($user->staff_permissions ?? []),
            ],
        ]);

        return redirect('/admin/users/' . $user->id)->with('success', 'User created.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$usersQuery, $q, $status] = $this->buildUsersQuery($request);

        $fileName = 'users_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($usersQuery) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id',
                'name',
                'email',
                'roles',
                'phone_number',
                'country',
                'account_status',
                'wallet_usd',
                'created_at',
                'last_login_at',
            ]);

            $usersQuery
                ->select(['id', 'name', 'email', 'is_admin', 'is_support', 'is_editor', 'phone_number', 'country', 'account_status', 'walletBalance', 'created_at', 'last_login_at'])
                ->chunkById(1000, function ($users) use ($out) {
                    foreach ($users as $u) {
                        $roles = [];
                        if ($u->is_admin) {
                            $roles[] = 'admin';
                        }
                        if ($u->is_support) {
                            $roles[] = 'support';
                        }
                        if ($u->is_editor) {
                            $roles[] = 'editor';
                        }
                        if (empty($roles)) {
                            $roles[] = 'user';
                        }
                        fputcsv($out, [
                            $u->id,
                            $u->name,
                            $u->email,
                            implode('|', $roles),
                            $u->phone_number,
                            $u->country,
                            $u->account_status,
                            (float) ($u->walletBalance ?? 0),
                            $u->created_at ? \Illuminate\Support\Carbon::parse($u->created_at)->toDateTimeString() : null,
                            $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->toDateTimeString() : null,
                        ]);
                    }
                });

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPrint(Request $request)
    {
        [$usersQuery, $q, $status] = $this->buildUsersQuery($request);
        $users = $usersQuery->limit(5000)->get(['id', 'name', 'email', 'is_admin', 'is_support', 'is_editor', 'phone_number', 'country', 'account_status', 'walletBalance', 'created_at', 'last_login_at']);

        return view('admin.users.print', compact('users', 'q', 'status'));
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $tab = (string) $request->query('tab', 'overview');

        $data = [
            'user' => $user,
            'tab' => $tab,
        ];

        if ($tab === 'wallet') {
            $walletTransactions = WalletTransaction::where('user_id', $user->id)
                ->latest()
                ->paginate(20, ['*'], 'wallet_page');
            $walletTransactions->appends($request->query());

            $data['walletTransactions'] = $walletTransactions;
        } elseif ($tab === 'funding') {
            $funding = FundingTransaction::where('user_id', $user->id)
                ->latest()
                ->paginate(20, ['*'], 'funding_page');
            $funding->appends($request->query());

            $data['funding'] = $funding;
        } elseif ($tab === 'logs') {
            $logs = LogOrder::where('user_id', $user->id)
                ->latest()
                ->paginate(20, ['*'], 'logs_page');
            $logs->appends($request->query());

            $data['logs'] = $logs;
        } elseif ($tab === 'sms') {
            $sms = SmsOrder::where('user_id', $user->id)
                ->latest()
                ->paginate(20, ['*'], 'sms_page');
            $sms->appends($request->query());

            $data['sms'] = $sms;
        } elseif ($tab === 'boost') {
            $boost = DB::table('boost_orders')
                ->leftJoin('boost_services', 'boost_services.id', '=', 'boost_orders.boost_service_id')
                ->where('boost_orders.user_id', $user->id)
                ->orderByDesc('boost_orders.id')
                ->select([
                    'boost_orders.*',
                    'boost_services.name as service_name',
                ])
                ->paginate(20, ['*'], 'boost_page');
            $boost->appends($request->query());

            $data['boost'] = $boost;
        } elseif ($tab === 'tickets') {
            $tickets = SupportTicket::where('user_id', $user->id)
                ->latest()
                ->paginate(20, ['*'], 'tickets_page');
            $tickets->appends($request->query());

            $data['tickets'] = $tickets;
        } elseif ($tab === 'notifications') {
            $notifications = $user->notifications()
                ->latest()
                ->paginate(20, ['*'], 'notifications_page');
            $notifications->appends($request->query());

            $data['notifications'] = $notifications;
        } elseif ($tab === 'favorites') {
            $favorites = Favorite::where('user_id', $user->id)
                ->latest()
                ->paginate(20, ['*'], 'favorites_page');
            $favorites->appends($request->query());

            $data['favorites'] = $favorites;
        }

        $stats = [
            'funding_total_usd' => (float) FundingTransaction::where('user_id', $user->id)->where('status', 'approved')->sum('amount_usd'),
            'funding_count' => (int) FundingTransaction::where('user_id', $user->id)->count(),
            'logs_count' => (int) LogOrder::where('user_id', $user->id)->count(),
            'sms_count' => (int) SmsOrder::where('user_id', $user->id)->count(),
            'boost_count' => (int) DB::table('boost_orders')->where('user_id', $user->id)->count(),
            'tickets_count' => (int) SupportTicket::where('user_id', $user->id)->count(),
            'notifications_count' => (int) $user->notifications()->count(),
            'favorites_count' => (int) Favorite::where('user_id', $user->id)->count(),
        ];

        $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
        if ($ngnPerUsd <= 0) {
            $ngnPerUsd = 1500.0;
        }

        $data['stats'] = $stats;
        $data['ngnPerUsd'] = $ngnPerUsd;

        return view('admin.users.show', $data);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'country' => $user->country,
            'phone_number' => $user->phone_number,
            'account_status' => $user->account_status,
            'two_factor_enabled' => (bool) ($user->two_factor_enabled ?? false),
            'is_support' => (bool) ($user->is_support ?? false),
            'is_editor' => (bool) ($user->is_editor ?? false),
            'staff_permissions' => (array) ($user->staff_permissions ?? []),
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'country' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:30',
            'account_status' => 'required|in:active,pending,blocked',
            'two_factor_enabled' => 'nullable|in:0,1',
            'is_support' => 'nullable|in:1',
            'is_editor' => 'nullable|in:1',
            'staff_permissions' => 'nullable|array',
            'staff_permissions.*' => 'in:' . implode(',', $this->allowedStaffPermissions()),
        ]);

        $oldStatus = $user->account_status;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->country = $validated['country'] ?? null;
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->account_status = $validated['account_status'];
        $user->two_factor_enabled = ($validated['two_factor_enabled'] ?? '0') === '1';
        $user->is_support = $request->has('is_support');
        $user->is_editor = $request->has('is_editor');
        $user->staff_permissions = array_values(array_unique($validated['staff_permissions'] ?? []));
        $user->save();

        // Send email if account status changed
        if ($oldStatus !== $user->account_status) {
            $status = $user->account_status === 'blocked' ? 'suspended' : 'activated';
            $reason = $request->input('status_reason', null);
            try {
                Mail::to($user->email)->send(new UserStatusEmail($user, $status, $reason));
            } catch (\Exception $e) {
                Log::error('User Status Email failed to send: '.$e->getMessage());
            }
        }

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $user->id,
            'type' => 'user',
            'action' => 'update',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'before' => $before,
            'after' => [
                'name' => $user->name,
                'email' => $user->email,
                'country' => $user->country,
                'phone_number' => $user->phone_number,
                'account_status' => $user->account_status,
                'two_factor_enabled' => (bool) ($user->two_factor_enabled ?? false),
                'is_support' => (bool) ($user->is_support ?? false),
                'is_editor' => (bool) ($user->is_editor ?? false),
                'staff_permissions' => (array) ($user->staff_permissions ?? []),
            ],
        ]);

        return back()->with('success', 'User updated.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $user->id,
            'type' => 'user',
            'action' => 'reset_password',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
        ]);

        return back()->with('success', 'Password updated.');
    }

    public function adjustWallet(Request $request, $id, WalletService $wallet)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'direction' => 'required|in:credit,debit',
            'amount_usd' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:200',
        ]);

        $reference = 'admin_adjustment:' . Str::uuid()->toString();
        $meta = [
            'note' => $validated['note'] ?? null,
            'admin_id' => auth()->id(),
        ];

        try {
            if ($validated['direction'] === 'credit') {
                $wallet->credit($user, (float) $validated['amount_usd'], 'admin_adjustment', $reference, $meta);
            } else {
                $wallet->debit($user, (float) $validated['amount_usd'], 'admin_adjustment', $reference, $meta);
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage() === 'insufficient_funds' ? 'Insufficient wallet balance.' : 'Failed to update wallet.';
            return back()->withErrors(['wallet' => $msg]);
        }

        return back()->with('success', 'Wallet updated.');
    }

    public function notify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:500',
            'action_url' => 'nullable|string|max:500',
        ]);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'admin_message',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'action_url' => $validated['action_url'] ?: null,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $user->id,
            'type' => 'notification',
            'action' => 'send',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'meta' => [
                'title' => $validated['title'],
                'action_url' => $validated['action_url'] ?: null,
            ],
        ]);

        return back()->with('success', 'Notification sent.');
    }

    public function markAllNotificationsRead($id)
    {
        $user = User::findOrFail($id);
        $user->unreadNotifications->markAsRead();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $user->id,
            'type' => 'notification',
            'action' => 'mark_all_read',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
        ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function forceLogout($id)
    {
        $user = User::findOrFail($id);
        $before = $user->force_logout_at ? \Illuminate\Support\Carbon::parse($user->force_logout_at)->toDateTimeString() : null;

        $user->force_logout_at = now();
        $user->setRememberToken(Str::random(60));
        $user->save();

        if (method_exists($user, 'tokens')) {
            try {
                $user->tokens()->delete();
            } catch (\Throwable $e) {
            }
        }

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $user->id,
            'type' => 'user',
            'action' => 'force_logout',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'before' => [
                'force_logout_at' => $before,
            ],
            'after' => [
                'force_logout_at' => now()->toDateTimeString(),
            ],
        ]);

        return back()->with('success', 'User will be logged out on their next request.');
    }

    public function impersonate(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);

        $adminId = auth()->id();
        session(['impersonator_id' => $adminId]);

        SystemTransaction::create([
            'actor_user_id' => $adminId,
            'user_id' => $targetUser->id,
            'type' => 'user',
            'action' => 'impersonate_login',
            'status' => 'completed',
            'subject_type' => User::class,
            'subject_id' => (string) $targetUser->id,
        ]);

        auth()->login($targetUser);

        return redirect('/dashboard')->with('success', 'Logged into account for ' . $targetUser->name);
    }

    public function stopImpersonating()
    {
        if (session()->has('impersonator_id')) {
            $adminId = session('impersonator_id');
            session()->forget('impersonator_id');

            $adminUser = User::find($adminId);
            if ($adminUser) {
                auth()->login($adminUser);
                return redirect('/admin/users')->with('success', 'Returned to Admin session.');
            }
        }

        return redirect('/dashboard');
    }

    public function destroy(Request $request, $id)
    {
        $admin = auth()->user();
        if ($admin && (int) $admin->id === (int) $id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user = User::findOrFail($id);

        if ($user->is_admin && (int) $user->id === 1 && (int) ($admin->id ?? 0) !== 1) {
            return back()->withErrors(['error' => 'You cannot delete the primary administrator account.']);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        DB::beginTransaction();
        try {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            DB::table('favorites')->where('user_id', $user->id)->delete();
            DB::table('log_carts')->where('user_id', $user->id)->delete();
            DB::table('daily_login_gifts')->where('user_id', $user->id)->delete();
            DB::table('reviews')->where('user_id', $user->id)->delete();
            DB::table('order_reports')->where('user_id', $user->id)->delete();

            $ticketIds = DB::table('support_tickets')->where('user_id', $user->id)->pluck('id');
            if ($ticketIds->isNotEmpty()) {
                DB::table('support_ticket_messages')->whereIn('support_ticket_id', $ticketIds)->delete();
                DB::table('support_tickets')->whereIn('id', $ticketIds)->delete();
            }

            DB::table('sms_orders')->where('user_id', $user->id)->delete();
            DB::table('boost_orders')->where('user_id', $user->id)->delete();
            DB::table('log_orders')->where('user_id', $user->id)->delete();
            DB::table('funding_transactions')->where('user_id', $user->id)->delete();
            DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
            DB::table('system_transactions')->where('user_id', $user->id)->update(['user_id' => null]);

            $user->delete();

            SystemTransaction::create([
                'actor_user_id' => $admin ? $admin->id : null,
                'type' => 'user',
                'action' => 'delete',
                'status' => 'completed',
                'subject_type' => User::class,
                'subject_id' => (string) $id,
                'before' => [
                    'name' => $userName,
                    'email' => $userEmail,
                ],
            ]);

            DB::commit();

            return redirect('/admin/users')->with('success', "User '{$userName}' (#{$id}) was deleted successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to delete user #{$id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $admin = auth()->user();
        $adminId = $admin ? (int) $admin->id : 0;

        $userIds = array_values(array_unique(array_map('intval', $validated['user_ids'])));

        // Exclude current admin
        $userIds = array_values(array_filter($userIds, function ($id) use ($adminId) {
            return $id !== $adminId;
        }));

        // Protect primary admin account (#1) from non-primary admins
        if ($adminId !== 1) {
            $userIds = array_values(array_filter($userIds, function ($id) {
                return $id !== 1;
            }));
        }

        if (empty($userIds)) {
            return back()->withErrors(['error' => 'No eligible users selected for deletion (you cannot delete yourself or the primary admin).']);
        }

        DB::beginTransaction();
        try {
            $usersToDelete = User::whereIn('id', $userIds)->get(['id', 'name', 'email']);
            $deletedCount = 0;
            $deletedNames = [];

            foreach ($usersToDelete as $user) {
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                DB::table('favorites')->where('user_id', $user->id)->delete();
                DB::table('log_carts')->where('user_id', $user->id)->delete();
                DB::table('daily_login_gifts')->where('user_id', $user->id)->delete();
                DB::table('reviews')->where('user_id', $user->id)->delete();
                DB::table('order_reports')->where('user_id', $user->id)->delete();

                $ticketIds = DB::table('support_tickets')->where('user_id', $user->id)->pluck('id');
                if ($ticketIds->isNotEmpty()) {
                    DB::table('support_ticket_messages')->whereIn('support_ticket_id', $ticketIds)->delete();
                    DB::table('support_tickets')->whereIn('id', $ticketIds)->delete();
                }

                DB::table('sms_orders')->where('user_id', $user->id)->delete();
                DB::table('boost_orders')->where('user_id', $user->id)->delete();
                DB::table('log_orders')->where('user_id', $user->id)->delete();
                DB::table('funding_transactions')->where('user_id', $user->id)->delete();
                DB::table('wallet_transactions')->where('user_id', $user->id)->delete();
                DB::table('system_transactions')->where('user_id', $user->id)->update(['user_id' => null]);

                $deletedNames[] = "#{$user->id} ({$user->name})";
                $user->delete();
                $deletedCount++;
            }

            SystemTransaction::create([
                'actor_user_id' => $adminId ?: null,
                'type' => 'user',
                'action' => 'bulk_delete',
                'status' => 'completed',
                'subject_type' => User::class,
                'subject_id' => implode(',', $userIds),
                'after' => [
                    'count' => $deletedCount,
                    'users' => $deletedNames,
                ],
            ]);

            DB::commit();

            return redirect('/admin/users')->with('success', "Successfully deleted {$deletedCount} user(s).");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to bulk delete users: " . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete selected users: ' . $e->getMessage()]);
        }
    }
}
