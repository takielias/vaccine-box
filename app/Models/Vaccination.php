<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    use HasFactory, HasUlids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vaccination_date' => 'datetime',
        ];
    }

    protected $fillable = ['user_id', 'vaccination_center_id', 'vaccination_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vaccinationCenter(): BelongsTo
    {
        return $this->belongsTo(VaccinationCenter::class);
    }
}
