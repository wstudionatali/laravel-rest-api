<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use App\Notifications\EventReminderNotification;
class SendEventReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminder';

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
        $events = \App\Models\Event::with('attendees.user')
        ->whereBetween('start_time', [now(), now()->addDay()])
        ->get();

    $eventCount = $events->count();
    $eventLabel = Str::plural('event', $eventCount);

    $this->info("Found {$eventCount} {$eventLabel}.");

    $events->each(
        fn($event) => $event->attendees->each(
            fn($attendee) => $attendee->user->notify(
                new EventReminderNotification(
                    $event
                )
            )
        )
    );

    $this->info('Reminder notifications sent successfully!');
    }
}
