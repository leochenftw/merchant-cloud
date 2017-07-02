<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    protected $fillable = [
        'Title'
    ];

    public function Products()
    {
        return $this->hasMany('App\Product', 'manufacturer_id', 'id');
    }
}
