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
}
