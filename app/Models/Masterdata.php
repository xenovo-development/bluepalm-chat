<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Masterdata extends Model
{
    use HasFactory,SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at = Carbon::now('America/New_York');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('America/New_York');
        });
    }
    protected $fillable = [
        'signatory_title',
        'signatory_first_name',
        'signatory_last_name',
        'signatory_company',
        'signatory_street',
        'signatory_zip',
        'signatory_place',
        'signatory_state',
        'signatory_country',
        'signatory_phone',
        'signatory_email',
        'is_beneficiary_data_same_with_signatory',
        'beneficiary_title',
        'beneficiary_first_name',
        'beneficiary_last_name',
        'beneficiary_company',
        'beneficiary_street',
        'beneficiary_zip',
        'beneficiary_place',
        'beneficiary_state',
        'beneficiary_country',
        'beneficiary_phone',
        'beneficiary_email',
        'loyalty_bonus',
        'bank_owner_name',
        'bank_bic_swift',
        'bank_iban',
        'bank_routing',
        'bank_account',
    ];

    protected $casts=[
        'loyalty_bonus'=>'boolean',
        'is_beneficiary_data_same_with_signatory' =>'boolean'
    ];

    /**
     * User relation
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
