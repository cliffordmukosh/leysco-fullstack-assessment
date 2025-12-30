<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users (assuming users are already seeded)
        $users = User::take(3)->get(); // Get first 3 users (or adjust as needed)

        if ($users->isEmpty()) {
            $this->command->warn('No users found! Please seed users first.');
            return;
        }

        $notificationTypes = [
            'order_confirmation',
            'low_stock_alert',
            'system_announcement',
            'credit_limit_warning',
        ];

        $sampleNotifications = [
            [
                'type'    => 'order_confirmation',
                'title'   => 'Order Confirmed',
                'message' => 'Your order ORD-2025-12-001 has been successfully confirmed and is being processed.',
            ],
            [
                'type'    => 'low_stock_alert',
                'title'   => 'Low Stock Alert - SuperFuel Max 20W-50',
                'message' => 'Stock level for SF-MAX-20W50 is now below reorder level (Current: 18 units). Please restock soon.',
            ],
            [
                'type'    => 'system_announcement',
                'title'   => 'System Maintenance Notice',
                'message' => 'The system will undergo scheduled maintenance on January 5, 2026 from 2:00 AM to 4:00 AM. Expect brief downtime.',
            ],
            [
                'type'    => 'credit_limit_warning',
                'title'   => 'Credit Limit Warning',
                'message' => 'Your available credit is now below 20% (Remaining: KES 98,450 out of 500,000). Please review pending payments.',
            ],
            [
                'type'    => 'order_confirmation',
                'title'   => 'Order Confirmed - Second Order',
                'message' => 'Order ORD-2025-12-002 has been confirmed. Expected delivery: January 3, 2026.',
            ],
        ];

        foreach ($users as $user) {
            // Create 4–7 random notifications per user
            $count = rand(4, 7);

            for ($i = 0; $i < $count; $i++) {
                $sample = $sampleNotifications[array_rand($sampleNotifications)];

                Notification::create([
                    'id'         => (string) Str::uuid(),
                    'user_id'    => $user->id,
                    'type'       => $sample['type'],
                    'title'      => $sample['title'],
                    'message'    => $sample['message'],
                    'is_read'    => rand(0, 10) < 3, // ~30% chance to be already read
                    'read_at'    => rand(0, 10) < 3 ? now()->subHours(rand(1, 72)) : null,
                    'created_at' => now()->subDays(rand(0, 14))->subHours(rand(0, 23)),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Notifications seeded successfully! Total: ' . Notification::count());
    }
}