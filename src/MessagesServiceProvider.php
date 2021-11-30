<?php

namespace  Aifst\Messages;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Aifst\Messages\Observers\MessageObserve;

/**
 * Class MessagesServiceProvider
 * @package Aifst\Messages
 */
class MessagesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if(config('messages.events')) {
            foreach (config('messages.events') as $key_event => $events) {
                foreach($events as $key_type => $event) {
                    if($listener = config("messages.listeners.$key_event.$key_type")) {
                        Event::listen(
                            $event,
                            [$listener, 'handle']
                        );
                    }
                }
            }
        }

        $this->registerPublishables();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }

    /**
     * Publish migrations and config
     */
    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/messages.php' => config_path('messages.php'),
        ], 'config');

        if (! class_exists('CreateMessagesTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_messages_table.php.stub' =>
                    database_path('migrations/'.date('Y_m_d_His', time()).'_create_messages_table.php'),
            ], 'migrations');
        }
    }
}
