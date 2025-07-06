<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\AlumniService;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;

class ServicesController extends Controller
{
    //
    public function createService(StoreServiceRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->respondWithError('Unauthorized', 401);
        }

        if (!$user->hasRole('alumni')) {
            return $this->respondWithError('Forbidden', 403);
        }


        $validatedData = $request->validated();

        try {
            $count = AlumniService::where('alumni_id', $user->id)->count();
            if ($count >= 6) { 
                return $this->respondWithError('You have reached the maximum number of services', 400);
            }
            $exists = AlumniService::where('alumni_id', $user->id)
                ->where('service_type', $validatedData['serviceType'])
                ->where('title', $validatedData['title'])
                ->exists();
            if ($exists) { 
                return $this->respondWithError('Service already exists', 400);
            }
            $service = AlumniService::create([
                'alumni_id' => $user->id,
                'service_type' => $validatedData['serviceType'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
            ]);

            return $this->respondWithSuccess(['service' => $service], 201);
        } catch (\Exception $e) {
            return $this->respondWithError('An error occurred while creating the service: ' . $e->getMessage(), 500);
        }
    }

    public function updateService(UpdateServiceRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->respondWithError('Unauthorized', 401);
        }
        try {
            $service = AlumniService::find($request->id);
            $serviceData = $request->validated();
            if (!$service || $service->alumni_id !== $user->id) {
                return $this->respondWithError('Service not found or unauthorized', 404);
            }
            $exist = AlumniService::where('alumni_id', $user->id)
                ->where('service_type', $serviceData['serviceType'])
                ->where('title', $serviceData['title'])
                ->where('id', '!=', $service->id)
                ->exists();
            if ($exist) {
                return $this->respondWithError('Service already exists', 400);
            }
        
            if (isset($serviceData['description'])) {
                $service->description = $serviceData['description'];
            } else {
                $service->description = null; 
            }
            $service->service_type = $serviceData['serviceType'];
            $service->title = $serviceData['title'];
            $service->save();
            $updated_services = $service->fresh();

        return $this->respondWithSuccess(['services' => $updated_services], 200);
        } catch (\Exception $e) {
            return $this->respondWithError('An error occurred while updating the service: ' . $e->getMessage(), 500);
        }
    }

    public function deleteService(Request $request, $id)
    {
        $user = $request->user();
        if (!$user ) {
            return $this->respondWithError('Unauthorized or Forbidden', 401);
        }
        if (!$id || !is_numeric($id)) {
            return $this->respondWithError('Invalid service ID', 400);
        }

        $service = AlumniService::find($id);
        if (!$service || ($service->alumni_id !== $user->id && !$user->hasAnyRole(['admin', 'staff']))) {
            return $this->respondWithError('Service not found or unauthorized', 404);
        }

        $service->delete();

        return $this->respondWithSuccess(['message' => 'Service deleted successfully'], 200);
    }

    public function listUserServices(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->respondWithError('Unauthorized', 401);
        }
        $user = User::with('profile')->find($user->id);

        $user_services = AlumniService::where('alumni_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->select('id','description', 'service_type', 'title',  'created_at')
        ->get();
        
        return $this->respondWithSuccess([
            'user' => $user,
            'services' => $user_services,
        ]);
    }

    public function listUsedServices(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin', 'staff'])) {
            return $this->respondWithError('Unauthorized or Forbidden', 401);
        }

        $services = AlumniService::whereNotNull('service_type')->join('users', 'alumni_services.alumni_id', '=', 'users.id')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select(
                'alumni_services.*',
                'user_profiles.first_name',
                'user_profiles.last_name',
                'user_profiles.track',
                'user_profiles.intake',
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->respondWithSuccess(['services' => $services]);
    }
    public function listUnusedServices(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin', 'staff'])) {
            return $this->respondWithError('Unauthorized or Forbidden', 401);
        }

        $services = AlumniService::where('service_type', null)->join('users', 'alumni_services.alumni_id', '=', 'users.id')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select(
                'alumni_services.*',
                'user_profiles.first_name',
                'user_profiles.last_name',
                'user_profiles.track',
                'user_profiles.intake',
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->respondWithSuccess(['services' => $services]);
    }

    public function getServiceDetails(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin', 'staff'])) {
            return $this->respondWithError('Unauthorized or Forbidden', 401);
        }

        $service = AlumniService::where('alumni_services.id', $id)
        ->join('users', 'alumni_services.alumni_id', '=', 'users.id')
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->select(
            'alumni_services.*',
            'user_profiles.first_name',
            'user_profiles.last_name',
            'user_profiles.track',
            'user_profiles.intake',
            'users.email'
        )
        ->first();
        if (!$service) {
            return $this->respondWithError('Service not found', 404);
        }

        return $this->respondWithSuccess(['details' => $service]);
    }
    public function evaluateService(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin', 'staff'])) {
            return $this->respondWithError('Unauthorized or Forbidden', 401);
        }

        $service = AlumniService::find($id);
        if (!$service) {
            return $this->respondWithError('Service not found', 404);
        }
        $request->validate([
            'feedback' => 'nullable|string|max:500',
            'evaluation' => 'required|in:positive,neutral,negative',
        ]);
        if (isset($request->feedback)) {
            $service->feedback = $request->feedback;
        }
        $service->evaluation = $request->evaluation;
        $service->save();
        $updatedService = AlumniService::find($id);
        return $this->respondWithSuccess(['service' => $updatedService]);
    }
}
