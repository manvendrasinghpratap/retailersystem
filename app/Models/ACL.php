<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Route as RouteModel;
use App\Models\Designation;


class ACL extends Model
{
    use HasFactory;
    protected $primaryKey = null;
    public $incrementing = false;
    protected $table = 'designation_route';
    protected $fillable = [
        'designation_id',
        'account_id',
        'route_id',
    ];
    public function route()
    {
        return $this->belongsTo(RouteModel::class);
    }
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }
}