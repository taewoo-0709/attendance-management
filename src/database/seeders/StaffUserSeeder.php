<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class StaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $staffs = [
            ['name' => '佐藤 一郎', 'email' => 'staff1@example.com'],
            ['name' => '鈴木 花子', 'email' => 'staff2@example.com'],
            ['name' => '田中 次郎', 'email' => 'staff3@example.com'],
            ['name' => '山田 優子', 'email' => 'staff4@example.com'],
        ];

        foreach ($staffs as $staff) {
            User::updateOrCreate(
                ['email' => $staff['email']],
                [
                    'name' => $staff['name'],
                    'password' => Hash::make('coachtech123'),
                    'is_admin' => 0,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}