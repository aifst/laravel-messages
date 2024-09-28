<?php

namespace Aifst\Messages\Models;

use Aifst\Messages\Events\CreatedMessage;
use Aifst\Messages\Events\UpdatedWithRelationsMessage;
use Aifst\Messages\Observers\MessageObserve;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Message
 *
 * @package Aifst\Messages\Models
 * @property int $id
 * @property int $group_id
 * @property int $main_id
 * @property string $owner_model_type
 * @property int $owner_model_id
 * @property string $from_model_type
 * @property int $from_model_id
 * @property int $created_at
 * @property int $updated_at
 * @property string $subject
 * @property string $message
 * @property array $data
 * @method $this|Builder whereMember(\Aifst\Messages\Contracts\MessageMember $member)
 * @method $this|Builder whereMemberModel(string $member_model_type, int $member_model_id)
 * @method $this|Builder whereInThread(int $message_id)
 * @method $this|Builder joinReadMembers(string $member_model_type, int $member_model_id)
 */
class Message extends Model implements \Aifst\Messages\Contracts\MessageContract
{
    /**
     * @var array
     */
    protected $fillable = [
        'group_id',
        'main_id',
        'reply_id',
        'is_main',
        'owner_model_type',
        'owner_model_id',
        'from_model_type',
        'from_model_id',
        'subject',
        'message',
        'data'
    ];
    /**
     * @var string[]
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function members()
    {
        return $this->hasMany(
            config('messages.models.message_member'),
            'message_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function initiatorModel()
    {
        return $this->hasOne(
            config('messages.models.message_model'),
            'main_id', 'main_id'
        );
    }

    public function group()
    {
        return $this->hasOne(
            config('messages.models.message_group'),
            'id', 'group_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function readMembers()
    {
        return $this->hasMany(
            config('messages.models.message_read_member'),
            'message_id',
            'id'
        );
    }

    /**
     * @param array $members
     * @return $this
     */
    public function assignMembers(array $members, bool $clear = true): self
    {
        return $this->assignMembersMorph($members, 'members',
            function (\Aifst\Messages\Contracts\MessageMember $item) {
                $class = config('messages.models.message_member');
                $class = new $class;
                $class->model_type = $item->getMessageMemberModelType();
                $class->model_id = $item->getMessageMemberModelId();
                return $class;
            },
            $clear
        );
    }

    /**
     * @param \Aifst\Messages\Contracts\MessageMember $members
     * @return $this
     */
    public function assignReadMember(\Aifst\Messages\Contracts\MessageMember $member): self
    {
       $saveReadMember = function (\Aifst\Messages\Contracts\MessageMember $item, Message $message) {
           $attr = [
               'main_id' => $message->main_id ?? ($message->is_main ? $message->id : null),
               'model_type' => $item->getMessageMemberModelType(),
               'model_id' => $item->getMessageMemberModelId()
           ];
           return config('messages.models.message_read_member')::firstOrCreate($attr, ['read' => true, 'read_at' => $this->dateNow()]);
       };

        if ($this->exists) {
            $saveReadMember($member, $this);
        } else {
            $model = $this->getModel();

            static::saved(
                function ($object) use ($model, $member) {
                    $saveReadMember($member, $model);
                }
            );
        }
    }

    /**
     * @return false|string
     */
    public function dateNow()
    {
        return date('YYYY-MM-DD hh:mm:ss');
    }

    /**
     * @param array $members
     * @param string $method
     * @param callable $callback
     * @return $this
     */
    protected function assignMembersMorph(array $members, string $method, callable $callback, bool $clear = true): self
    {
        $members = collect($members)
            ->filter(function ($member) {
                return $member instanceof \Aifst\Messages\Contracts\MessageMember;
            })
            ->all();

        $getInitMembers = function ($members, $message, $callback) {
            $result = [];
            foreach ($members as $item) {
                $result[] = $callback($item, $message);
            }

            return $result;
        };

        $model = $this->getModel();

        if ($model->exists) {
            if ($result = $getInitMembers($members, $this, $callback)) {
                if ($clear) {
                    $this->$method()->delete();
                } else {
                    foreach($members as $member) {
                        $this->$method()
                            ->where('model_type', $member->getMessageMemberModelType())
                            ->where('model_id', $member->getMessageMemberModelId())
                            ->delete();
                    }
                }
                $this->$method()->saveMany($result);
                $model->load($method);
            }
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($model, $method, $getInitMembers, $members, $callback) {
                    if ($result = $getInitMembers($members, $this, $callback)) {
                        $this->$method()->saveMany($result);
                        $model->load($method);
                    }
                }
            );
        }

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function statistic()
    {
        return $this->hasOne(config('messages.models.message_statistic'), 'main_id');
    }

    /**
     * @param Builder $builder
     * @param \Aifst\Messages\Contracts\MessageMember $member
     */
    public function scopeWhereMember(Builder $builder, \Aifst\Messages\Contracts\MessageMember $member)
    {
        $builder->whereExists(function ($query) use ($member) {
            $query->from(config('messages.table_names.message_members'))
                ->where('model_type', $member->getMessageMemberModelType())
                ->where('model_id', $member->getMessageMemberModelId())
                ->where(
                    config('messages.table_names.message_members') . '.message_id',
                    DB::Raw(config('messages.table_names.messages') . '.id')
                );
        });
    }

    /**
     * @param Builder $builder
     * @param string $member_model_type
     * @param int $member_model_id
     */
    public function scopeWhereMemberModel(Builder $builder, string $member_model_type, int $member_model_id)
    {
        $builder->whereExists(function ($query) use ($member_model_type, $member_model_id) {
            $query->from(config('messages.table_names.message_members'))
                ->where('model_type', $member_model_type)
                ->where('model_id', $member_model_id)
                ->where(
                    config('messages.table_names.message_members') . '.message_id',
                    DB::Raw(config('messages.table_names.messages') . '.id')
                );
        });
    }

    /**
     * @param Builder $builder
     * @param int $message_id
     */
    public function scopeWhereInThread(Builder $builder, int $message_id)
    {
        $builder->where(config('messages.table_names.messages') . '.main_id', $message_id);
    }

    /**
     * @param Builder $builder
     */
    public function scopeWhereOnlyParent(Builder $builder)
    {
        $builder->where(config('messages.table_names.messages') . '.is_main', true);
    }

    /**
     * @return int
     */
    public function getMainId()
    {
        return $this->main_id ?? $this->id;
    }
}
