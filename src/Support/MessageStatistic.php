<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Events\CreatedWithRelationsMessage as CreatedWithRelationsMessageEvent;
use Aifst\Messages\Models\Message;
use Illuminate\Support\Facades\DB;

class MessageStatistic
{
    /**
     * @param Message $message
     */
    public function fresh(Message $message)
    {
        $message_id = $message->id;
        $main_id = $message->main_id ?? $message_id;
        $message_class = config('messages.models.message');
        $message_statistic_class = config('messages.models.message_statistic');
        $count = $message_class::whereInThread($main_id)->count();

        $attributes = ['main_id' => $main_id];
        $message_statistic_class::updateOrCreate(
            $attributes,
            $attributes + [
                'count' => $count,
                'last_id' => $message_id,
                'last_at' => $message->created_at,
            ]
        );

        $this->updateMemberMessageStatistic($main_id, $message->members, $count);
        $this->updateMemberStatistic($main_id, $message->members);
    }

    /**
     * @param int $main_id
     * @param array $members
     */
    protected function updateMemberStatistic(int $main_id, $members)
    {
        $member_statistic_message_class = config('messages.models.message_member_message_statistic');
        $member_statistic_class = config('messages.models.message_member_statistic');
        foreach ($members as $member) {
            $countData = $member_statistic_message_class::query()
                ->where('model_type', $member->model_type)
                ->where('model_id', $member->model_id)
                ->where('main_id', $main_id)
                ->select(DB::Raw('sum(count) as count, sum(count_read) as count_read'))
                ->first()
                ->toArray();

            $attributes = [
                'model_type' => $member->model_type,
                'model_id' => $member->model_id,
            ];
            $member_statistic_class::updateOrCreate($attributes, $attributes + $countData);
        }
    }

    /**
     * @param int $main_id
     * @param array $members
     */
    protected function updateMemberMessageStatistic(int $main_id, $members, int $message_trait_count)
    {
        $message_read_member_class = config('messages.models.message_read_member');
        $member_statistic_message_class = config('messages.models.message_member_message_statistic');

        foreach ($members as $member) {
            $countData['count_read'] = $message_read_member_class::query()
                ->where('main_id', $main_id)
                ->where('model_type', $member->model_type)
                ->where('model_id', $member->model_id)
                ->count();

            $countData['count'] = $message_trait_count;
            $attributes = [
                'main_id' => $main_id,
                'model_type' => $member->model_type,
                'model_id' => $member->model_id
            ];
            $member_statistic_message_class::updateOrCreate($attributes, $attributes + $countData);
        }
    }
}
