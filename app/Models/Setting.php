<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One stored setting. Read and write through App\Services\Settings.
 */
class Setting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
