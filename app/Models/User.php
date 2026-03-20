<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Notifications\ResetUserPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Table associated with the model
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable
     */
    protected $fillable = [
        'account_id',
        'user_type_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'username',
        'mobile_no',
        'password',
        'avatar',
        'is_staff',
        'is_active',
        'status',
        'designation_id',
        'created_by'
    ];

    /**
     * Attributes hidden from arrays / JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
     |--------------------------------------------------------------------------
     | Mutators
     |--------------------------------------------------------------------------
     */

    /**
     * Format first name
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucwords(strtolower($value));
    }

    /**
     * Format last name
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucwords(strtolower($value));
    }

    /**
     * Format hire date before saving
     */
    public function setHireDateAttribute($value)
    {
        $this->attributes['hire_date'] = Settings::formatDate($value, 'Y-m-d');
    }

    /*
     |--------------------------------------------------------------------------
     | Accessors
     |--------------------------------------------------------------------------
     */

    /**
     * Get formatted staff name
     */
    public function getStaffNameAttribute()
    {
        return ucwords(strtolower($this->name));
    }

    /**
     * Format hire date when retrieving
     */
    public function getHireDateAttribute($value)
    {
        return $value
            ? date(Config::get('constants.dateformat.slashdmyonly'), strtotime($value))
            : null;
    }

    /**
     * Format created_at date
     */
    public function getCreatedAtAttribute($value)
    {
        return Settings::getFormattedDatetime($value);
    }

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    /**
     * User designation
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * User subscription plan
     */
    public function subscriptionplan()
    {
        return $this->belongsTo(SubscriptionPlan::class , 'subscription_id', 'id');
    }

    /**
     * User detail (profile information)
     */
    public function detail()
    {
        return $this->hasOne(UserDetail::class , 'user_id');
    }

    /**
     * User subscription status
     */
    public function subscriptionStatus()
    {
        return $this->hasOne(UserAccountSubscription::class , 'user_id');
    }

    /**
     * Customer profile
     */
    public function customer()
    {
        return $this->hasOne(Customer::class , 'user_id');
    }

    /*
     |--------------------------------------------------------------------------
     | Notifications
     |--------------------------------------------------------------------------
     */

    /**
     * Send password reset notification
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetUserPasswordNotification(
            url('/reset-password/'.$token)
        ));
    }

    public static function createStaff($request, $filename)
    {
        $user = new self();

        $user->account_id = Auth::user()->account_id;
        $user->user_type_id = config('constants.staff');
        $user->name = $request->suffix . ' ' . ucwords($request->first_name) . ' ' . ucwords($request->last_name);
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->avatar = $filename;
        $user->is_staff = config('constants.is_staff');
        $user->is_active = $request->staffstatus;
        $user->status = $request->staffstatus;
        $user->designation_id = $request->designation_id;
        $user->created_by = Auth::id();

        $user->save();

        return $user;
    }
}
