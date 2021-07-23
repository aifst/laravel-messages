<?php

namespace Aifst\Messages\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MessageModel
 * @package Aifst\Messages\Models
 */
class MessageModel extends Model
{

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'main_id',
        'model_type',
        'model_id',
    ];
}
