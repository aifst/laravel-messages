<?php

namespace Aifst\Messages\Models;

use Aifst\Messages\Contracts\MessageMemberModel;
use Aifst\Messages\Contracts\MessageMember as MessageMemberContract;
use Illuminate\Database\Eloquent\Model;

class MessageMember extends Model implements MessageMemberModel, MessageMemberContract
{

    /**
     * @var string
     */
    protected $primaryKey = 'message_id';
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'message_id',
        'model_type',
        'model_id',
    ];

    /**
     * @return string
     */
    public function getMessageMemberModelType(): string {
        return $this->model_type;
    }

    /**
     * @return int
     */
    public function getMessageMemberModelId(): int {
        return $this->model_id;
    }
}
