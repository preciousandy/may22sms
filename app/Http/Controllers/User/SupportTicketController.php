<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Notifications\SimpleDatabaseNotification;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
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

    public function index()
    {
        $tickets = SupportTicket::where('user_id', auth()->id())
            ->withCount('messages')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $openCount = SupportTicket::where('user_id', auth()->id())->where('status', 'open')->count();
        $closedCount = SupportTicket::where('user_id', auth()->id())->where('status', 'closed')->count();

        return view('user.tickets.index', compact('tickets', 'openCount', 'closedCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:20',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,zip,txt',
        ]);

        [$attachmentPath, $attachmentName, $attachmentType] = $this->processAttachment($request);

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?? 'general',
            'priority' => $validated['priority'] ?? 'normal',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_admin' => false,
            'message' => $validated['message'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        auth()->user()->notify(new SimpleDatabaseNotification(
            'Support ticket created',
            'Your support ticket #' . $ticket->id . ' was created successfully.',
            '/tickets/' . $ticket->id
        ));

        return redirect('/tickets/' . $ticket->id)->with('success', 'Ticket created successfully.');
    }

    public function show($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())
            ->with(['messages' => function ($q) {
                $q->oldest();
            }])
            ->findOrFail($id);

        return view('user.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
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
            'is_admin' => false,
            'message' => (string) ($validated['message'] ?? ''),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        $ticket->last_message_at = now();
        $ticket->save();

        return back()->with('success', 'Reply sent successfully.');
    }

    public function close($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        $ticket->status = 'closed';
        $ticket->save();

        return back()->with('success', 'Ticket closed successfully.');
    }
}
