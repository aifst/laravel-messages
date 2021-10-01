<?php

namespace Aifst\Messages\Support\Reply;

class MessageMember implements \Aifst\Messages\Contracts\MessageMember
{
    protected string $model_type;
    protected int $model_id;

    /**
     * @param string $model_type
     * @param int $model_id
     */
    public function __construct(string $model_type, int $model_id)
    {
        $this->model_type = $model_type;
        $this->model_id = $model_id;
    }

    /**
     * @return string
     */
    public function getMessageMemberModelType(): string
    {
        return $this->model_type;
    }

    /**
     * @return int
     */
    public function getMessageMemberModelId(): int
    {
        return $this->model_id;
    }
}
