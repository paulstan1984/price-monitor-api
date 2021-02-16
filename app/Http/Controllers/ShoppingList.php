<?php

namespace App\Http\Controllers;

use App\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
use App\Services\PaginationService;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class ShoppingList extends Controller
{

    protected $paginationService;

    public function __construct(PaginationService $paginationService) {

        $this->paginationService = $paginationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shopping_list = json_decode(Storage::disk('local')->get(env('SHOPPING_LIST_FILE')));
        return response()->json($shopping_list, 200);
    }

    /**
     * Price a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $shopping_list = json_encode($request->all());
        Storage::disk('local')->put(env('SHOPPING_LIST_FILE'), $shopping_list);
        return response()->json($request->all(), 200);
    }
    
    public function testOCR() { 

        $fileContent  = Storage::disk('local')->get('bon.jpg');
        
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$storagePath.'/GooglePlusMetrics-efbcd5738989.json');

        $imageAnnotator = new ImageAnnotatorClient();

        # annotate the image
        $ocr_response = $imageAnnotator->textDetection($fileContent);
        $texts = $ocr_response->getTextAnnotations();

        $response = [
            'count' => count($texts),
            'words' => []
        ];
        
        foreach ($texts as $text) {
            $response['words'][]=$text->getDescription();
        }
    
        $imageAnnotator->close();
    

        return response()->json($response, 200);
    }
}
