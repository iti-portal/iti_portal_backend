<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::where('user_one_id', $user->id)
            ->orWhere('user_two_id', $user->id)
            ->with(['userOne', 'userTwo', 'messages' => function ($query) {
                $query->latest();
            }])
            ->get();

        return response()->json($conversations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $sender = Auth::user();
        $receiverId = $request->input('receiver_id');

        $conversation = Conversation::where(function ($query) use ($sender, $receiverId) {
            $query->where('user_one_id', $sender->id)
                  ->where('user_two_id', $receiverId);
        })->orWhere(function ($query) use ($sender, $receiverId) {
            $query->where('user_one_id', $receiverId)
                  ->where('user_two_id', $sender->id);
        })->first();

        if ($conversation) {
            return response()->json($conversation);
        }

        $conversation = Conversation::create([
            'user_one_id' => $sender->id,
            'user_two_id' => $receiverId,
        ]);

        return response()->json($conversation, 201);
    }
}
