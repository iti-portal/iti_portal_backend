<?php

namespace App\Http\Controllers;

use App\Services\ContactUsService;
use App\Http\Requests\StoreContactUsRequest;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    protected $contactUsService;

    public function __construct(ContactUsService $contactUsService)
    {
        $this->contactUsService = $contactUsService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $response = $this->contactUsService->getAll($perPage);

        if ($response['success']) {
            return response()->json(['message' => $response['message'], 'data' => $response['data']], 200);
        } else {
            return response()->json(['message' => $response['message']], 500);
        }
    }

    public function show($id)
    {
        $response = $this->contactUsService->getById($id);

        if ($response['success']) {
            return response()->json(['message' => $response['message'], 'data' => $response['data']], 200);
        } else {
            return response()->json(['message' => $response['message']], $response['message'] === 'Contact us submission not found.' ? 404 : 500);
        }
    }

    public function store(StoreContactUsRequest $request)
    {
        $response = $this->contactUsService->create($request);

        if ($response['success']) {
            return response()->json(['message' => $response['message'], 'data' => $response['data']], 201);
        } else {
            return response()->json(['message' => $response['message']], 500);
        }
    }

    public function destroy($id)
    {
        $response = $this->contactUsService->delete($id);

        if ($response['success']) {
            return response()->json(['message' => $response['message']], 204);
        } else {
            return response()->json(['message' => $response['message']], $response['message'] === 'Contact us submission not found.' ? 404 : 500);
        }
    }
}
