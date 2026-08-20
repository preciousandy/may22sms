<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'testreset@example.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'testreset@example.com',
        ]);

        $response->assertSessionHasNoErrors();
    }
}
