<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="passport-client-id" content="{{ env('PASSPORT_SPA_CLIENT_ID') }}">
    <title>Завершаем вход…</title>
</head>
<body>
    <p style="font-family: sans-serif; padding: 2rem;">Завершаем вход…</p>
    <script type="module">
        import { handleCallback } from '/js/auth.js';
        handleCallback()
            .then(() => {
                window.location.href = sessionStorage.getItem('post_login_redirect') || '/posts';
            })
            .catch((err) => {
                document.body.innerHTML =
                    '<p style="font-family: sans-serif; padding: 2rem; color: #b00;">Ошибка входа: ' + err.message + '</p>';
            });
    </script>
</body>
</html>
