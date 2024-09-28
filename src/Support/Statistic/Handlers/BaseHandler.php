<?php

namespace Aifst\Messages\Support\Statistic\Handlers;

use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Contracts\MessageGroupMemberContract;
use Aifst\Messages\Contracts\MessageGroupContract;
use Aifst\Messages\Models\Message;
use \Aifst\Messages\Contracts\MessageMember as MessageMemberContract;
use Illuminate\Support\Facades\DB;
use Aifst\Messages\Support\MessageManager;

abstract class BaseHandler implements \Aifst\Messages\Contracts\MessageStatisticHandler
{
    /**
     * @param MessageContract $message
     * @param array|null $data
     * @return bool
     */
    public function freshMessageStatistic(MessageContract $message, ?array $data = null): bool
    {
        $count = MessageManager::getInstance()::getCountMessageInThread($message);
        $last_message = MessageManager::getInstance()::getLastMessageInThread($message);

        $dataCount = [
            'count' => $count,
            'last_id' => $last_message->id ?? null,
            'last_at' => $last_message->created_at ?? null,
        ];

        $result = MessageManager::getInstance()::getInitMessageStatistic($message, $dataCount);

        return $result->isDirty() ?: false;
    }

    /**
     * @param MessageContract $message
     * @param MessageMemberContract $member
     * @param array|null $data
     * @return bool
     */
    public function freshMemberMessageStatistic(
        MessageContract $message,
        MessageMemberContract $member,
        ?array $data = null
    ): bool {
        if (config('messages.is_set_all_messages_unread_for_added_member')
            || MessageManager::getInstance()::getMessageMemberStatistic($message, $member)) {
            $dataCount['count_read'] = MessageManager::getInstance()::getMemberMessageCountRead($message, $member);
        } else {
            $count = MessageManager::getInstance()::getMessageStatistic($message)->count ?? 0;
            if ($count > 1 && $previousMessage = MessageManager::getInstance()::getPreviousMessage($message)) {
                $dataCount['last_read_message_id'] = $previousMessage->id;
                $dataCount['last_read_message_at'] = $previousMessage->created_at;
            }
            $dataCount['count_read'] = $count ? $count - 1 : 0;
        }

        $result = MessageManager::getInstance()::getInitMessageMemberStatistic($message, $member, $dataCount);
        return $result->isDirty() ?: false;
    }

    /**
     * @param MessageMemberContract $member
     * @param array|null $data
     * @return bool
     */
    public function freshMemberStatistic(
        MessageMemberContract $member,
        ?array $data = null
    ): bool {

        $group_member_class = config('messages.models.message_group_member');
        $group_member_table = config('messages.table_names.message_group_members');
        $statistic = $group_member_class::query()
            ->where($group_member_table . '.model_type', $member->getMessageMemberModelType())
            ->where($group_member_table . '.model_id', $member->getMessageMemberModelId())
            ->select(
                DB::raw('sum(' . $group_member_table . '.count_read) as count_read'),
                DB::raw('sum(' . $group_member_table . '.count) as count')
            )
            ->first();

        $dataCount = [
            'count' => $statistic->count ?? 0,
            'count_read' => $statistic->count_read ?? 0
        ];

        $result = MessageManager::getInstance()::getInitMemberStatistic($member, $dataCount);

        return $result->isDirty() ?: false;
    }

    /**
     * @param MessageGroupContract $group
     * @param MessageMemberContract $member
     * @return bool
     */
    public function freshGroupMemberStatistic(
        MessageGroupContract $group,
        MessageMemberContract $member,
        ?array $data = null
    ): bool {
        $message_class = config('messages.models.message');
        $message_table = config('messages.table_names.messages');
        $message_member_message_statistic_table = config('messages.table_names.message_member_message_statistics');
        $message_statistic_table = config('messages.table_names.message_statistics');
        $statistic = $message_class::where($message_table . '.group_id', $group->id)
            ->join(
                $message_member_message_statistic_table,
                $message_member_message_statistic_table . '.main_id',
                $message_table . '.id'
            )
            ->join($message_statistic_table, $message_statistic_table . '.main_id', $message_table . '.id')
            ->where('is_main', true)
            ->where('model_type', $member->getMessageMemberModelType())
            ->where('model_id', $member->getMessageMemberModelId())
            ->select(
                DB::raw('sum(' . $message_member_message_statistic_table . '.count_read) as count_read'),
                DB::raw('sum(' . $message_statistic_table . '.count) as count')
            )
            ->first();

        $dataCount = [
            'count' => $statistic->count ?? 0,
            'count_read' => $statistic->count_read ?? 0
        ];

        $result = MessageManager::getInstance()::getInitMessageGroupMember($member, $group, $dataCount);

        return $result->isDirty() ?: false;
    }
}
