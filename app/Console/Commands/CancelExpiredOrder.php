<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class CancelExpiredOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Orders::where('status',  OrderStatus::PENDING)
            ->where('created_at', '<=', now()->subMinutes(1))
            ->with('orderDetails.ticket')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order) {
                        $this->cancelOrder($order);
                    });
                }
            });

        $this->info('Expired orders canceled successfully.');
    }

    private function cancelOrder(Orders $order)
    {
        // Update order status
        $order->update(['status' => OrderStatus::CANCELLED]);

        // Release reserved tickets back to inventory
        foreach ($order->orderDetails as $detail) {
            $detail->ticket->update([
                'quota' => DB::raw("quota + {$detail->quantity}"),
                'sold_count' => DB::raw("sold_count - {$detail->quantity}")
            ]);
        }
    }
}
