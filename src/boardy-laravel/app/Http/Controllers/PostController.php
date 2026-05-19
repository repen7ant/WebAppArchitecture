<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Загружаем посты вместе с авторами (чтобы избежать проблемы N+1),
        // сортируем от новых к старым и разбиваем по 10 на страницу
        $posts = \App\Models\Post::with('author')->latest()->paginate(10);
        
        return view('posts.index', compact('posts'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        // 1. Валидация входных данных
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
        ]);

        // 2. Получаем текущего пользователя (или берем тестового юзера с ID 1, пока нет системы логина)
        $user = $request->user() ?? \App\Models\User::first();

        // 3. Создаем пост через связь (user_id подставится автоматически)
        $post = $user->posts()->create($data);

        // 4. Редирект на страницу созданного поста с flash-сообщением
        return redirect()->route('posts.show', $post)
            ->with('success', 'Пост успешно опубликован!');
    }
    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Post $post)
    {
        // Подгружаем автора поста и авторов всех комментариев (чтобы избежать проблемы N+1 запросов)
        $post->load('author', 'comments.author');

        return view('posts.show', compact('post'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        // Проверка прав: может ли текущий юзер редактировать (update) этот пост
        $this->authorize('update', $post);
        
        return view('posts.edit', compact('post'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
        ]);

        $post->update($data);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Пост успешно обновлен!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Для удаления проверяем право delete
        $this->authorize('delete', $post);
        
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Пост успешно удален!');
    }
}
