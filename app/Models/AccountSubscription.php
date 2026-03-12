<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSubscription extends Model
{
    use HasFactory;
	protected $table = 'account_subscription';	
    public function user() {
        return $this->hasOne( User::class, 'id', 'user_id' );
    }
    public function account() {
        return $this->hasOne( Account::class, 'id', 'account_id' );
    }
   
}
