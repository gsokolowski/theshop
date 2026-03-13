<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OrderConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public array $ordersData;

    /**
     * Create a new message instance.
     *
     * @param  Collection<int, Order>  $orders
     */
    public function __construct(User $user, Collection $orders)
    {
        $this->user = $user;
        $this->ordersData = $this->prepareOrdersData($orders);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation - The Shop',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'user' => $this->user,
                'ordersData' => $this->ordersData,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Prepare order data for the email view.
     *
     * @param  Collection<int, Order>  $orders
     * @return array<int, array<string, mixed>>
     */
    private function prepareOrdersData(Collection $orders): array
    {
        return $orders->map(function (Order $order) {
            return [
                'id' => $order->id,
                'qty' => $order->qty,
                'total' => $order->total,
                'created_at' => Carbon::parse($order->getRawOriginal('created_at'))->format('F j, Y'),
                'coupon' => $order->coupon,
                'products' => $order->products->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'thumbnail' => $product->thumbnail ? asset('storage/' . $product->thumbnail) : null,
                        'price' => $product->price,
                        'color_name' => $this->resolveColorName($product),
                        'size_name' => $this->resolveSizeName($product),
                    ];
                })->toArray(),
            ];
        })->values()->toArray();
    }

    /**
     * Resolve color name from product pivot.
     */
    private function resolveColorName($product): ?string
    {
        $colorId = $product->pivot->color_id ?? null;
        if (!$colorId || !$product->relationLoaded('colors')) {
            return null;
        }
        $color = $product->colors->firstWhere('id', $colorId);

        return $color?->name;
    }

    /**
     * Resolve size name from product pivot.
     */
    private function resolveSizeName($product): ?string
    {
        $sizeId = $product->pivot->size_id ?? null;
        if (!$sizeId || !$product->relationLoaded('sizes')) {
            return null;
        }
        $size = $product->sizes->firstWhere('id', $sizeId);

        return $size?->name;
    }
}
