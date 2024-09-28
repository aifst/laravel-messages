<?php

namespace Aifst\Messages\Listeners;

use Aifst\Messages\Events\CreatedMessage as CreatedMessageEvent;
use Aifst\Messages\Events\ReadMessage as ReadMessageEvent;
use Aifst\Messages\Models\Message;
use Aifst\Messages\Support\MessageManager;
use Aifst\Messages\Support\Statistic\Handlers\BaseHandler;
use Illuminate\Support\Facades\DB;
use Aifst\Messages\Support\MessageStatistic;

class BaseMessage
{
    /**
     * @param $event
     * @return \Aifst\Messages\Contracts\MessageStatisticHandler|null
     * @throws \Exception
     */
    public function baseHandle($event)
    {
        if (!($groupHandler = MessageManager::getInstance()::getMessageGroupHandler($event->message))) {
            throw new \Exception('Message group handler not found.');
        }
        $groupHandler->freshMessageStatistic($event->message, $event->data);

        return $groupHandler;
    }

    /**
     * @param CreatedMessageEvent $event
     * @param $member
     */
    protected function freshByMember(BaseHandler $groupHandler, $event, $member)
    {
        $groupHandler->freshMemberMessageStatistic($event->message, $member, $event->data);
        $groupHandler->freshGroupMemberStatistic($event->message->group, $member, $event->data);
        $groupHandler->freshMemberStatistic($member, $event->data);
    }
}
