<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminMail;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function index()
    {
        $logs = EmailLog::latest()->paginate(10);

        return view('admin.mail', compact('logs'));
    }

    public function sendSingle(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $email = $request->input('email');
        $subject = $request->input('subject');
        $body = $request->input('body');

        $log = EmailLog::create([
            'admin_id' => auth()->id(),
            'recipient_type' => 'single',
            'recipients' => [$email],
            'subject' => $subject,
            'body' => $body,
            'status' => 'queued',
        ]);

        try {
            Mail::to($email)->send(new AdminMail($subject, $body));
            $log->update(['status' => 'sent']);
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }

        return back()->with('success', 'Email sent successfully!');
    }

    public function sendBulk(Request $request)
    {
        $request->validate([
            'recipient_group' => 'required|in:all,active,verified',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $group = $request->input('recipient_group');
        $subject = $request->input('subject');
        $body = $request->input('body');

        $usersQuery = User::query();

        if ($group === 'active') {
            $usersQuery->where('account_status', 'active');
        } elseif ($group === 'verified') {
            $usersQuery->whereNotNull('email_verified_at');
        }

        $users = $usersQuery->get();
        $emails = $users->pluck('email')->toArray();

        $log = EmailLog::create([
            'admin_id' => auth()->id(),
            'recipient_type' => 'bulk',
            'recipients' => $emails,
            'subject' => $subject,
            'body' => $body,
            'status' => 'queued',
        ]);

        try {
            foreach ($users as $user) {
                Mail::to($user->email)->send(new AdminMail($subject, $body));
            }
            $log->update(['status' => 'sent']);
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send bulk email: ' . $e->getMessage());
        }

        return back()->with('success', 'Bulk email sent successfully to ' . count($emails) . ' users!');
    }
}
