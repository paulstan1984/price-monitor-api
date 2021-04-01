<?php

namespace App\Http\Controllers;

use App\Price;
use App\Product;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;

Validator::extend('numericarray', function($attribute, $value, $parameters)
{
    foreach($value as $v) {
        if(!is_int($v)) return false;
    }
    return true;
});

class Statistics extends Controller
{
    private function getDetailedStats($pId, $sId, $stats) {
        $returnData = array();
        foreach($stats as $d){
            if($d['ProductId']==$pId && $d['StoreId']==$sId){
                $returnData[]=(object)array(
                    "name" => date('Y-m-d', strtotime($d['Date'])),
                    "value" => $d['MaxPrice'],
                );
            }
        }

        return $returnData;
    }

    private function getDetailedStoreStats($sId, $stats) {
        $returnData = array();
        foreach($stats as $d){
            if($d['StoreId']==$sId){
                $returnData[]=(object)array(
                    "name" => date('Y-m-d', strtotime($d['Date'])),
                    "value" => $d['MaxPrice'],
                );
            }
        }

        return $returnData;
    }

    private function getDetailedProductStats($pId, $stats) {
        $returnData = array();
        foreach($stats as $d){
            if($d['ProductId']==$pId){
                $returnData[]=(object)array(
                    "name" => date('Y-m-d', strtotime($d['Date'])),
                    "value" => $d['MaxPrice'],
                );
            }
        }

        return $returnData;
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
            'StoresIds' => ['array', 'numericarray'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $stats = Price::getStatistics($data);

        $formated_stats = array();
        //stats by store and prod
        if(!empty($data['ProductsIds']) && !empty($data['StoresIds'])){
            foreach($data['ProductsIds'] as $pId) {
                foreach($data['StoresIds'] as $sId) {
                    $detailedStats = $this->getDetailedStats($pId, $sId, $stats);
                    $title_arr = array();
                    $store = Store::where('id', $sId)->first();
                    if($store!=null){
                        $title_arr[]=$store->name;
                    }
                    $product = Product::where('id', $pId)->first();
                    if($product!=null){
                        $title_arr[]=$product->name;
                    }

                    $formated_stats[]=(object)array(
                        'name' => implode(' / ', $title_arr),
                        'series' => $detailedStats,
                    );
                }
            }
        // stats by prod
        } else if (!empty($data['ProductsIds']) && empty($data['StoresIds'])){
            foreach($data['ProductsIds'] as $pId) {
                $detailedStats = $this->getDetailedProductStats($pId, $stats);
                $title_arr = array();
                $product = Product::where('id', $pId)->first();
                if($product!=null){
                    $title_arr[]=$product->name;
                }

                $formated_stats[]=(object)array(
                    'name' => implode(' / ', $title_arr),
                    'series' => $detailedStats,
                );
            }
        // stats by store
        } else if (empty($data['ProductsIds']) && !empty($data['StoresIds'])){
            foreach($data['StoresIds'] as $sId) {
                $detailedStats = $this->getDetailedStoreStats($sId, $stats);
                $title_arr = array();
                $store = Store::where('id', $sId)->first();
                if($store!=null){
                    $title_arr[]=$store->name;
                }
                
                $formated_stats[]=(object)array(
                    'name' => implode(' / ', $title_arr),
                    'series' => $detailedStats,
                );
            }
        }
        
        return response()->json($formated_stats, 200);
    }
}
