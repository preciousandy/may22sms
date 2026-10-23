<?php

namespace Tests\Feature;

use App\Mail\AdminMail;
use App\Mail\BoostOrderEmail;
use App\Mail\FundingSuccessEmail;
use App\Mail\LogPurchaseEmail;
use App\Mail\SendOtpMail;
use App\Mail\SmsPurchaseEmail;
use App\Mail\UserStatusEmail;
use App\Mail\WelcomeEmail;
use App\Models\BoostOrder;
use App\Models\BoostService;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\SmsOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_renders_successfully(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $mailable = new WelcomeEmail($user);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Welcome to May22SMS', $rendered);
        $this->assertStringContainsString('John Doe', $rendered);
        $this->assertStringContainsString('Instant Wallet Top-Up', $rendered);
    }

    public function test_otp_email_renders_successfully(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        $mailable = new SendOtpMail('849201', $user);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Verify Your May22SMS Account', $rendered);
        $this->assertStringContainsString('849201', $rendered);
        $this->assertStringContainsString('Security Reminder', $rendered);
    }

    public function test_funding_success_email_renders_successfully(): void
    {
        $user = User::factory()->create(['name' => 'Alex Trader', 'email' => 'alex@example.com']);
        $funding = new FundingTransaction([
            'payment_method' => 'cryptomus',
            'reference' => 'CR-9928124',
            'amount' => 50.00,
        ]);
        $funding->created_at = now();

        $mailable = new FundingSuccessEmail($user, $funding, 50.00);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Your Wallet Has Been Funded!', $rendered);
        $this->assertStringContainsString('50.00', $rendered);
        $this->assertStringContainsString('CR-9928124', $rendered);
        $this->assertStringContainsString('Cryptomus', $rendered);
    }

    public function test_sms_purchase_email_renders_successfully(): void
    {
        $user = User::factory()->create(['name' => 'Sam Smith', 'email' => 'sam@example.com']);
        $order = new SmsOrder([
            'country' => 'United States',
            'product' => 'Telegram Verification',
            'phone_number' => '+1 555 019 2834',
            'price' => 1.50,
        ]);
        $order->id = 1204;
        $order->created_at = now();

        $mailable = new SmsPurchaseEmail($user, $order);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Your SMS Number is Ready!', $rendered);
        $this->assertStringContainsString('#1204', $rendered);
        $this->assertStringContainsString('United States', $rendered);
        $this->assertStringContainsString('Telegram Verification', $rendered);
    }

    public function test_log_purchase_email_renders_successfully(): void
    {
        $user = User::factory()->create(['name' => 'Sam Smith', 'email' => 'sam@example.com']);
        $order = new LogOrder([
            'total_items' => 3,
            'total_amount' => 15.00,
            'status' => 'completed',
        ]);
        $order->id = 582;
        $order->created_at = now();

        $mailable = new LogPurchaseEmail($user, $order);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Your Logs are Ready to Download!', $rendered);
        $this->assertStringContainsString('#582', $rendered);
        $this->assertStringContainsString('3 item(s)', $rendered);
    }

    public function test_boost_order_email_renders_successfully(): void
    {
        $user = User::factory()->create(['name' => 'Sam Smith', 'email' => 'sam@example.com']);
        $order = new BoostOrder([
            'quantity' => 1000,
            'charge' => 8.50,
            'status' => 'processing',
        ]);
        $order->id = 912;
        $order->created_at = now();

        $mailable = new BoostOrderEmail($user, $order);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Your Boost Order is On The Way!', $rendered);
        $this->assertStringContainsString('#912', $rendered);
        $this->assertStringContainsString('1,000', $rendered);
    }

    public function test_user_status_email_renders_suspended_and_reactivated(): void
    {
        $user = User::factory()->create(['name' => 'Emma Watson', 'email' => 'emma@example.com']);

        // Suspended
        $suspendedMail = new UserStatusEmail($user, 'suspended', 'Multiple failed verification attempts detected.');
        $renderedSuspended = $suspendedMail->render();
        $this->assertStringContainsString('Important Account Status Notice', $renderedSuspended);
        $this->assertStringContainsString('Reason for Suspension', $renderedSuspended);
        $this->assertStringContainsString('Multiple failed verification attempts detected.', $renderedSuspended);

        // Reactivated
        $reactivatedMail = new UserStatusEmail($user, 'active');
        $renderedActive = $reactivatedMail->render();
        $this->assertStringContainsString('Welcome Back, Emma Watson!', $renderedActive);
        $this->assertStringContainsString('Account Standing: Active', $renderedActive);
    }

    public function test_admin_broadcast_email_renders_successfully(): void
    {
        $mailable = new AdminMail('Scheduled Platform Upgrade Notice', "Hello everyone,\n\nWe will be upgrading our SMS servers tonight at 02:00 UTC.\n\nThank you!");
        $rendered = $mailable->render();

        $this->assertStringContainsString('Scheduled Platform Upgrade Notice', $rendered);
        $this->assertStringContainsString('We will be upgrading our SMS servers', $rendered);
        $this->assertStringContainsString('May22<span style="color: #0d9488;">SMS</span>', $rendered);
    }
}
