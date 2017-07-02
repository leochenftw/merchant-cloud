<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'Title'
    ];

    public function Products()
    {
        return $this->hasMany('App\Product', 'supplier_id', 'id');
    }
}
