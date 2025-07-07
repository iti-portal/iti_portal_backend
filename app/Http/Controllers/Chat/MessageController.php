<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\NewMessage;


class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        return response()->json($conversation->messages()->with('sender')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Message::class);

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string',
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

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $sender->id,
                'user_two_id' => $receiverId,
            ]);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'body' => $request->input('body'),
        ]);

        broadcast(new NewMessage($message))->toOthers();

        return response()->json($message->load('sender'));
    }
}
