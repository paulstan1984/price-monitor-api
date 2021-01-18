<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'prices';
    protected $primaryKey = 'id'; 

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'id');
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'store_id', 'id');
    }
}
