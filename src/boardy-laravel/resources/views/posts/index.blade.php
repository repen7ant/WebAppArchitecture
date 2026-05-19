@extends('layouts.app')

@section('title', 'Лента постов')

@section('content')
    <h1 class="mb-4">Лента постов</h1>

    @forelse ($posts as $post)
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="card-title">
                    <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                </h3>
                <p class="card-text">{{ Str::limit($post->body, 200) }}</p>
                <p class="text-muted small mb-0">
                    Автор: {{ $post->author->name }} &middot; Дата: {{ $post->created_at->format('d.m.Y H:i') }}
                </p>
            </div>
        </div>
    @empty
        <p>Постов пока нет.</p>
    @endforelse

    <!-- Блок с кнопками пагинации -->
    <div class="mt-4">
        {{ $posts->links('pagination::bootstrap-5') }}
    </div>
@endsection
