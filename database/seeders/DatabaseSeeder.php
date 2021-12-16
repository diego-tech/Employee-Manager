<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $workplace_array = ['Directivo','RRHH','Empleado'];

        for ($i = 1; $i < 20; $i++) {
            DB::table('users')->insert([
                'name' => Str::random(20),
                'email' => Str::random(10) . '@gmail.com',
                'password' => Hash::make('password'),
                'workplace' => Arr::random($workplace_array),
                'salary' => Str::random(10) . '$',
                'biography' => Str::random(200),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }


        DB::table('users')->insert([
            'name' => "Admin",
            'email' => "admin@admin.admin",
            'password' => Hash::make('admin'),
            'workplace' => "Directivo",
            'salary' => 0,
            'biography' => "",
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
