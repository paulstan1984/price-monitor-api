<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';   

    public function category()
    {
        return $this->hasOne(Category::class);
    }
}
