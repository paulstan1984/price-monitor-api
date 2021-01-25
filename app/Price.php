<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'prices';
    protected $primaryKey = 'id'; 

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
