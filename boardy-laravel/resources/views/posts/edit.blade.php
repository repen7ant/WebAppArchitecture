@extends('layouts.app')

@section('title', 'Редактировать пост')

@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <h1 class="card-title mb-4">Редактирование поста</h1>

            <form action="{{ route('posts.update', $post) }}" method="POST">
                @csrf
                @method('PUT') <!-- Подмена метода POST на PUT -->

                <div class="mb-3">
                    <label for="title" class="form-label">Заголовок</label>
                    <input type="text" name="title" id="title" 
                           class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title', $post->title) }}" required>
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label">Текст</label>
                    <textarea name="body" id="body" rows="6" 
                              class="form-control @error('body') is-invalid @enderror" 
                              required>{{ old('body', $post->body) }}</textarea>
                </div>

                <button type="submit" class="btn btn-success">Сохранить изменения</button>
                <a href="{{ route('posts.show', $post) }}" class="btn btn-outline-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection
