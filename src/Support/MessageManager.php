<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Contracts\MessageGroupContract;
use Aifst\Messages\Contracts\MessageStatisticHandler;
use Aifst\Messages\Events\CreatedMessage as CreatedWithRelationsMessageEvent;
use Aifst\Messages\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use \Aifst\Messages\Contracts\MessageMember as MessageMemberContract;
use PHPUnit\Util\Annotation\DocBlock;
use PHPUnit\Util\Annotation\Registry;

class MessageManager
{
    /**
     * @var
     */
    private static $instance;

    /**
     *
     */
    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        $configInstance = config('messages.manager');
        $instance = $configInstance ? new $configInstance : new self;

        if (!$instance instanceof self) {
            throw new \Exception('MessageManager wrong instanceof');
        }

        return self::$instance ?? self::$instance = $instance;
    }

    /**
     * @param MessageContract $message
     * @return MessageStatisticHandler
     */
    public static function getMessageGroupHandler(MessageContract $message)
    {
        if ($group = $message->group()->first())
        {
            return self::getGroupHandler($group->code);
        }

        return null;
    }

    /**
     * @param string $code
     * @return MessageStatisticHandler|null
     */
    public static function getGroupHandler(string $code)
    {
        if (
            ($handlerConfig = config('messages.groups.' . $code)) &&
            !empty($handlerConfig['handler'])
        ) {
            return new $handlerConfig['handler'];
        }

        return null;
    }

    /**
     * @param MessageContract $message
     * @param array|null $data
     * @return mixed
     */
    public static function getInitMessageStatistic(MessageContract $message, ?array $data = null)
    {
        $attributes = ['main_id' => $message->getMainId()];
        $data = $data ?: $attributes;
        $message_statistic_class = config('messages.models.message_statistic');
        return $message_statistic_class::updateOrCreate($attributes, $data);
    }

    /**
     * @param MessageMemberContract $member
     * @param array|null $data
     * @return mixed
     */
    public static function getInitMemberStatistic(
        MessageMemberContract $member,
        ?array $data = null
    ) {
        $attributes = [
            'model_type' => $member->getMessageMemberModelType(),
            'model_id' => $member->getMessageMemberModelId()
        ];

        $member_statistic_class = config('messages.models.message_member_statistic');

        $data = $data ?: $attributes;
        return $member_statistic_class::updateOrCreate($attributes, $data);
    }

    /**
     * @param MessageContract $message
     * @param MessageMemberContract $member
     * @param array|null $data
     * @return mixed
     */
    public static function getInitMessageMemberStatistic(
        MessageContract $message,
        MessageMemberContract $member,
        ?array $data = null
    ) {
        $member_message_statistic_class = config('messages.models.message_member_message_statistic');

        $member_message_statistic = $member_message_statistic_class::where('main_id', $message->getMainId())
            ->where('model_type', $member->getMessageMemberModelType())
            ->where('model_id', $member->getMessageMemberModelId())
            ->first();

        if (!$member_message_statistic) {
            $member_message_statistic = new $member_message_statistic_class;
            $member_message_statistic->main_id = $message->getMainId();
            $member_message_statistic->model_type = $member->getMessageMemberModelType();
            $member_message_statistic->model_id = $member->getMessageMemberModelId();
        }

        if ($data) {
            $member_message_statistic = static::setMessageMemberStatistic(
                $member_message_statistic,
                $data
            );
        }

        return $member_message_statistic;
    }

    /**
     * @param $member_message_statistic
     * @param array $data
     * @return mixed
     */
    public static function setMessageMemberStatistic(
        $member_message_statistic,
        array $data
    ) {
        foreach ($data as $key => $item) {
            $member_message_statistic->$key = $item;
        }

        $member_message_statistic->save();

        return $member_message_statistic;
    }

    /**
     * @param MessageContract $message
     * @param MessageMemberContract $member
     * @param array|null $data
     * @return mixed
     */
    public static function getInitMessageMemberMessageStatistic(
        MessageContract $message,
        MessageMemberContract $member,
        ?array $data = null
    ) {
        $attributes = [
            'main_id' => $message->getMainId(),
            'model_type' => $member->getMessageMemberModelType(),
            'model_id' => $member->getMessageMemberModelId()
        ];

        $member_message_statistic_class = config('messages.models.message_member_message_statistic');

        $data = $data ?: $attributes;
        return $member_message_statistic_class::updateOrCreate($attributes, $data);
    }

    /**
     * @param MessageMemberContract $member
     * @param MessageGroupContract $group
     * @param array|null $data
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function getInitMessageGroupMember(
        MessageMemberContract $member,
        MessageGroupContract $group,
        ?array $data = null
    ) {
        $attributes = [
            'group_id' => $group->id,
            'model_type' => $member->getMessageMemberModelType(),
            'model_id' => $member->getMessageMemberModelId()
        ];

        $data = $data ?? $attributes;

        $message_group_member = config('messages.models.message_group_member');
        $result = $message_group_member::updateOrCreate($attributes, $data);

        return $result;
    }

    /**
     * @param MessageContract $message
     * @param MessageMemberContract $member
     * @return mixed
     */
    public static function getMessageMemberStatistic(
        MessageContract $message,
        MessageMemberContract $member
    ) {
        $member_message_statistic_class = config('messages.models.message_member_message_statistic');
        return $member_message_statistic_class::where('main_id', $message->getMainId())
            ->where('model_type', $member->getMessageMemberModelType())
            ->where('model_id', $member->getMessageMemberModelId())
            ->first();
    }

    /**
     * @param MessageContract $message
     * @return mixed
     */
    public static function getMessageStatistic(
        MessageContract $message
    ) {
        $message_statistic_class = config('messages.models.message_statistic');
        return $message_statistic_class::where('main_id', $message->getMainId())
            ->where('main_id', $message->getMainId())
            ->first();
    }

    /**
     * @param MessageContract $message
     * @param MessageMemberContract $member
     * @return mixed
     */
    public static function getGroupMemberByMessageAndMember(MessageContract $message, MessageMemberContract $member)
    {
        $message_group_member_class = config('messages.models.message_group_members');

        return $message_group_member_class::where('group_id', $message->group_id)
            ->where('model_type', $member->getMessageMemberModelType())
            ->where('model_id', $member->getMessageMemberModelId())
            ->first();
    }

    /**
     * @param MessageContract $message
     * @return mixed
     */
    public static function getPreviousMessage(
        MessageContract $message
    ) {
        $message_class = config('messages.models.message');
        return $message_class::where('main_id', $message->getMainId())
            ->where('id', '<', $message->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * @param MessageContract $message
     * @return mixed
     */
    public static function getLastMessageInThread(
        MessageContract $message
    ) {
        $message_class = config('messages.models.message');
        return $message_class::where('main_id', $message->getMainId())
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * @param MessageContract $message
     * @return int
     */
    public static function getCountMessageInThread(
        MessageContract $message
    ) {
        $message_class = config('messages.models.message');
        return $message_class::whereInThread($message->getMainId())->count();
    }

    /**
     * @param MessageContract $message
     * @param MessageMemberContract $member
     * @return mixed
     */
    public static function getMemberMessageCountRead(MessageContract $message, MessageMemberContract $member)
    {
        $statistic_table = config('messages.table_names.message_member_message_statistics');
        $message_table = config('messages.table_names.messages');
        $message_member_message_statistic_class = config('messages.models.message_member_message_statistic');

        return $message_member_message_statistic_class::query()
            ->join($message_table, $message_table . '.main_id', $statistic_table . '.main_id')
            ->whereNotNull($statistic_table . '.last_read_message_id')
            ->where($statistic_table . '.last_read_message_id', '>=', DB::raw($message_table . '.id'))
            ->where($statistic_table . '.main_id', $message->getMainId())
            ->where($statistic_table . '.model_type', $member->getMessageMemberModelType())
            ->where($statistic_table . '.model_id', $member->getMessageMemberModelId())
            ->count();
    }

    /**
     * @param MessageContract $message
     * @return array
     */
    public static function getMessageMembers(MessageContract $message)
    {
        return $message->members->all();
    }
}
