<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    public static function getSelectable()
    {
        return self::whereNotIn('id', [1, 3])
            ->pluck('name', 'id')
            ->toArray();
    }
}
