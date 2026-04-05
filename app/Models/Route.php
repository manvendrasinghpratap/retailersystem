<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
	use HasFactory;
	protected $table = 'routes';
	public function designations()
	{
		return $this->belongsToMany(Designation::class, 'designation_route');
	}

	public static function getSelectable()
	{
		return self::pluck('name', 'id')->toArray();
	}

}
