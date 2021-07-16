<?php

namespace App\Http\Controllers;

use App\Price;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

Validator::extend('numericarray', function ($attribute, $value, $parameters) {
    foreach ($value as $v) {
        if (!is_int($v)) return false;
    }
    return true;
});

class Statistics extends Controller
{
    private function getDetailedProductStats($pId, $stats)
    {
        $returnData = array();
        foreach ($stats as $d) {
            if ($d['ProductId'] == $pId) {
                $returnData[] = (object)array(
                    "name" => date('Y-m-d', strtotime($d['Date'])),
                    "value" => $d['TotalPrice'],
                );
            }
        }

        return $returnData;
    }

    private function getDetailedTotalStats($stats)
    {
        $totals = [];
        foreach ($stats as $s) {
            $totals[] = (object)array(
                "name" => date('Y-m-d', strtotime($s['Date'])),
                "value" => $s['TotalPrice'],
            );
        }

        return $totals;
    }

    private function getTotalDetails($stats)
    {
        $totals = [];
        foreach ($stats as $s) {
            $totals[] = (object)array(
                "name" => sprintf("%s%s", $s['Store'], !empty($s['Product']) ? (' / ' . $s['Product']) : null),
                "value" => $s['TotalPrice'],
            );
        }

        return $totals;
    }

    /**
     * Search products
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'StartDate' => ['date', 'nullable'],
            'EndDate' => ['date', 'nullable'],
            'ProductsIds' => ['array', 'numericarray'],
            'GrouppingType' => ['string', 'required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $created_by = $request->attributes->get('user_id');
        $is_admin = $request->attributes->get('admin');
        
        if($data['GrouppingType']=='month') {
            $stats = Price::getStatistics($data, 'month', !$is_admin ? $created_by : 0);
        }
        else if ($data['GrouppingType']=='none') {
            $stats = Price::getStatistics($data, 'none', !$is_admin ? $created_by : 0);
        }
        else {
            $stats = Price::getStatistics($data, 'day', !$is_admin ? $created_by : 0);
        }

        $formated_stats = array();

        if($data['GrouppingType']=='none') {
            $formated_stats[] = (object)array(
                'name' => 'Total',
                'series' => $this->getTotalDetails($stats)
            );
            // stats by prod
        } else if (!empty($data['ProductsIds'])) {
            foreach ($data['ProductsIds'] as $pId) {
                $detailedStats = $this->getDetailedProductStats($pId, $stats);
                $title_arr = array();
                $product = Product::where('id', $pId)->first();
                if ($product != null) {
                    $title_arr[] = $product->name;
                }

                $formated_stats[] = (object)array(
                    'name' => implode(' / ', $title_arr),
                    'series' => $detailedStats,
                );
            }
        }
        //stats by totals
        else {

            $formated_stats[] = (object)array(
                'name' => 'Total',
                'series' => $this->getDetailedTotalStats($stats)
            );
        }

        return response()->json($formated_stats, 200);
    }
}
