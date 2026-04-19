<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'staff_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'work_hours',
        'remarks',
        'created_by'
    ];

    public function staff()
    {
        return $this->belongsTo(User::class);
    }
}