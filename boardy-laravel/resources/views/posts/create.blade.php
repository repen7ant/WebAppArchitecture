@extends('layouts.app')

@section('title', 'Создать пост')

@section('content')
    <div class="mb-4">
        <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Назад к ленте</a>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <h1 class="card-title mb-4">Написать новый пост</h1>

            <!-- Форма отправляет POST запрос на маршрут posts.store -->
            <form action="{{ route('posts.store') }}" method="POST">
                @csrf <!-- Обязательный токен защиты -->

                <div class="mb-3">
                    <label for="title" class="form-label">Заголовок</label>
                    <input type="text" name="title" id="title" 
                           class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title') }}" required>
                    
                    <!-- Вывод ошибки валидации для заголовка -->
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label">Текст поста</label>
                    <textarea name="body" id="body" rows="6" 
                              class="form-control @error('body') is-invalid @enderror" 
                              required>{{ old('body') }}</textarea>
                    
                    <!-- Вывод ошибки валидации для текста -->
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Опубликовать</button>
            </form>
        </div>
    </div>
@endsection
