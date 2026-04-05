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
}