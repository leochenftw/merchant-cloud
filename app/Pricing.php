<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    protected $fillable = [
        'Cost',
        'Price',
        'product_id'
    ];

    public function Product()
    {
        return $this->belongsTo('App\Product');
    }
}
