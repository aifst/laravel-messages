<?php

namespace Aifst\Messages\Support;

use Aifst\Messages\Contracts\MessageContract;
use Aifst\Messages\Contracts\MessageGroupContract;
use Aifst\Messages\Contracts\MessageModelContract;
use Aifst\Messages\Contracts\MessageOwner;
use Aifst\Messages\Support\Reply\MessageMember;
use App\Classes\Messages\Contracts\MessageToModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use \Aifst\Messages\Contracts\MessageBuilder as MessageBuilderInterface;
use Illuminate\Support\Facades\Event;

/**
 * Class MessageDirector
 * @package Aifst\Messages
 */
class MessageDirector
{
    /**
     * @var MessageBuilderInterface
     */
    private $builder;

    /**
     * MessageBuilderInterface constructor.
     */
    public function __construct(?MessageBuilderInterface $builder = null)
    {
        $this->builder = $builder ?? new MessageBuilder();
    }

    /**
     * @param string $subject
     * @param string $message
     * @param array $from_members
     * @param array $to_members
     * @param array $read_members
     * @param MessageOwner|null $owner
     * @param array|null $data
     * @return MessageContract
     */
    public function send(
        MessageGroupContract $group,
        string $subject,
        string $message,
        \Aifst\Messages\Contracts\MessageMember $from_member,
        array $to_members,
        array $read_members = [],
        ?MessageOwner $owner = null,
        ?array $data = null,
        ?MessageModelContract $initiator_model = null
    ): MessageContract {
        $this->builder
            ->setGroup($group->id)
            ->setSubject($subject)
            ->setMessage($message)
            ->setIsMain(true)
            ->setOwner($owner)
            ->setFromMembers($from_member);

        if ($initiator_model) {
            $this->builder
                ->setInitiatorModel($initiator_model);
        }

        if ($to_members) {
            $this->builder
                ->setToMembers(new MessageMembersCollection($to_members));
        }

        if ($data) {
            $this->builder->setData($data);
        }

        $message = $this->builder
            ->getMessage();

        $message->save();

        $message->main_id = $message->id;
        $message->saveQuietly();

        $result = MessageManager::getInstance()::getInitMessageMemberStatistic(
            $message,
            $from_member,
            [
                'last_read_message_id' => $message->id,
                'last_read_message_at' => $message->created_at
            ]
        );

        $fireEvent = config('messages.events.message.created');
        event(new $fireEvent($message));

        return $message;
    }

    /**
     * @param int $message_id
     * @param string $message
     * @param array $from_members
     * @param array $read_members
     * @param array|null $data
     * @return MessageContract
     * @throws \Exception
     */
    public function reply(
        int $message_id,
        string $message,
        \Aifst\Messages\Contracts\MessageMember $from_member,
        array $read_members = [],
        ?array $data = null,
        ?string $subject = null,
        ?int $reply_message_id = null
    ): MessageContract {
        $main = config('messages.models.message')::where('id', $message_id)
            ->whereOnlyParent()
            ->first();

        if (!$main) {
            throw new \Exception('Message for reply not found!');
        }

        $this->builder
            ->setGroup($main->group_id)
            ->setMain($message_id)
            ->setIsMain(false)
            ->setMessage($message)
            ->setOwner(new \Aifst\Messages\Support\Reply\MessageOwner($main->owner_model_type, $main->owner_model_id))
            ->setToMembers(
                new MessageMembersCollection(
                    $main->members->map(fn($item) => new MessageMember($item->model_type, $item->model_id))->all()
                )
            )
            ->setFromMembers($from_member);

        if ($data) {
            $this->builder->setData($data);
        }

        if ($reply_message_id) {
            $this->builder->setReply($reply_message_id);
        }

        if ($subject) {
            $this->builder->setSubject($subject);
        }

        $message = $this->builder
            ->getMessage();

        $message->save();

        $result = MessageManager::getInstance()::getInitMessageMemberStatistic(
            $message,
            $from_member,
            [
                'last_read_message_id' => $message->id,
                'last_read_message_at' => $message->created_at
            ]
        );

        $fireEvent = config('messages.events.message.created');
        event(new $fireEvent($message));

        return $message;
    }

    /**
     * @param int $main_id
     * @param MessageMember|null $user_member
     * @param int|null $page
     * @param int|null $per_page
     * @param string $order
     * @param bool $mark_read
     * @param \Closure|null $callback
     * @return LengthAwarePaginator
     */
    public function getThread(
        int $main_id,
        \Aifst\Messages\Contracts\MessageMember $member,
        ?int $page = null,
        ?int $per_page = null,
        string $order = 'asc',
        bool $mark_read = true,
        ?\Closure $callback = null
    ): LengthAwarePaginator {
        $mainMessage = config('messages.models.message')::find($main_id);

        $builder = config('messages.models.message')::whereInThread($main_id);

        if ($order) {
            $builder
                ->orderBy('id', $order === 'asc' ? $order : 'desc');
        }

        if ($callback) {
            $builder = $callback($builder);
        }

        $messages = $builder->paginate($per_page, '*', '', $page);
        if ($mark_read && $messages->count()) {
            $last_read_message = null;
            foreach ($messages as $message) {
                if (!$last_read_message || $last_read_message->id < $message->id) {
                    $last_read_message = $message;
                }
            }

            if ($last_read_message) {
                $member_message_statistic = MessageManager::getInstance()::getInitMessageMemberStatistic(
                    $message,
                    $member
                );

                if ($member_message_statistic->last_read_message_id < $last_read_message->id) {
                    MessageManager::getInstance()::setMessageMemberStatistic(
                        $member_message_statistic,
                        [
                            'last_read_message_id' => $last_read_message->id,
                            'last_read_message_at' => $last_read_message->created_at
                        ]
                    );

                    $fireEvent = config('messages.events.message.read');
                    event(new $fireEvent($last_read_message, $member));
                }
            }
        }

        return $messages;
    }

    /**
     * @param int $message_id
     */
    public function delete(int $message_id)
    {
        $message = config('messages.models.message')::find($message_id);

        if ($message->is_main) {
            config('messages.models.message')::where('main_id', $message->id)
                ->delete();
        }

        $message->delete();

        $fireEvent = config('messages.events.message.delete');
        event(new $fireEvent($message));
    }

    /**
     * @param int $message_id
     * @param MessageMember $member
     */
    public function removeMemberFromMessage(int $message_id, MessageMember $member)
    {
        config('messages.models.message_member')::where('model_type', $member->getMessageMemberModelType())
            ->where('model_id', $member->getMessageMemberModelId())
            ->where('message_id', $message_id)
            ->delete();

        $message = config('messages.models.message')::find($message_id);

        $fireEvent = config('messages.events.message.remove_member_from_message');
        event(new $fireEvent($message, $member));
    }
}
