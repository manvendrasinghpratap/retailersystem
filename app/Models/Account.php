<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
	protected $table = 'accounts';	
    protected $fillable = [
    'user_id',
    'name',
    'status'
    ];
    public function user() {
        return $this->hasOne( User::class, 'id', 'user_id' );
    }
    public function userdetail() {
        return $this->hasOne( UserDetail::class,'user_id' );
    }
     public function accountsubscription() {
        return $this->hasOne( AccountSubscription::class, 'id', 'user_id' );
    }
    public function subscription()
    {
    return $this->hasOne(AccountSubscription::class, 'account_id')->where('is_expired', 0);
    }
    public function subscriptions()
    {
    return $this->hasMany(AccountSubscription::class, 'account_id');
    }

    public function subscriptiondetails() {
        return $this->hasOne(AccountSubscription::class, 'account_id', 'id' )->where('is_expired',0);
    }

    public static function createAccount($user, $request)
        {
            return self::create([
                'user_id' => $user->id,
                'name' => $request->input('suffix').' '.
                        ucwords($request->input('first_name')).' '.
                        ucwords($request->input('last_name')),
                'status' => $request->input('is_active')
            ]);
        }
   
}
