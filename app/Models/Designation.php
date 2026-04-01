<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    public static function getSelectable()
    {
        return self::where('status', 1)->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }
}
