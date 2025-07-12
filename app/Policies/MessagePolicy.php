<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use App\Models\Connection;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->id === $message->receiver_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $receiverId = request()->input('receiver_id');
        if ($user->hasRole('admin')) {
            return true;
        }

        $connectionExists = Connection::where(function ($query) use ($user, $receiverId) {
            $query->where('requester_id', $user->id)
                  ->where('addressee_id', $receiverId);
        })->orWhere(function ($query) use ($user, $receiverId) {
            $query->where('requester_id', $receiverId)
                  ->where('addressee_id', $user->id);
        })->where('status', 'accepted')->exists();

        return $connectionExists;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id;
    }
}
