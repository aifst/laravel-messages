<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\DeleteMessage as DeleteMessageEvent;
use Aifst\Messages\Models\Message;
use Illuminate\Support\Facades\DB;
use Aifst\Messages\Support\MessageStatistic;

class DeleteMessage
{
    /**
     * @param Message $model
     */
    public function handle(DeleteMessageEvent $event)
    {
        (new MessageStatistic)->fresh($event->message);
    }
}
