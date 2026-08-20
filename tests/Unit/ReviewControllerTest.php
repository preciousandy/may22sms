<?php

namespace Tests\Unit;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_review_and_check_status()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'account_status' => 'active',
        ]);

        $this->actingAs($user);

        // Check initially hasn't reviewed
        $checkRes = $this->getJson('/api/reviews/check');
        $checkRes->assertStatus(200);
        $this->assertFalse($checkRes->json('has_reviewed'));

        // Post review
        $postRes = $this->postJson('/api/reviews', [
            'rating' => 5,
            'service_type' => 'sms',
            'review_text' => 'Great SMS delivery speeds!',
        ]);

        $postRes->assertStatus(200);
        $this->assertTrue($postRes->json('success'));

        // Assert recorded in DB
        $review = Review::first();
        $this->assertNotNull($review);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('sms', $review->service_type);
        $this->assertEquals('Great SMS delivery speeds!', $review->review_text);

        // Check status now shows true
        $checkRes2 = $this->getJson('/api/reviews/check');
        $checkRes2->assertStatus(200);
        $this->assertTrue($checkRes2->json('has_reviewed'));
    }

    public function test_admin_can_view_and_delete_review()
    {
        $admin = User::factory()->create([
            'account_status' => 'active',
            'is_admin' => true,
        ]);

        $user = User::factory()->create(['account_status' => 'active']);

        $review = Review::create([
            'user_id' => $user->id,
            'rating' => 4,
            'service_type' => 'logs',
            'review_text' => 'Fast delivery',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/reviews');
        $response->assertStatus(200);
        $response->assertSee('Fast delivery');

        $delRes = $this->delete("/admin/reviews/{$review->id}");
        $delRes->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
