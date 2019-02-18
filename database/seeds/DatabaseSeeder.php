<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $quan = factory(\App\User::class)->create([
            'name' => 'Quan',
            'email' => 'quan@example.com',
            'password' => bcrypt('secret'),
        ]);
        $users = factory(App\User::class, 10)->create();
    }
}
