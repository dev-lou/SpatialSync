<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant admin privileges to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email '{$email}' not found.");

            return 1;
        }

        if ($user->is_admin) {
            $this->info("User '{$user->name}' ({$email}) is already an admin.");

            return 0;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("✓ User '{$user->name}' ({$email}) is now an admin!");

        return 0;
    }
}
