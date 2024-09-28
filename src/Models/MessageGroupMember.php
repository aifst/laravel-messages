<?php

namespace Aifst\Messages\Models;

use Aifst\Messages\Contracts\MessageGroupMemberContract;
use App\Models\Traits\BaseTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MessageCategoryUser
 *
 * @property int $id
 * @property int $group_id
 * @property string $model_type
 * @property string $model_id
 * @property int $count
 * @property int $count_read
 */
class MessageGroupMember extends Model implements MessageGroupMemberContract
{
    use BaseTrait;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'group_id',
        'model_type',
        'model_id',
        'count',
        'count_read'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group()
    {
        return $this->hasOne(MessageGroup::class, 'id', 'group_id');
    }

    public function getMessageMemberModelId(): int
    {
        return $this->id;
    }

    public function getMessageMemberModelType(): string
    {
        return static::class;
    }
}
