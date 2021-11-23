<?php

namespace Aifst\Messages\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class MessageMemberMessageStatistic
 * @package Aifst\Messages\Models
 * @method $this|Builder whereMember(\Aifst\Messages\Contracts\MessageMember $member)
 */
class MessageMemberMessageStatistic extends Model
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
        'count',
        'count_read',
    ];

    /**
     * @param Builder $builder
     * @param \Aifst\Messages\Contracts\MessageMember $member
     */
    public function scopeWhereMember(Builder $builder, \Aifst\Messages\Contracts\MessageMember $member)
    {
        $this->where('model_type', $member->getMessageMemberModelType())
            ->where('model_id', $member->getMessageMemberModelId());
    }
}
