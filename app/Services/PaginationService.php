<?php

namespace App\Services;

class PaginationService {

    var $PageSize;

    function __construct() {
        $this->PageSize = (int)env('PAGE_SIZE', 20);
    }

    function applyPagination($query, $page, $PageSize = 0) {

        if($PageSize == 0) {
            $PageSize = $this->PageSize;
        }
        
        $count = $query->count();

        return [
            'count'    => $count,
            'page'     => $page,
            'page_size'=> $PageSize,
            'nr_pages' => ceil($count / $this->PageSize),
            'results'  => $query
                        ->skip(($page - 1) * $this->PageSize)
                        ->take($PageSize)
                        ->get()
        ];
    }

    function applyOrder($query, $order_by, $order_dir = 'ASC', $base_table = ''){

        if(strpos($order_by, '.') == false){
            return $query->orderBy($order_by, $order_dir);
        }
        else {
            $fields = explode('.', $order_by);
            $order_by = $fields[1].'.'.$fields[2];
            $related_table = $fields[1];
            $foreign_key = $fields[0];

            return $query
                    ->join($related_table, $base_table.'.'.$foreign_key, '=', $related_table.'.id')
                    ->orderBy($order_by, $order_dir);
        }
    }

}