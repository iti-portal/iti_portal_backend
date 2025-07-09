<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteExpiredAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete accounts that are marked for deletion after 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('DeleteExpiredAccounts command is running via scheduler.');

        $users = User::whereNotNull('marked_for_deletion_at')
            ->where('marked_for_deletion_at', '<=', now())
            ->get();

        foreach ($users as $user){
            $user->delete();
            $this->info("User {$user->id} has been deleted.");
        }
    }
}
