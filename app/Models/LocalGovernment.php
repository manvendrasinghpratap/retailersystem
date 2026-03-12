<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalGovernment extends Model
{
    use HasFactory;
    protected $table = 'local_governments';	

	public function name(){
		return $this->belongsTo(User::class,'collected_by','id');
	}


}
