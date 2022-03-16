<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ParentalControlMessages extends Controller
{
    const message_file = 'messages.json';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getmessage()
    {
        $message = array('message' => '', 'date' => date('Y-m-d H:i:s'));
        if (Storage::disk('local')->exists(ParentalControlMessages::message_file)) {
            $message = json_decode(Storage::disk('local')->get(ParentalControlMessages::message_file), true);
        }
        $message['date'] = date('Y-m-d H:i:s');
        return response()->json($message, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storemessage(Request $request)
    {
        $validator = Validator::make($request->only(['message']), [
            'message' => ['required','max:100']
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $message = $validator->valid();

        Storage::disk('local')->put(ParentalControlMessages::message_file, json_encode($message));
        return response()->json($message, 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function deletemessage()
    {
        if (Storage::disk('local')->exists(ParentalControlMessages::message_file)) {
            Storage::disk('local')->delete(ParentalControlMessages::message_file);
        }
    }
}
