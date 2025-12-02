<?php

namespace App\Models;

use App\Models\PackagePrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiderType extends Model
{
    use HasFactory;

    protected $table = 'rider_types';

    protected $fillable = [
        'name',
        'slug'
    ];


    /**
     * Relationship: A rider type has many package prices.
     */
    public function packagePrices()
    {
        return $this->hasMany(PackagePrice::class);
    }

}
