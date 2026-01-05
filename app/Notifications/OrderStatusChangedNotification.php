<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    protected Order $order;
    protected string $oldStatus;
    protected string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusMessages = [
            'pending' => 'is pending',
            'processing' => 'is being processed',
            'shipped' => 'has been shipped',
            'completed' => 'has been completed',
            'cancelled' => 'has been cancelled',
        ];

        $statusMessage = $statusMessages[$this->newStatus] ?? 'status has been updated';

        return (new MailMessage)
            ->subject('Order Status Update - ' . $this->order->order_number)
            ->greeting('Hello ' . $this->order->customer_name . '!')
            ->line('Your order status has been updated.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Status: ' . ucfirst($this->newStatus))
            ->line('Your order ' . $statusMessage . '.')
            ->action('View Order', route('orders.show', $this->order->order_number))
            ->line('Thank you for shopping with us!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusMessages = [
            'pending' => 'is pending',
            'processing' => 'is being processed',
            'shipped' => 'has been shipped',
            'completed' => 'has been completed',
            'cancelled' => 'has been cancelled',
        ];

        $statusMessage = $statusMessages[$this->newStatus] ?? 'status has been updated';

        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => 'Order #' . $this->order->order_number . ' ' . $statusMessage,
            'url' => route('orders.show', $this->order->order_number),
        ];
    }
}
