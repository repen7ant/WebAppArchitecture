@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <!-- Кнопка назад -->
    <div class="mb-4">
        <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Назад к ленте</a>
    </div>

    <!-- Сам пост -->
    <div class="card mb-5">
        <div class="card-body">
            <h1 class="card-title">{{ $post->title }}</h1>
            <p class="text-muted small">
                Автор: {{ $post->author->name }} &middot; {{ $post->created_at->format('d.m.Y H:i') }}
            </p>
            <p class="card-text fs-5" style="white-space: pre-wrap;">{{ $post->body }}</p>
        </div>
    </div>

    <!-- Кнопки управления постом (видны только автору) -->
    <div class="mb-4">
        @can('update', $post)
            <a href="{{ route('posts.edit', $post) }}" class="btn btn-warning btn-sm">Редактировать</a>
        @endcan

        @can('delete', $post)
            <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE') <!-- Подмена метода POST на DELETE -->
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Точно удалить?')">Удалить</button>
            </form>
        @endcan
    </div>

    <!-- Блок комментариев: React + FastAPI (boardy_api) через OAuth-токен -->
    <h3 class="mb-3">Комментарии</h3>
    <div id="comments-root"
         data-post-id="{{ $post->id }}"
         data-user-id="{{ auth()->id() }}"
         data-user-name="{{ auth()->user()?->name }}"></div>

    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script type="module" src="/js/auth.js"></script>
    <script type="text/babel" data-presets="react" src="/js/comments.jsx"></script>
@endsection
