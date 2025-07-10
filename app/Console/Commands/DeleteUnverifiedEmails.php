<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUnverifiedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-unverified-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users with unverified emails after 24 hours from registration';


    /**
     * Execute the console command.
     */
    public function handle()
    {
       try {
        $count = User::whereNull('email_verified_at')
            ->where('created_at', '<=', now()->subHours(24))
            ->delete();
        $this->info("Unverified users deleted successfully. Total deleted: $count");
    } catch (\Exception $e) {
        $this->error("An error occurred: {$e->getMessage()}");
    }
    }
}
