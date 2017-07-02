<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'Title',
        'Barcode',
        'Measurement',
        'Width',
        'Height',
        'Depth',
        'Weight',
        'StockCount',
        'supplier_id',
        'manufacturer_id'
        // 'translation_id'
    ];

    public function Translation()
    {
        return $this->hasOne('App\Translation');
    }

    public function Supplier()
    {
        return $this->belongsTo('App\Supplier');
    }

    public function Manufacturer()
    {
        return $this->belongsTo('App\Manufacturer');
    }

    public function Images()
    {
        return $this->hasMany('App\Image');
    }

    public function Pricings()
    {
        return $this->hasMany('App\Pricing')->orderBy('id', 'desc');
    }
}
