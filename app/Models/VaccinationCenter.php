<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationCenter extends Model
{
    /** @use HasFactory<\Database\Factories\VaccinationCenterFactory> */
    use HasFactory, HasUlids;

    protected $fillable = ['name', 'daily_capacity'];
}
