<?php

namespace Aifst\Messages\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Message
 *
 * @package Aifst\Messages\Models
 * @property int $id
 * @property int $main_id
 * @property string $owner_model_type
 * @property int $owner_model_id
 * @property string $creator_model_type
 * @property int $creator_model_id
 * @property int $created_at
 * @property int $updated_at
 * @property string $subject
 * @property string $message
 * @property array $data
 * @method $this|Builder whereMember(\Aifst\Messages\Contracts\MessageMember $member)
 * @method $this|Builder whereMemberModel(string $member_model_type, int $member_model_id)
 * @method $this|Builder whereInThread(int $message_id)
 * @method $this|Builder whereOnlyParent()
 * @method $this|Builder joinReadMembers(string $member_model_type, int $member_model_id)
 */
class Message extends Model implements \Aifst\Messages\Contracts\MessageModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'main_id',
        'owner_model_type',
        'owner_model_id',
        'creator_model_type',
        'creator_model_id',
        'subject',
        'message',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function members()
    {
        return $this->hasMany(config('messages.models.message_member'),
            'message_id',
            'id');
    }

    public function from_members()
    {
        return $this->hasMany(config('messages.models.message_from_member'),
            'message_id',
            'id');
    }

    public function read_members()
    {
        return $this->hasMany(config('messages.models.message_read_member'),
            'message_id',
            'id');
    }

    /**
     * @param array $members
     * @return $this
     */
    public function assignFromMembers(array $members): self
    {
        return $this->assignMembersMorph($members, 'from_members',
            function (\Aifst\Messages\Contracts\MessageMember $item) {
                $class = config('messages.models.message_from_member');
                $class = new $class;
                $class->model_type = $item->getMessageMemberModelType();
                $class->model_id = $item->getMessageMemberModelId();
                return $class;
            });
    }

    /**
     * @param array $members
     * @return $this
     */
    public function assignMembers(array $members): self
    {
        return $this->assignMembersMorph($members, 'members',
            function (\Aifst\Messages\Contracts\MessageMember $item) {
                $class = config('messages.models.message_member');
                $class = new $class;
                $class->model_type = $item->getMessageMemberModelType();
                $class->model_id = $item->getMessageMemberModelId();
                return $class;
            }
        );
    }

    /**
     * @param array $members
     * @return $this
     */
    public function assignReadMembers(array $members): self
    {
        return $this->assignMembersMorph($members, 'read_members',
            function (\Aifst\Messages\Contracts\MessageMember $item) {
                $class = config('messages.models.message_read_member');
                $class = new $class;
                $class->model_type = $item->getMessageMemberModelType();
                $class->model_id = $item->getMessageMemberModelId();
                $class->read = true;
                return $class;
            }
        );
    }

    /**
     * @param array $members
     * @param string $method
     * @param callable $callback
     * @return $this
     */
    protected function assignMembersMorph(array $members, string $method, callable $callback): self
    {
        $members = collect($members)
            ->filter(function ($member) {
                return $member instanceof \Aifst\Messages\Contracts\MessageMember;
            })
            ->all();

        $result = [];
        foreach ($members as $item) {
            $result[] = $callback($item);
        }

        if ($result) {
            $model = $this->getModel();

            if ($model->exists) {
                $this->$method()->delete();
                $this->$method()->saveMany($result);
                $model->load($method);
            } else {
                $class = \get_class($model);

                $class::saved(
                    function ($object) use ($result, $model, $method) {
                        $this->$method()->saveMany($result);
                        $model->load($method);
                    }
                );
            }
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
                ->where(config('messages.table_names.message_members') . '.message_id',
                    DB::Raw(config('messages.table_names.messages') . '.id'));
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
                ->where(config('messages.table_names.message_members') . '.message_id',
                    DB::Raw(config('messages.table_names.messages') . '.id'));
        });
    }

    /**
     * @param Builder $builder
     * @param int $message_id
     */
    public function scopeWhereInThread(Builder $builder, int $message_id)
    {
        $builder->where(config('messages.table_names.messages') . '.main_id', $message_id)
            ->orWhere(config('messages.table_names.messages') . '.id', $message_id);
    }

    /**
     * @param Builder $builder
     */
    public function scopeWhereOnlyParent(Builder $builder)
    {
        $builder->whereNull(config('messages.table_names.messages') . '.main_id');
    }

    /**
     * @param Builder $builder
     * @param string $member_model_type
     * @param int $member_model_id
     */
    public function scopeJoinReadMembers(Builder $builder, string $member_model_type, int $member_model_id)
    {
        $builder->leftJoin(config('messages.table_names.message_read_members'),
            function ($query) use ($member_model_type, $member_model_id) {
                $query->where(config('messages.table_names.message_read_members') . '.message_id',
                    DB::Raw(config('messages.table_names.messages') . '.id'))
                    ->where(config('messages.table_names.message_read_members') . '.model_type',
                        $member_model_type)
                    ->where(config('messages.table_names.message_read_members') . '.model_id',
                        $member_model_id);
            });
    }
}
