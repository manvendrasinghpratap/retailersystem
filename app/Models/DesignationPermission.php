<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignationPermission extends Model
{
    use HasFactory;

    protected $table = 'designation_permissions';

    protected $fillable = [
        'account_id',
        'designation_id',
        'permission_id',
    ];

    /**
     * Assignment belongs to a designation.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(
            Designation::class
        );
    }

    /**
     * Assignment belongs to a permission.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(
            Permission::class
        );
    }
}