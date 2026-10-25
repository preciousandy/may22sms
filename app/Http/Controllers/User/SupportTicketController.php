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

    public function index(Request $request)
    {
        $userId = auth()->id();
        $statusFilter = $request->query('status', 'all');
        $searchQuery = trim((string) $request->query('q', ''));

        $query = SupportTicket::where('user_id', $userId)
            ->withCount('messages')
            ->with(['messages' => function ($q) {
                $q->latest()->take(1);
            }]);

        if ($statusFilter === 'open') {
            $query->where('status', 'open');
        } elseif ($statusFilter === 'closed') {
            $query->where('status', 'closed');
        }

        if ($searchQuery !== '') {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('subject', 'like', "%{$searchQuery}%")
                  ->orWhere('category', 'like', "%{$searchQuery}%")
                  ->orWhere('id', 'like', "%{$searchQuery}%");
            });
        }

        $tickets = $query->orderByDesc('last_message_at')->orderByDesc('id')->paginate(15)->withQueryString();

        $openCount = SupportTicket::where('user_id', $userId)->where('status', 'open')->count();
        $closedCount = SupportTicket::where('user_id', $userId)->where('status', 'closed')->count();
        $totalCount = SupportTicket::where('user_id', $userId)->count();

        // Active ticket if specified in query
        $activeTicketId = $request->query('ticket');
        $activeTicket = null;
        if ($activeTicketId) {
            $activeTicket = SupportTicket::where('user_id', $userId)
                ->with(['messages' => function ($q) {
                    $q->oldest();
                }])
                ->find($activeTicketId);
        }

        // Recent user orders for 1-click issue linking
        $recentSms = \App\Models\SmsOrder::where('user_id', $userId)->latest()->take(3)->get(['id', 'order_id', 'product', 'phone_number', 'price', 'status', 'created_at']);
        $recentFunding = \App\Models\FundingTransaction::where('user_id', $userId)->latest()->take(3)->get(['id', 'reference', 'amount_usd', 'payment_method', 'status', 'created_at']);
        $recentLogs = \App\Models\LogOrder::where('user_id', $userId)->latest()->take(3)->get(['id', 'total_amount', 'total_items', 'status', 'created_at']);
        $recentUtilities = \App\Models\UtilityTransaction::where('user_id', $userId)->latest()->take(3)->get(['id', 'reference', 'service_type', 'product_name', 'customer_target', 'total_charged_usd', 'status', 'created_at']);

        return view('user.tickets.index', compact(
            'tickets',
            'openCount',
            'closedCount',
            'totalCount',
            'statusFilter',
            'searchQuery',
            'activeTicket',
            'recentSms',
            'recentFunding',
            'recentLogs',
            'recentUtilities'
        ));
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
            '/tickets?ticket=' . $ticket->id
        ));

        return redirect('/tickets?ticket=' . $ticket->id)->with('success', 'Ticket #' . $ticket->id . ' created successfully.');
    }

    public function show($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())
            ->with(['messages' => function ($q) {
                $q->oldest();
            }])
            ->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($ticket);
        }

        return redirect('/tickets?ticket=' . $ticket->id);
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        if ($ticket->status !== 'open') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'This ticket is closed. Please reopen it to reply.'], 422);
            }
            return back()->with('error', 'This ticket is closed. Please reopen it to reply.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,zip,txt',
        ]);

        if (empty(trim((string) ($request->message ?? ''))) && ! $request->hasFile('attachment')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Please enter a message or attach a file.'], 422);
            }
            return back()->with('error', 'Please enter a message or attach a file.');
        }

        [$attachmentPath, $attachmentName, $attachmentType] = $this->processAttachment($request);

        $msg = SupportTicketMessage::create([
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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Reply sent successfully.',
                'data' => [
                    'id' => $msg->id,
                    'is_admin' => false,
                    'sender_name' => 'You',
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_url,
                    'attachment_name' => $msg->attachment_name,
                    'attachment_type' => $msg->attachment_type,
                    'is_image' => $msg->isImage(),
                    'created_at_formatted' => $msg->created_at->format('M d, Y · h:i A'),
                    'created_at_diff' => $msg->created_at->diffForHumans(),
                ],
            ]);
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    public function close($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        $ticket->status = 'closed';
        $ticket->save();

        return back()->with('success', 'Ticket #' . $ticket->id . ' closed successfully.');
    }

    public function reopen($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        $ticket->status = 'open';
        $ticket->last_message_at = now();
        $ticket->save();

        return back()->with('success', 'Ticket #' . $ticket->id . ' has been reopened.');
    }

    public function messagesJson($id)
    {
        $ticket = SupportTicket::where('user_id', auth()->id())->findOrFail($id);
        $messages = $ticket->messages()->oldest()->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'is_admin' => (bool) $m->is_admin,
                'sender_name' => $m->is_admin ? 'May22SMS Support' : 'You',
                'message' => $m->message,
                'attachment_url' => $m->attachment_url,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
                'is_image' => $m->isImage(),
                'created_at_formatted' => $m->created_at->format('M d, Y · h:i A'),
                'created_at_diff' => $m->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'ticket_id' => $ticket->id,
            'ticket_status' => $ticket->status,
            'messages' => $messages,
        ]);
    }
}
