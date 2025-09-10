<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create-user {email} {password} {name}';
    protected $description = 'Create an admin user for the Filament admin panel';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name');

        try {
            $user = Users::create([
                'email' => $email,
                'password_hash' => Hash::make($password),
                'full_name' => $name,
            ]);

            $this->info("Admin user created successfully!");
            $this->info("Email: {$email}");
            $this->info("You can now login to the admin panel at /admin");
            
        } catch (\Exception $e) {
            $this->error("Error creating admin user: " . $e->getMessage());
        }
    }
}
