<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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


    public static function getStatistics($data) {
        $query = Price::query(); 

        if(!empty($data['ProductsIds']) && is_array($data['ProductsIds'])){
            $query = $query->whereIn('product_id', $data['ProductsIds']);
        }

        if(!empty($data['StoresIds']) && is_array($data['StoresIds'])){
            $query = $query->whereIn('store_id', $data['StoresIds']);
        }

        if(!empty($data['StartDate'])){
            $query = $query->where('prices.created_at', '>=', $data['StartDate']);
        }

        if(!empty($data['EndDate'])){
            $query = $query->where('prices.created_at', '<=', $data['EndDate']);
        }

        $query = $query
            ->join('stores', 'prices.store_id', '=', 'stores.id')
            ->join('products', 'prices.product_id', '=', 'products.id')
            ->select([
                DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m-%d\') as Date'),
                'stores.name as Store',
                'products.name as Product',
                DB::raw('max(prices.amount) as MaxPrice'),
                DB::raw('avg(prices.amount) as AvgPrice'),
                'products.id as ProductId',
                'stores.id as StoreId',
            ])
            ->groupBy([
                DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m-%d\')'),
                'stores.name',
                'products.name',
                'products.id',
                'stores.id',
            ])
            ->get();

        return $query;
    }
}
