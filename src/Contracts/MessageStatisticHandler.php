<?php

namespace Aifst\Messages\Contracts;

use Aifst\Messages\Contracts\MessageBuilder as MessageBuilderContract;
use Aifst\Messages\Contracts\MessageMember as MessageMemberContract;
use Aifst\Messages\Support\MessageMembersCollection;
use \Aifst\Messages\Contracts\MessageMember;
use App\Classes\Messages\Contracts\MessageToModel;

interface MessageStatisticHandler
{
    /**
     * @param MessageContract $message
     * @param array|null $data
     * @return bool
     */
    public function freshMessageStatistic(MessageContract $message, ?array $data = null): bool;

    /**
     * @param \Aifst\Messages\Contracts\MessageMember $member
     * @param array|null $data
     * @return bool
     */
    public function freshMemberStatistic(
        MessageMemberContract $member,
        ?array $data = null
    ): bool;

    /**
     * @param MessageContract $message
     * @param array|null $data
     * @return bool
     */
    public function freshMemberMessageStatistic(
        MessageContract $message,
        MessageMemberContract $member,
        ?array $data = null
    ): bool;

    /**
     * @param MessageGroupMemberContract $message_group_member
     * @return bool
     */
    public function freshGroupMemberStatistic(
        MessageGroupContract $group,
        MessageMemberContract $member,
        ?array $data = null
    ): bool;
}
