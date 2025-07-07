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
        $contacts = $this->contactUsService->getAll($perPage);
        return response()->json($contacts);
    }

    public function show($id)
    {
        $contact = $this->contactUsService->getById($id);
        return response()->json($contact);
    }

    public function store(StoreContactUsRequest $request)
    {
        $contact = $this->contactUsService->create($request);
        return response()->json($contact, 201);
    }

    public function destroy($id)
    {
        $this->contactUsService->delete($id);
        return response()->json(null, 204);
    }
}
