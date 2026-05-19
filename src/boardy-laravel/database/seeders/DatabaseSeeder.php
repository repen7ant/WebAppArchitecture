<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Известный тестовый пользователь
        User::factory()->create([
            'name' => 'Тест',
            'email' => 'test@boardy.local',
            'password' => bcrypt('password'),
        ]);

        // 2. 4 случайных пользователя
        $users = User::factory()->count(4)->create();
        
        // Получаем всех пользователей (включая тестового) для привязки
        $allUsers = User::all();

        // 3. 10 случайных постов
        $posts = Post::factory()->count(10)->create([
            'user_id' => fn() => $allUsers->random()->id,
        ]);

        // 4. 25 случайных комментариев к случайным постам от случайных юзеров
        Comment::factory()->count(25)->create([
            'post_id' => fn() => $posts->random()->id,
            'user_id' => fn() => $allUsers->random()->id,
        ]);
    }
}
