<?php

namespace Aifst\Messages\Models;

use Aifst\Messages\Contracts\MessageMemberModel;
use Illuminate\Database\Eloquent\Model;

class MessageMember extends Model implements MessageMemberModel
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
}
