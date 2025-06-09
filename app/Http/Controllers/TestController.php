<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function index()
    {
        return $this->responseWithSuccess(['message' => 'Test successful']);
    }

    public function error()
    {
        return $this->responseWithError('This is a test error', 500);
    }


}