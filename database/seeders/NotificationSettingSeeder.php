<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NotificationSetting;

class NotificationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'new_messages',
            'comments_on_pins',
            'likes_on_pins',
            'friend_requests',
        ];

        User::chunk(200, function ($users) use ($types) {
            foreach ($users as $user) {
                foreach ($types as $type) {
                    NotificationSetting::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'type' => $type,
                        ],
                        [
                            'is_enabled' => true,
                        ]
                    );
                }
            }
        });

        $this->command->info('Notification settings seeded successfully.');
    }
}