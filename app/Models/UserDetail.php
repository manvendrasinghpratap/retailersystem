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
        'local_government',
        'country_of_origin',
        'state_of_origin',
        'date_of_birth',
        'hire_date',
        'nin',

        'staff_suffix',
        'emergency_suffix',
        'guarantor_suffix',

        'emergency_contact_name',
        'emergency_phone',
        'emergency_relationship',

        'guarantor_name',
        'guarantor_address',
        'guarantor_phone',

        'note',

        'office_phone',
        'cell_phone',
        'whatsapp_number',

        'pinterest',
        'instagram',
        'linkedin',
        'twitter',
        'facebook'
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

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = $value
            ? ucwords(trim($value))
            : null;
    }
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = $value
            ? ucwords(trim($value))
            : null;
    }
    public function setHireDateAttribute($value)
    {
        $this->attributes['hire_date'] = $value
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

    public function getHireDateAttribute($value)
    {
        return $value
            ? date(config('constants.dateformat.slashdmyonly'), strtotime($value))
            : null;
    }

    /**
     * Create or Update User Detail
     */
    public static function updateOrCreateDetailOld($userId, $data)
    {
        return self::updateOrCreate(
            ['user_id' => $userId],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'street_address' => $data['street_address'] ?? null,
                'office_phone' => $data['office_phone'] ?? null,
                'cell_phone' => $data['cell_phone'] ?? null,
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
                'local_government' => $data['local_government'] ?? null,
                'country_of_origin' => $data['country'] ?? null,
                'state_of_origin' => $data['state'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'nin' => $data['nin'] ?? null,
                'staff_suffix' => $data['suffix'] ?? null,
            ]
        );
    }


    public static function updateOrCreateDetail($userId, $data)
    {

        return self::updateOrCreate(
            ['user_id' => $userId],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,

                'street_address' => $data['street_address'] ?? null,
                'local_government' => $data['local_government'] ?? null,
                'country_of_origin' => $data['country_of_origin'] ?? null,
                'state_of_origin' => $data['state_of_origin'] ?? null,

                'date_of_birth' => $data['date_of_birth'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,

                'nin' => $data['nin'] ?? null,
                'cell_phone' => $data['cell_phone'] ?? null,
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
                'office_phone' => $data['office_phone'] ?? $data['mobile_no'] ?? null,

                'staff_suffix' => $data['staff_suffix'] ?? null,
                'emergency_suffix' => $data['emergency_suffix'] ?? null,
                'guarantor_suffix' => $data['guarantor_suffix'] ?? null,

                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_phone' => $data['emergency_phone'] ?? null,
                'emergency_relationship' => $data['emergency_relationship'] ?? null,

                'guarantor_name' => $data['guarantor_name'] ?? null,
                'guarantor_address' => $data['guarantor_address'] ?? null,
                'guarantor_phone' => $data['guarantor_phone'] ?? null,

                'note' => $data['note'] ?? null,

                'pinterest' => $data['pinterest'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'linkedin' => $data['linkedin'] ?? null,
                'twitter' => $data['twitter'] ?? null,
                'facebook' => $data['facebook'] ?? null,
            ]
        );
    }
}
