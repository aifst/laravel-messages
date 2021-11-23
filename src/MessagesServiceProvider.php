<?php

namespace  Aifst\Messages;

use Aifst\Messages\Observers\ModelObserve;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Aifst\Messages\Observers\MessageObserve;
use App\Models\Message;


/**
 * Class MessagesServiceProvider
 * @package Aifst\Messages
 */
class MessagesServiceProvider extends ServiceProvider
{

    public function boot()
    {
//        $this->registerPublishables();

//        config('messages.models.message')::observe(
//            config('messages.observers.models.message')
//        );

        Message::observe(
            MessageObserve::class
        );
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
