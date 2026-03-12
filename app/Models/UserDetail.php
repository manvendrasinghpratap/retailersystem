<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Settings;

class UserDetail extends Model
{
    use HasFactory;

    protected $table = 'user_details';
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'street_address',
        'office_phone',
        'cell_phone',
        'whatsapp_number',
        'local_government',
        'country_of_origin',
        'state_of_origin',
        'date_of_birth',
        'nin',
        'staff_suffix',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mutator for Date of Birth
     */
    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = $value 
            ? Settings::formatDate($value, config('constants.dateformat.datepicker')) 
            : null;
    }

    /**
     * Accessor for Date of Birth
     */
    public function getDateOfBirthAttribute($value)
    {
        return $value 
            ? date(config('constants.dateformat.slashdmyonly'), strtotime($value)) 
            : null;
    }

    /**
     * Create or Update User Detail
     */
    public static function updateOrCreateDetail($userId, $data)
    { 
        // echo Settings::formatDate($data['date_of_birth'], config('constants.dateformat.datepicker')); die();
        return self::updateOrCreate(
            ['user_id' => $userId],
            [
                'first_name'        => $data['first_name'] ?? null,
                'last_name'         => $data['last_name'] ?? null,
                'street_address'    => $data['street_address'] ?? null,
                'office_phone'      => $data['office_phone'] ?? null,
                'cell_phone'        => $data['cell_phone'] ?? null,
                'whatsapp_number'   => $data['whatsapp_number'] ?? null,
                'local_government'  => $data['local_government'] ?? null,
                'country_of_origin' => $data['country'] ?? null,
                'state_of_origin'   => $data['state'] ?? null,
                'date_of_birth'     => $data['date_of_birth'] ?? null,
                'nin'               => $data['nin'] ?? null,
                'staff_suffix'      => $data['suffix'] ?? null,
                ]
        );
    }
}
