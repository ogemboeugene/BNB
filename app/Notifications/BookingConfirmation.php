<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $bnb;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking, $bnb)
    {
        $this->booking = $booking;
        $this->bnb = $bnb;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmation - ' . $this->bnb->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking has been confirmed for ' . $this->bnb->name)
            ->line('Location: ' . $this->bnb->location)
            ->line('Price per night: $' . $this->bnb->price_per_night)
            ->action('View Booking Details', url('/bookings/' . $this->booking->id))
            ->line('Thank you for choosing WillingHost!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'bnb_id' => $this->bnb->id,
            'bnb_name' => $this->bnb->name,
            'message' => 'Your booking for ' . $this->bnb->name . ' has been confirmed',
        ];
    }
}