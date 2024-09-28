<?php

namespace Aifst\Messages\Models;

use Aifst\Messages\Contracts\MessageGroupContract;
use App\Models\Localizations\MessageCategoryLocalization;
use App\Models\Traits\BaseTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MessageCategory
 *
 * @property int $id
 * @property string $code
 * @property int $type_id
 */
class MessageGroup extends Model implements MessageGroupContract
{
    use BaseTrait;

    /**
     * @var array
     */
    protected $fillable = [
        'code'
    ];

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
