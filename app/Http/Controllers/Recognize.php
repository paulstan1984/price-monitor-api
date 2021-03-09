<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;

const unwanted_array = array(    
'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
'ă'=>'a' );


class Recognize extends Controller
{
    var $API_Key;
    var $API_URL;

    public function __construct() {
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$storagePath.'/GooglePlusMetrics-efbcd5738989.json');
        $this->API_Key = env('GOOGLE_API_KEY');
        $this->API_URL = 'https://vision.googleapis.com/v1/images:annotate?key='.$this->API_Key;
    }

    function httpPost($url, $data)
    {
        // print json_encode($data);
        // exit;
        // Create a new cURL resource
        $ch = curl_init($url);

        $payload = json_encode($data);

        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the POST request
        $result = curl_exec($ch);

        // Close cURL resource
        curl_close($ch);

        return $result;
    }

    public function invoice(Request $request) { 

        $fileContent  = $request->getContent();

        $GoogleOCRRequest = (object) array(
            'requests' => array(
                (object) array(
                    'image' => (object) array('content' => base64_encode($fileContent)),
                    'features' => array(
                        (object) array('type' => 'TEXT_DETECTION')
                    )
                )
            )
        );

        
        
        $response = json_decode($this->httpPost($this->API_URL, $GoogleOCRRequest), true);

        try{
            $lines_str = $response['responses'][0]['textAnnotations'][0]['description'];
            $lines = array_filter(preg_split('/\r\n|\r|\n/', $lines_str), function($value) { return !empty($value);});
            return response()->json($lines, 200);
        }
        catch(Exception $ex){
            return response()->json($ex, 400);
        }
    }

    public function getPrices(Request $request) { 

        $data = $request->all();
        
        $shoppingList = $this->getShoppingListPrices($data['shopping_list'], $data['text_lines']);

        return response()->json($shoppingList, 200);
    }
    
    private function getShoppingListPrices($shopping_list, $lines) {


        foreach($shopping_list["items"] as &$item){
            $item['price'] = $this->getPrice($item['product']['name'], $lines);
        }
        return $shopping_list;
    }

    private function getPrice($prodName, $lines) {
        $price = 0;

        $prodName = strtolower($prodName);

        $pos = -1;
        $price_marker = strtolower('BUC X');
        for($i=0;$i<count($lines); $i++){
            $prodName = strtr( $prodName, unwanted_array );
            $lines[$i] = strtr( $lines[$i], unwanted_array);
            if(strpos(strtolower($lines[$i]), $prodName) !== FALSE){
                $pos = $i;
                break;
            }
        }

        if($pos >= 0){
            $price = 1;
            for($i=1;$i<3;$i++) {
                $price_pos = strpos(strtolower($lines[$pos+$i]), $price_marker);
                if($price_pos !== FALSE){
                    $price_line = substr($lines[$pos+$i], $price_pos + strlen($price_marker));
                    $price_line = trim($price_line);
                    $price_line = str_replace( '. ', '.', $price_line);
                    $price = explode(' ', $price_line)[0];
                }
            }
        }

        return $price;
    }
}
