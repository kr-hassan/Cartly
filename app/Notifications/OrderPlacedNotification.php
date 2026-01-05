<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Order Placed - ' . $this->order->order_number)
            ->greeting('Hello Admin!')
            ->line('A new order has been placed on your store.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Customer: ' . $this->order->customer_name)
            ->line('Total Amount: ৳' . number_format($this->order->total, 2))
            ->action('View Order', route('admin.orders.show', $this->order))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer_name,
            'total' => $this->order->total,
            'status' => $this->order->status,
            'message' => 'New order #' . $this->order->order_number . ' placed by ' . $this->order->customer_name,
            'url' => route('admin.orders.show', $this->order),
        ];
    }
}
