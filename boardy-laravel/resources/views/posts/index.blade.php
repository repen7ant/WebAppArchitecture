@extends('layouts.app')

@section('title', 'Лента постов')

@section('content')
    <h1 class="mb-4">Лента постов</h1>

    <div id="posts-feed">
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

        <div class="mt-4">
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
    const wsUrl = 'wss://boardy-api.emrysdev.xyz/ws'

    function connect() {
        const ws = new WebSocket(wsUrl)
        ws.onopen    = () => console.log('WS connected')
        ws.onmessage = (e) => {
            const msg = JSON.parse(e.data)
            if (msg.type === 'new_post') prependPost(msg.post)
        }
        ws.onclose = () => setTimeout(connect, 3000)
    }

    function prependPost(post) {
        const feed = document.getElementById('posts-feed')
        if (!feed) return
        const el = document.createElement('div')
        el.className = 'card mb-3'
        el.innerHTML = `
            <div class="card-body">
                <h3 class="card-title">${escapeHtml(post.title)}</h3>
                <p class="card-text">${escapeHtml(post.body)}</p>
                <p class="text-muted small mb-0">Автор: ${escapeHtml(post.author)}</p>
            </div>`
        feed.prepend(el)
    }

    function escapeHtml(str) {
        const d = document.createElement('div')
        d.textContent = str
        return d.innerHTML
    }

    connect()
    </script>
@endsection
