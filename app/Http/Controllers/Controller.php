<?php

namespace App\Http\Controllers;

class Controller
{
    //
    protected function respondWithSuccess($data, $message = null,
     $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
    protected function respondWithError($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }


}
