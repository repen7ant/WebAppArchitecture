<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидация: проверяем, что текст есть, а post_id ссылается на существующий пост
        $data = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'body' => 'required|string|max:1000',
        ]);

        // 2. Создаем комментарий через связь с текущим юзером
        // user_id подставится автоматически, а post_id и body возьмутся из $data
        $request->user()->comments()->create($data);

        // 3. Возвращаем пользователя обратно на ту же страницу (на страницу поста)
        return back()->with('success', 'Комментарий успешно добавлен!');
    }
}
