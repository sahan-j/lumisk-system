<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Base for all in-app (database) notifications. Subclasses just return the
 * payload array from toDatabase(); icons are inline-SVG path strings.
 */
abstract class BaseDatabaseNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    abstract public function toDatabase(object $notifiable): array;
}
