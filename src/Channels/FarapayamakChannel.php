<?php

namespace Farapayamak\Laravel\Channels;

use Farapayamak\Laravel\FarapayamakManager;
use Farapayamak\Laravel\Messages\FarapayamakMessage;
use Farapayamak\Laravel\Support\PhoneNumber;
use Illuminate\Notifications\Notification;

class FarapayamakChannel
{
    public function __construct(protected FarapayamakManager $farapayamak) {}

    /**
     * Send the given notification via Farapayamak.
     */
    public function send(mixed $notifiable, Notification $notification): mixed
    {
        $message = $notification->toFarapayamak($notifiable);

        if (is_string($message)) {
            $message = new FarapayamakMessage($message);
        }

        if (!$message instanceof FarapayamakMessage) {
            return null;
        }

        $to = $message->getTo() ?: $this->getRecipient($notifiable, $notification);

        if (empty($to)) {
            return null;
        }

        $cleanedTo = PhoneNumber::clean($to);

        // Pattern / Template sending
        if ($message->isPattern()) {
            return $this->farapayamak->pattern(
                $message->getPatternId(),
                $cleanedTo,
                $message->getVariables()
            );
        }

        // Voice SMS
        if ($message->getSpeechText()) {
            return $this->farapayamak->voice(
                $cleanedTo,
                $message->getSpeechText(),
                $message->getContent()
            );
        }

        // Standard SMS
        return $this->farapayamak->send(
            $cleanedTo,
            $message->getContent() ?? '',
            $message->getFrom(),
            $message->isFlash()
        );
    }

    /**
     * Automatically discover phone number from notifiable model.
     */
    protected function getRecipient(mixed $notifiable, Notification $notification): ?string
    {
        if ($to = $notifiable->routeNotificationFor('farapayamak', $notification)) {
            return $to;
        }

        if ($to = $notifiable->routeNotificationFor('sms', $notification)) {
            return $to;
        }

        foreach (['mobile', 'phone_number', 'phone', 'cellphone'] as $attr) {
            if (!empty($notifiable->{$attr})) {
                return (string) $notifiable->{$attr};
            }
        }

        return null;
    }
}
