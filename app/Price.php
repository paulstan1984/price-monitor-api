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

    public static function getLastPrice($product_id) {
        $price = Price::where('product_id', $product_id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if(!empty($price)) {
            return round($price->amount, 2);
        }

        return null;
    }

    public static function getAvgPrice($product_id) {
        $avg_price = Price::where('product_id', $product_id)
            ->orderBy('created_at', 'DESC')
            ->avg('amount');

        
        return round($avg_price, 2);
    }

    public static function getStores($product_id) {
        $stores = Price::where('product_id', $product_id)
            ->join('stores', 'prices.store_id', '=', 'stores.id')
            ->select('stores.name')
            ->distinct()
            ->get();

        $store_names = [];
        foreach($stores as $store){
            $store_names[]=$store['name'];
        }
        
        return implode(', ', $store_names);
    }
}
