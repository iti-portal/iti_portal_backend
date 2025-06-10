<?php

namespace App\Http\Controllers;

class Controller
{
    //
    protected function responseWithSuccess($data, $message = null,
     $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    protected function responseWithError($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }


}
