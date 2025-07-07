<?php

namespace App\Services;

use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsService
{
    public function getAll($perPage = 15)
    {
        try {
            $contacts = ContactUs::paginate($perPage);
            return ['success' => true, 'message' => 'Contact us submissions retrieved successfully.', 'data' => $contacts];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to retrieve contact us submissions: ' . $e->getMessage()];
        }
    }

    public function getById($id)
    {
        try {
            $contact = ContactUs::findOrFail($id);
            return ['success' => true, 'message' => 'Contact us submission retrieved successfully.', 'data' => $contact];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ['success' => false, 'message' => 'Contact us submission not found.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to retrieve contact us submission: ' . $e->getMessage()];
        }
    }

    public function create(Request $request)
    {
        try {
            $contact = ContactUs::create($request->all());
            return ['success' => true, 'message' => 'Contact us submission created successfully.', 'data' => $contact];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to create contact us submission: ' . $e->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $contact = ContactUs::findOrFail($id);
            $contact->delete();
            return ['success' => true, 'message' => 'Contact us submission deleted successfully.'];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ['success' => false, 'message' => 'Contact us submission not found.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete contact us submission: ' . $e->getMessage()];
        }
    }
}
