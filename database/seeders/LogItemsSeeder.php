<?php

namespace Database\Seeders;

use App\Models\LogItem;
use App\Models\LogCredential;
use Illuminate\Database\Seeder;

class LogItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Aged Facebook Account (2018)',
                'platform' => 'facebook',
                'price' => 5.00,
                'description' => 'Aged Facebook account with cookies, verified email, and profile picture.',
                'is_active' => true,
            ],
            [
                'title' => 'Instagram Business Account',
                'platform' => 'instagram',
                'price' => 7.50,
                'description' => 'Instagram business account with 100+ followers and verified email.',
                'is_active' => true,
            ],
            [
                'title' => 'Twitter/X Aged Account',
                'platform' => 'twitter',
                'price' => 6.00,
                'description' => 'Aged Twitter account with profile, bio, and some tweets.',
                'is_active' => true,
            ],
            [
                'title' => 'YouTube Channel (Monetized Ready)',
                'platform' => 'youtube',
                'price' => 15.00,
                'description' => 'YouTube channel with aged Gmail and recovery access included.',
                'is_active' => true,
            ],
            [
                'title' => 'TikTok Creator Account',
                'platform' => 'tiktok',
                'price' => 8.00,
                'description' => 'TikTok creator account with some videos and followers.',
                'is_active' => true,
            ],
            [
                'title' => 'Discord Nitro Account',
                'platform' => 'discord',
                'price' => 12.00,
                'description' => 'Discord account with active Nitro subscription.',
                'is_active' => false,
            ],
        ];

        foreach ($items as $itemData) {
            $item = LogItem::create($itemData);

            $credentials = [];
            $stockCount = rand(3, 8);

            for ($i = 1; $i <= $stockCount; $i++) {
                $credentials[] = [
                    'log_item_id' => $item->id,
                    'data' => $this->generateCredentialData($item->platform, $i),
                    'is_sold' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LogCredential::insert($credentials);
        }
    }

    private function generateCredentialData(string $platform, int $index): string
    {
        $users = ['user', 'account', 'profile', 'login', 'member'];
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'mail.com'];
        
        $username = $users[array_rand($users)] . $index . rand(100, 999);
        $email = $username . '@' . $domains[array_rand($domains)];
        $password = 'Pass' . rand(1000, 9999) . '!';
        
        switch ($platform) {
            case 'facebook':
                $cookie = base64_encode('fb_cookie_data_' . rand(1000, 9999));
                return "{$email}:{$password}:{$cookie}";
                
            case 'instagram':
                $cookie = base64_encode('ig_session_' . rand(1000, 9999));
                return "{$username}:{$password}:{$cookie}";
                
            case 'twitter':
                $cookie = base64_encode('twitter_auth_' . rand(1000, 9999));
                return "{$username}:{$password}:{$cookie}";
                
            case 'youtube':
                $cookie = base64_encode('yt_session_' . rand(1000, 9999));
                return "{$email}:{$password}:{$cookie}";

            case 'tiktok':
                $cookie = base64_encode('tt_token_' . rand(1000, 9999));
                return "{$username}:{$password}:{$cookie}";
                
            case 'discord':
                $token = bin2hex(random_bytes(16));
                return "{$username}:{$password}:{$token}";
                
            default:
                return "{$username}:{$password}:extra_data";
        }
    }
}
