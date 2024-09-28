<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\CreatedMessage as CreatedMessageEvent;
use Aifst\Messages\Models\Message;
use Aifst\Messages\Support\MessageManager;
use Illuminate\Support\Facades\DB;
use Aifst\Messages\Support\MessageStatistic;

class CreatedMessage extends BaseMessage
{
    /**
     * @param Message $model
     */
    public function handle(CreatedMessageEvent $event)
    {
        $groupHandler = $this->baseHandle($event);
        \Illuminate\Support\Facades\DB::connection()->enableQueryLog();
        $members = MessageManager::getInstance()->getMessageMembers($event->message);
$r=\Illuminate\Support\Facades\DB::getQueryLog();
        foreach($members as $member) {
            $this->freshByMember($groupHandler, $event, $member);
        }
    }
}
