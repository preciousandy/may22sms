<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket_with_attachment()
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('screenshot.png');

        $response = $this->actingAs($user)->post('/tickets', [
            'subject' => 'Issue with SMS delivery',
            'category' => 'sms_delivery',
            'priority' => 'high',
            'message' => 'Please check attached screenshot',
            'attachment' => $file,
        ]);

        $response->assertRedirect();

        $ticket = SupportTicket::first();
        $this->assertNotNull($ticket);
        $this->assertEquals('Issue with SMS delivery', $ticket->subject);
        $this->assertEquals('sms_delivery', $ticket->category);

        $msg = SupportTicketMessage::first();
        $this->assertNotNull($msg);
        $this->assertNotNull($msg->attachment_path);
        $this->assertEquals('image', $msg->attachment_type);
        $this->assertTrue($msg->isImage());

        Storage::disk('public')->assertExists($msg->attachment_path);
    }

    public function test_user_can_view_tickets_page_with_filters_and_shortcuts()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $ticket1 = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Active problem with DaisySMS',
            'category' => 'virtual_numbers',
            'priority' => 'high',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $ticket2 = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Old resolved question',
            'category' => 'general',
            'priority' => 'normal',
            'status' => 'closed',
            'last_message_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('Support &amp; Resolution Hub', false);
        $response->assertSee('Active problem with DaisySMS');
        $response->assertSee('Old resolved question');
        $response->assertSee('WhatsApp Community');
        $response->assertSee('Telegram Support');
        $response->assertSee('+ New Ticket');

        // Test status filtering
        $openResponse = $this->actingAs($user)->get('/tickets?status=open');
        $openResponse->assertStatus(200);
        $openResponse->assertSee('Active problem with DaisySMS');
        $openResponse->assertDontSee('Old resolved question');
    }

    public function test_user_with_recent_orders_can_view_tickets_page_without_column_errors()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        // Create recent Log order
        \App\Models\LogOrder::create([
            'user_id' => $user->id,
            'total_amount' => 12.50,
            'total_items' => 2,
            'status' => 'completed',
        ]);

        // Create recent SMS order
        \App\Models\SmsOrder::create([
            'user_id' => $user->id,
            'provider' => 'daisysms',
            'order_id' => 'SMS-1002',
            'phone_number' => '+12025550192',
            'country' => 'usa',
            'operator' => 'any',
            'product' => 'whatsapp',
            'price' => 1.50,
            'status' => 'RECEIVED',
        ]);

        // Create recent Funding
        \App\Models\FundingTransaction::create([
            'user_id' => $user->id,
            'payment_method' => 'paystack',
            'currency' => 'USD',
            'amount' => 15000,
            'amount_usd' => 10.00,
            'reference' => 'FUND-TEST-REF',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Create recent Utility
        \App\Models\UtilityTransaction::create([
            'user_id' => $user->id,
            'reference' => 'UTL-REF-999',
            'service_type' => 'airtime',
            'provider' => 'bigisub',
            'network_or_disco' => 'mtn',
            'product_name' => 'MTN VTU Airtime',
            'customer_target' => '08012345678',
            'amount_ngn' => 1000,
            'amount_usd' => 0.65,
            'total_charged_usd' => 0.65,
            'exchange_rate' => 1500,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('Logs: Order #');
        $response->assertSee('SMS: whatsapp');
        $response->assertSee('VTU: MTN VTU Airtime');
    }

    public function test_user_can_view_active_ticket_in_tickets_hub()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Urgent OTP Help',
            'category' => 'sms_delivery',
            'priority' => 'urgent',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin' => false,
            'message' => 'First message from customer',
        ]);

        // Access via tickets hub query
        $response = $this->actingAs($user)->get('/tickets?ticket=' . $ticket->id);
        $response->assertStatus(200);
        $response->assertSee('Urgent OTP Help');
        $response->assertSee('First message from customer');
        $response->assertSee('Send Reply');

        // Access via show route redirects to hub with ticket
        $showResponse = $this->actingAs($user)->get('/tickets/' . $ticket->id);
        $showResponse->assertRedirect('/tickets?ticket=' . $ticket->id);
    }

    public function test_user_can_reply_to_ticket_and_receive_json()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Chat test',
            'category' => 'general',
            'priority' => 'normal',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/tickets/' . $ticket->id . '/reply', [
            'message' => 'Here is my reply via asynchronous chat',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Reply sent successfully.',
        ]);

        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'message' => 'Here is my reply via asynchronous chat',
        ]);
    }

    public function test_user_can_fetch_messages_json()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Polling test',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_admin' => false,
            'message' => 'Initial inquiry',
        ]);

        $response = $this->actingAs($user)->getJson('/tickets/' . $ticket->id . '/messages');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'ticket_id' => $ticket->id,
            'ticket_status' => 'open',
        ]);
        $response->assertJsonFragment([
            'message' => 'Initial inquiry',
            'sender_name' => 'You',
        ]);
    }

    public function test_user_can_close_and_reopen_ticket()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Close test',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        // Close ticket
        $closeResponse = $this->actingAs($user)->post('/tickets/' . $ticket->id . '/close');
        $closeResponse->assertRedirect();
        $this->assertEquals('closed', $ticket->fresh()->status);

        // Replying to closed ticket is rejected
        $replyResponse = $this->actingAs($user)->postJson('/tickets/' . $ticket->id . '/reply', [
            'message' => 'Trying to reply to closed ticket',
        ]);
        $replyResponse->assertStatus(422);

        // Reopen ticket
        $reopenResponse = $this->actingAs($user)->post('/tickets/' . $ticket->id . '/reopen');
        $reopenResponse->assertRedirect();
        $this->assertEquals('open', $ticket->fresh()->status);

        // Can reply again after reopening
        $replyAfterReopen = $this->actingAs($user)->postJson('/tickets/' . $ticket->id . '/reply', [
            'message' => 'Now replying after reopen',
        ]);
        $replyAfterReopen->assertStatus(200);
    }
}
