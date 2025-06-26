<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConnectionController extends Controller
{
    /**
     * Send a connection request to another user
     */
    public function connect(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500'
        ]);

        $currentUser = $request->user();
        $targetUserId = $request->user_id;

        // Prevent self-connection
        if ($currentUser->id == $targetUserId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot connect with yourself'
            ], 400);
        }

        // Check if connection already exists
        $existingConnection = Connection::where(function ($query) use ($currentUser, $targetUserId) {
            $query->where('requester_id', $currentUser->id)
                  ->where('addressee_id', $targetUserId);
        })->orWhere(function ($query) use ($currentUser, $targetUserId) {
            $query->where('requester_id', $targetUserId)
                  ->where('addressee_id', $currentUser->id);
        })->first();

        if ($existingConnection) {
            $status = $existingConnection->status;
            $message = match($status) {
                'pending' => 'Connection request already sent',
                'accepted' => 'You are already connected with this user',
                'declined' => 'Connection request was previously declined'
            };
            
            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create connection request
            $connection = Connection::create([
                'requester_id' => $currentUser->id,
                'addressee_id' => $targetUserId,
                'status' => 'pending',
                'message' => $request->message
            ]);

            // Create notification for the target user
            Notification::create([
                'user_id' => $targetUserId,
                'type' => 'connection_request',
                'title' => 'New Connection Request',
                'message' => $currentUser->full_name . ' wants to connect with you',
                'data' => [
                    'connection_id' => $connection->id,
                    'requester_id' => $currentUser->id,
                    'requester_name' => $currentUser->full_name,
                    'requester_avatar' => $currentUser->profile?->profile_picture,
                    'message' => $request->message
                ],
                'is_read' => false
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Connection request sent successfully',
                'data' => [
                    'connection' => $connection->load([ 'requester.profile:user_id,first_name,last_name,username,summary,profile_picture,branch,track,intake,program,student_status', 
                                                        'addressee.profile:user_id,first_name,last_name,username,summary,profile_picture,branch,track,intake,program,student_status' 
                                                    ])
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send connection request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept a connection request
     */
    public function acceptConnection(Request $request): JsonResponse
    {
        $request->validate([
            'connection_id' => 'required|exists:connections,id'
        ]);

        $currentUser = $request->user();
        $connection = Connection::with(['requester.profile', 'addressee.profile'])
            ->where('id', $request->connection_id)
            ->where('addressee_id', $currentUser->id)
            ->where('status', 'pending')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection request not found or already processed'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Accept the connection
            $connection->update(['status' => 'accepted']);

            // Create notification for the requester
            Notification::create([
                'user_id' => $connection->requester_id,
                'type' => 'connection_accepted',
                'title' => 'Connection Accepted',
                'message' => $currentUser->full_name . ' accepted your connection request',
                'data' => [
                    'connection_id' => $connection->id,
                    'accepter_id' => $currentUser->id,
                    'accepter_name' => $currentUser->full_name,
                    'accepter_avatar' => $currentUser->profile?->profile_picture
                ],
                'is_read' => false
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Connection request accepted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept connection request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject/Decline a connection request
     */
    public function rejectConnection(Request $request): JsonResponse
    {
        $request->validate([
            'connection_id' => 'required|exists:connections,id'
        ]);

        $currentUser = $request->user();
        $connection = Connection::where('id', $request->connection_id)
            ->where('addressee_id', $currentUser->id)
            ->where('status', 'pending')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection request not found or already processed'
            ], 404);
        }

        try {
            $connection->update(['status' => 'declined']);

            return response()->json([
                'success' => true,
                'message' => 'Connection request declined successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline connection request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove/Disconnect from an existing connection
     */
    public function unconnect(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUser = $request->user();
        $targetUserId = $request->user_id;

        try {
            $connection = Connection::where(function ($query) use ($currentUser, $targetUserId) {
                $query->where('requester_id', $currentUser->id)
                      ->where('addressee_id', $targetUserId);
            })->orWhere(function ($query) use ($currentUser, $targetUserId) {
                $query->where('requester_id', $targetUserId)
                      ->where('addressee_id', $currentUser->id);
            })->where('status', 'accepted')->first();

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to find connection, please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection found with this user'
            ], 404);
        }

        try {
            $connection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Connection removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove connection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all connected users (accepted connections)
     */
    public function getConnectedUsers(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        $query = Connection::with(['requester.profile', 'addressee.profile'])
            ->where('status', 'accepted')
            ->where(function ($query) use ($currentUser) {
                $query->where('requester_id', $currentUser->id)
                      ->orWhere('addressee_id', $currentUser->id);
            });

        $connections = $query->orderBy('updated_at', 'desc')->paginate($perPage);

        // Transform the data to show the connected user (not the current user)
        $connections->getCollection()->transform(function ($connection) use ($currentUser) {
            $connectedUser = $connection->requester_id == $currentUser->id 
                ? $connection->addressee 
                : $connection->requester;

            return [
                'connection_id' => $connection->id,
                'connected_at' => $connection->updated_at,
                'user' => [
                    'id' => $connectedUser->id,
                    'email' => $connectedUser->email,
                    'full_name' => $connectedUser->full_name,
                    'profile' => $connectedUser->profile ? [
                        'username' => $connectedUser->profile->username,
                        'profile_picture' => $connectedUser->profile->profile_picture,
                        'summary' => $connectedUser->profile->summary,
                        'branch' => $connectedUser->profile->branch,
                        'program' => $connectedUser->profile->program,
                        'intake' => $connectedUser->profile->intake,
                        'track' => $connectedUser->profile->track,
                    ] : null
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Connected users retrieved successfully',
            'data' => $connections
        ]);
    }

    /**
     * Get pending connection requests (received by current user)
     */
    public function getPendingRequests(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $perPage = $request->get('per_page', 15);

        $pendingRequests = Connection::with(['requester.profile'])
            ->where('addressee_id', $currentUser->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform the data to show requester information
        $pendingRequests->getCollection()->transform(function ($connection) {
            return [
                'connection_id' => $connection->id,
                'message' => $connection->message,
                'requested_at' => $connection->created_at,
                'requester' => [
                    'id' => $connection->requester->id,
                    'email' => $connection->requester->email,
                    'full_name' => $connection->requester->full_name,
                    'profile' => $connection->requester->profile ? [
                        'username' => $connection->requester->profile->username,
                        'profile_picture' => $connection->requester->profile->profile_picture,
                        'summary' => $connection->requester->profile->summary,
                        'branch' => $connection->requester->profile->branch,
                        'program' => $connection->requester->profile->program,
                        'intake' => $connection->requester->profile->intake,
                        'track' => $connection->requester->profile->track,
                    ] : null
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Pending connection requests retrieved successfully',
            'data' => $pendingRequests
        ]);
    }

    /**
     * Get sent connection requests (sent by current user)
     */
    public function getSentRequests(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $perPage = $request->get('per_page', 15);

        $sentRequests = Connection::with(['addressee.profile'])
            ->where('requester_id', $currentUser->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform the data to show addressee information
        $sentRequests->getCollection()->transform(function ($connection) {
            return [
                'connection_id' => $connection->id,
                'message' => $connection->message,
                'requested_at' => $connection->created_at,
                'addressee' => [
                    'id' => $connection->addressee->id,
                    'email' => $connection->addressee->email,
                    'full_name' => $connection->addressee->full_name,
                    'profile' => $connection->addressee->profile ? [
                        'username' => $connection->addressee->profile->username,
                        'profile_picture' => $connection->addressee->profile->profile_picture,
                        'summary' => $connection->addressee->profile->summary,
                        'branch' => $connection->addressee->profile->branch,
                        'program' => $connection->addressee->profile->program,
                        'intake' => $connection->addressee->profile->intake,
                        'track' => $connection->addressee->profile->track,
                    ] : null
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Sent connection requests retrieved successfully',
            'data' => $sentRequests
        ]);
    }

    /**
     * Get connection status with a specific user
     */
    public function getConnectionStatus(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUser = $request->user();
        $targetUserId = $request->user_id;

        if ($currentUser->id == $targetUserId) {
            return response()->json([
                'success' => true,
                'status' => 'self'
            ]);
        }

        $connection = Connection::where(function ($query) use ($currentUser, $targetUserId) {
            $query->where('requester_id', $currentUser->id)
                  ->where('addressee_id', $targetUserId);
        })->orWhere(function ($query) use ($currentUser, $targetUserId) {
            $query->where('requester_id', $targetUserId)
                  ->where('addressee_id', $currentUser->id);
        })->first();

        if (!$connection) {
            return response()->json([
                'success' => true,
                'status' => 'not_connected',
                'can_connect' => true
            ]);
        }

        $isRequester = $connection->requester_id == $currentUser->id;

        return response()->json([
            'success' => true,
            'status' => $connection->status,
            'is_requester' => $isRequester,
            'connection_id' => $connection->id,
            'requested_at' => $connection->created_at
        ]);
    }
}