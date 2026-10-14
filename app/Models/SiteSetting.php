<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'string',
    ];

    public function getValueAttribute($value): mixed
    {
        if ($value === null) {
            return null;
        }

        $normalized = $value;

        for ($i = 0; $i < 3; $i++) {
            if (!is_string($normalized)) {
                return $normalized;
            }

            $decoded = json_decode($normalized, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $normalized;
            }

            if (is_string($decoded) && $decoded !== $normalized) {
                $normalized = $decoded;
                continue;
            }

            return $decoded;
        }

        return $normalized;
    }

    public function setValueAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['value'] = null;
            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->attributes['value'] = (string) $value;
            return;
        }

        $this->attributes['value'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
