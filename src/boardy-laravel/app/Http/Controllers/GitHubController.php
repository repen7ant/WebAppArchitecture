<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GitHubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
            
            // Ищем пользователя по github_id или создаем нового
            $user = User::updateOrCreate(
                ['github_id' => $githubUser->id],
                [
                    'name' => $githubUser->name ?? $githubUser->nickname ?? 'GitHub User',
                    'email' => $githubUser->email,
                    'password' => bcrypt(Str::random(24)), // Ставим случайный длинный пароль
                ]
            );

            Auth::login($user);

            // Редирект в ленту постов после успешного входа
            return redirect('/posts');
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['oauth' => 'Ошибка авторизации через GitHub.']);
        }
    }
}
