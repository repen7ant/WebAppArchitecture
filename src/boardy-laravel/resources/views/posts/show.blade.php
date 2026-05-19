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

    <!-- Блок комментариев -->
    <h3>Комментарии ({{ $post->comments->count() }})</h3>

    @forelse ($post->comments as $comment)
        <div class="card mb-3 shadow-sm">
            <div class="card-body py-2">
                <p class="mb-1" style="white-space: pre-wrap;">{{ $comment->body }}</p>
                <small class="text-muted">
                    <strong>{{ $comment->author->name }}</strong> &middot; {{ $comment->created_at->format('d.m.Y H:i') }}
                </small>
            </div>
        </div>
    @empty
        <p class="text-muted">Комментариев пока нет. Будьте первым!</p>
    @endforelse

    <hr class="my-4">

    <!-- Форма добавления комментария (только для авторизованных) -->
    @auth
        <h4>Добавить комментарий</h4>
        <form action="{{ route('comments.store') }}" method="POST">
            @csrf
            <!-- Скрытое поле для передачи ID поста -->
            <input type="hidden" name="post_id" value="{{ $post->id }}">

            <div class="mb-3">
                <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="3" required placeholder="Напишите ваш комментарий..."></textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Отправить</button>
        </form>
    @else
        <div class="alert alert-secondary">
            Для добавления комментариев необходимо авторизоваться.
        </div>
    @endauth
@endsection
