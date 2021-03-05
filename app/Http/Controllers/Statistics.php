<?php

namespace App\Http\Controllers;

use App\Price;
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
            'ProductIds' => ['array', 'numericarray'],
            'StoresIds' => ['array', 'numericarray'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $stats = Price::getStatistics($data);

        // should format the response as follow
        // multi = [
        //     {
        //       "name": "Igienol / Profi",
        //       "series": [
        //         {
        //           "name": "2021-01",
        //           "value": 4
        //         },
        //         {
        //           "name": "2021-02",
        //           "value": 5
        //         },
        //         {
        //           "name": "2021-03",
        //           "value": 6
        //         }
        //       ]
        //     },
        
        //     {
        //       "name": "Igienol / Lidl",
        //       "series": [
        //         {
        //           "name": "2021-01",
        //           "value": 4.2
        //         },
        //         {
        //           "name": "2021-02",
        //           "value": 4.1
        //         },
        //         {
        //           "name": "2021-03",
        //           "value": 6.7
        //         }
        //       ]
        //     },
        
        //     {
        //       "name": "Pâine / Profi",
        //       "series": [
        //         {
        //           "name": "2021-01",
        //           "value": 5
        //         },
        //         {
        //           "name": "2021-02",
        //           "value": 5
        //         },
        //         {
        //           "name": "2021-03",
        //           "value": 6
        //         }
        //       ]
        //     },
        //     {
        //       "name": "Pâine / Lidl",
        //       "series": [
        //         {
        //           "name": "2021-01",
        //           "value": 6
        //         },
        //         {
        //           "name": "2021-02",
        //           "value": 5
        //         },
        //         {
        //           "name": "2021-03",
        //           "value": 6
        //         }
        //       ]
        //     }
        //   ];

        return response()->json($stats, 200);
    }
}
