<?php

namespace Aifst\Messages\Models;

use Illuminate\Database\Eloquent\Model;

class MessageReadMember extends Model
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
        'read',
        'read_at',
    ];
}
