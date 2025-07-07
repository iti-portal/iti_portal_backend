<?php

namespace App\Services;

use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsService
{
    public function getAll($perPage = 15)
    {
        return ContactUs::paginate($perPage);
    }

    public function getById($id)
    {
        return ContactUs::findOrFail($id);
    }

    public function create(Request $request)
    {
        return ContactUs::create($request->all());
    }

    public function delete($id)
    {
        $contact = ContactUs::findOrFail($id);
        $contact->delete();
    }
}
