<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\ReadMessage as ReadMessageEvent;
use Aifst\Messages\Models\Message;
use Aifst\Messages\Support\MessageManager;
use Illuminate\Support\Facades\DB;
use Aifst\Messages\Support\MessageStatistic;

class ReadMessage extends BaseMessage
{
    /**
     * @param Message $model
     */
    public function handle(ReadMessageEvent $event)
    {
        $this->freshByMember(
            $this->baseHandle($event),
            $event,
            $event->member
        );
    }
}
