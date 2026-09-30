<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'group_key',
        'item_key',
        'value',
    ];

    public static function get(string $groupKey, string $itemKey, mixed $default = null): mixed
    {
        $setting = static::query()
            ->where('group_key', $groupKey)
            ->where('item_key', $itemKey)
            ->first();

        return $setting?->value ?? $default;
    }

    public static function set(string $groupKey, string $itemKey, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['group_key' => $groupKey, 'item_key' => $itemKey],
            ['value' => $value]
        );
    }
}
