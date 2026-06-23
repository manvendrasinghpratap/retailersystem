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

    protected $table = 'users';

    protected $fillable = [
        'account_id',
        'user_type_id',
        'store_id',
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucwords(strtolower($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucwords(strtolower($value));
    }

    public function setHireDateAttribute($value)
    {
        $this->attributes['hire_date'] = Settings::formatDate($value, 'Y-m-d');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStaffNameAttribute()
    {
        return ucwords(strtolower($this->name));
    }

    public function getHireDateAttribute($value)
    {
        return $value
            ? date(Config::get('constants.dateformat.slashdmyonly'), strtotime($value))
            : null;
    }

    public function getCreatedAtAttribute($value)
    {
        return Settings::getFormattedDatetime($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function subscriptionplan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_id', 'id');
    }

    public function detail()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    public function subscriptionStatus()
    {
        return $this->hasOne(UserAccountSubscription::class, 'user_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'staff_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetUserPasswordNotification(
            url('/reset-password/' . $token)
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Static Helpers
    |--------------------------------------------------------------------------
    */

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

    public static function getByAccount($id, $accountId)
    {
        return self::where('id', $id)
            ->where('account_id', $accountId)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function hasDesignation()
    {
        return in_array($this->designation_id, [1, 2]);
    }

    public function isAdmin()
    {
        return $this->designation_id == 2;
    }

    public function isStaff()
    {
        return $this->is_staff == 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOfAccount($query, $accountId = null)
    {
        $accountId = $accountId ?? auth()->user()->account_id;
        return $query->where('account_id', $accountId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_staff', 1);
    }

    public function scopeActiveByAccount($query)
    {
        return $query->ofAccount()->active();
    }

    public function scopeActiveByAccountAndStaff($query)
    {
        return $query->ofAccount()
            ->staff()
            ->active();
    }

    public function scopeVisibleToUser($query)
    {
        if (!Auth::user()->hasDesignation()) {
            return $query->where('id', Auth::id());
        }
        return $query;
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}