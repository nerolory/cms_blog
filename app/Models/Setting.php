<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent-модель настройки приложения (ключ-значение).
 *
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value'];
}
