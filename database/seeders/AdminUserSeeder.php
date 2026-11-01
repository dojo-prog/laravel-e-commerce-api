<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the local development administrator.
     */
    public function run(): void
    {
        
        $email = 'admin@dojo.com';
        $password = 'testPass';
        $admin = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($admin === null) {
            $admin = new User(['email' => $email]);
        }

        $admin->name = 'Dojo Admin';
        $admin->role = 'admin';
        $admin->password = $password;
        $admin->email_verified_at ??= now();
        $admin->save();
    }
}
