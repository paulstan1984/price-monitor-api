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

    public static function getLastPrice($product_id)
    {
        $price = Price::where('product_id', $product_id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!empty($price)) {
            return round($price->amount, 2);
        }

        return null;
    }

    public static function getAvgPrice($product_id)
    {
        $avg_price = Price::where('product_id', $product_id)
            ->orderBy('created_at', 'DESC')
            ->avg('amount');


        return round($avg_price, 2);
    }

    public static function getStatistics($data, $groupingType = 'day', $created_by = 0)
    {
        $data['StartDate'] = date('Y-m-d', strtotime($data['StartDate']));
        $data['EndDate'] = date('Y-m-d', strtotime($data['EndDate'] . ' +1 day'));

        $query = Price::query();

        if ($created_by > 0) {

            $query = $query->where("created_by", '=', $created_by);
        }

        $select_cols = [];
        if ($groupingType == 'day') {
            $select_cols[] = DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m-%d\') as Date');
        } else if ($groupingType == 'month') {
            $select_cols[] = DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m\') as Date');
        }

        $select_cols = array_merge($select_cols, [
            DB::raw('ROUND(max(prices.amount), 2) as MaxPrice'),
            DB::raw('ROUND(avg(prices.amount), 2) as AvgPrice'),
            DB::raw('ROUND(sum(prices.amount), 2) as TotalPrice'),
        ]);

        $group_by_cols = [];
        if ($groupingType == 'day') {
            $group_by_cols[] = DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m-%d\')');
        } else if ($groupingType == 'month') {
            $group_by_cols[] = DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m\')');
        }

        if (!empty($data['ProductsIds']) && is_array($data['ProductsIds'])) {
            $query = $query->whereIn('product_id', $data['ProductsIds']);

            $select_cols[] = 'products.id as ProductId';
            $select_cols[] = 'products.name as Product';

            $group_by_cols[] = 'products.id';
            $group_by_cols[] = 'products.name';
        }

        if (!empty($data['StartDate'])) {
            $query = $query->where('prices.created_at', '>=', $data['StartDate']);
        }

        if (!empty($data['EndDate'])) {
            $query = $query->where('prices.created_at', '<=', $data['EndDate']);
        }

        $query = $query
            ->join('products', 'prices.product_id', '=', 'products.id')
            ->select($select_cols);

        if (count($group_by_cols) > 0) {
            $query = $query->groupBy($group_by_cols);
        }

        if ($groupingType == 'day') {
            $query = $query->orderBy(DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m-%d\')'));
        } else if ($groupingType == 'month') {
            $query = $query->orderBy(DB::raw('DATE_FORMAT(prices.created_at, \'%Y-%m\')'));
        }

        $query = $query->get();

        return $query;
    }
}
