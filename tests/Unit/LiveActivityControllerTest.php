<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_activity_api_returns_success_and_activities()
    {
        User::factory()->create([
            'name' => 'Tunde Alex',
            'country' => 'Nigeria',
            'account_status' => 'active',
        ]);

        $response = $this->get('/api/live-activity');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'activities' => [
                '*' => [
                    'id',
                    'type',
                    'icon',
                    'bg_color',
                    'title',
                    'message',
                    'time',
                ],
            ],
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('activities'));
    }
}
