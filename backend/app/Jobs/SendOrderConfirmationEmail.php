<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationEmail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public User $user;

    /**
     * @var array<int, int>
     */
    public array $orderIds;

    /**
     * Create a new job instance.
     *
     * @param  array<int, int>  $orderIds
     */
    public function __construct(User $user, array $orderIds)
    {
        $this->user = $user;
        $this->orderIds = $orderIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orders = Order::with(['products.colors', 'products.sizes', 'coupon'])
            ->where('user_id', $this->user->id)
            ->whereIn('id', $this->orderIds)
            ->orderBy('id')
            ->get();

        if ($orders->isNotEmpty()) {
            Mail::to($this->user->email)->send(new OrderConfirmationEmail($this->user, $orders));
        }
    }
}
