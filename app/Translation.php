<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'Chinese',
        'product_id'
    ];

    public function Product()
    {
        return $this->belongsTo('App\Product');
    }
}
