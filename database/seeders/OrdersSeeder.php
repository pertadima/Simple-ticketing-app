<?php

namespace Database\Seeders;

use App\Enums\IdCardType;
use App\Enums\OrderStatus;
use App\Models\Orders;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = Users::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UsersSeeder first.');
            return;
        }

        foreach ($users as $user) {
            // Each user gets 0-3 orders randomly
            $orderCount = rand(0, 3);
            
            for ($i = 0; $i < $orderCount; $i++) {
                $status = $this->getRandomStatus();
                $idCardData = $this->getRandomIdCardData();
                
                $order = Orders::create([
                    'user_id' => $user->user_id,
                    'total_amount' => 0, // Will be calculated later from order details
                    'status' => $status,
                    'discount_amount' => rand(0, 1) ? rand(5000, 50000) : 0, // Sometimes apply discount
                    'created_at' => $this->getRandomOrderDate(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        
        $this->command->info('Orders seeded successfully. OrderDetails will calculate total_amount.');
    }

    /**
     * Get random order status with realistic distribution
     */
    private function getRandomStatus(): OrderStatus
    {
        $rand = rand(1, 100);
        
        return match (true) {
            $rand <= 60 => OrderStatus::PAID,      // 60% paid
            $rand <= 85 => OrderStatus::PENDING,   // 25% pending
            default => OrderStatus::CANCELLED,     // 15% cancelled
        };
    }

    /**
     * Generate random ID card data
     */
    private function getRandomIdCardData(): array
    {
        // 80% of orders have ID card info
        if (rand(1, 100) <= 20) {
            return ['type' => null, 'number' => null];
        }

        $types = [
            IdCardType::NATIONAL,
            IdCardType::PASSPORT,
            IdCardType::DRIVING_LICENSE,
        ];

        $type = $types[array_rand($types)];
        
        $number = match ($type) {
            IdCardType::NATIONAL => $this->generateNationalId(),
            IdCardType::PASSPORT => $this->generatePassportNumber(),
            IdCardType::DRIVING_LICENSE => $this->generateDriverLicense(),
        };

        return ['type' => $type, 'number' => $number];
    }

    /**
     * Generate realistic national ID
     */
    private function generateNationalId(): string
    {
        return sprintf('%016d', rand(1000000000000000, 9999999999999999));
    }

    /**
     * Generate realistic passport number
     */
    private function generatePassportNumber(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $prefix = $letters[rand(0, 25)] . $letters[rand(0, 25)];
        $numbers = sprintf('%07d', rand(1000000, 9999999));
        return $prefix . $numbers;
    }

    /**
     * Generate realistic driver license
     */
    private function generateDriverLicense(): string
    {
        return sprintf('DL-%08d', rand(10000000, 99999999));
    }

    /**
     * Generate random order date within the last 6 months
     */
    private function getRandomOrderDate(): Carbon
    {
        $start = Carbon::now()->subMonths(6);
        $end = Carbon::now();
        
        $randomTimestamp = rand($start->timestamp, $end->timestamp);
        return Carbon::createFromTimestamp($randomTimestamp);
    }
}
