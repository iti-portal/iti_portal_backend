<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Message;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Conversation::class => ConversationPolicy::class,
        Message::class => MessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Broadcast::routes();

        Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
            return $user->id === Conversation::find($conversationId)->user_one_id || 
                   $user->id === Conversation::find($conversationId)->user_two_id;
        });
    }
}
