<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(CreateNewUser::class)->create([
            'name' => 'Lester Hurtado',
            'email' => 'devops@joy-nostalg.com',
            'password' => '#Password1',
            'password_confirmation' => '#Password1',
        ]);
    }
}
