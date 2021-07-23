<?php

namespace  Aifst\Messages;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class MessagesServiceProvider
 * @package Aifst\Messages
 */
class MessagesServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Event::listen(
            config('messages.events.message.created'),
            [config('messages.listeners.message.created'), 'handle']
        );
        
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
                __DIR__.'/../messages/migrations/create_messages_table.php.stub' =>
                    database_path('migrations/'.date('Y_m_d_His', time()).'_create_messages_table.php'),
            ], 'migrations');
        }
    }
}
