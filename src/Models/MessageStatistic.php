<?php

namespace Aifst\Messages\Models;

use Illuminate\Database\Eloquent\Model;

class MessageStatistic extends Model
{
    /**
     * @var string
     */
    protected $primaryKey = 'main_id';
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'main_id',
        'count',
        'last_id',
        'last_at',
    ];
}
