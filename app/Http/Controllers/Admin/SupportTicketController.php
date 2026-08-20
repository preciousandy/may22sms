<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Notifications\SimpleDatabaseNotification;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $type = (string) $request->query('type', 'all');

        $tickets = SupportTicket::with('user')
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($type !== 'all', function ($q) use ($type) {
                if ($type === 'sell_logs') {
                    $q->where('subject', 'like', 'Sell Logs -%');
                } elseif ($type === 'support') {
                    $q->where(function ($sub) {
                        $sub->whereNull('subject')->orWhere('subject', 'not like', 'Sell Logs -%');
                    });
                }
            })
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets', 'status', 'type'));
    }

    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages' => function ($q) {
            $q->oldest();
        }])->findOrFail($id);

        return view('admin.tickets.show', compact('ticket'));
    }

    private function processAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment') || ! $request->file('attachment')->isValid()) {
            return [null, null, null];
        }

        $file = $request->file('attachment');
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $isImg = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true);

        $path = $file->store('tickets', 'public');
        $type = $isImg ? 'image' : 'file';

        return [$path, $originalName, $type];
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::with('user')->findOrFail($id);
        if ($ticket->status !== 'open') {
            return back()->with('error', 'This ticket is closed.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,zip,txt',
        ]);

        if (empty(trim((string) ($request->message ?? ''))) && ! $request->hasFile('attachment')) {
            return back()->with('error', 'Please enter a message or attach a file.');
        }

        [$attachmentPath, $attachmentName, $attachmentType] = $this->processAttachment($request);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_admin' => true,
            'message' => (string) ($validated['message'] ?? ''),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        $ticket->last_message_at = now();
        $ticket->save();

        $ticket->user->notify(new SimpleDatabaseNotification(
            'Support reply',
            'Admin replied to your support ticket: ' . $ticket->subject,
            '/tickets/' . $ticket->id
        ));

        return back()->with('success', 'Reply sent successfully.');
    }

    public function close($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = 'closed';
        $ticket->save();

        return back()->with('success', 'Ticket closed.');
    }
}
