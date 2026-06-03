const { useState, useEffect, useCallback } = React;
const API = window.location.origin;
const WS_URL =
    (window.location.protocol === 'https:' ? 'wss://' : 'ws://') +
    window.location.host +
    '/ws';

function Comments({ postId, userId, userName }) {
    const [items, setItems] = useState([]);
    const [body, setBody] = useState('');
    const [token, setToken] = useState(sessionStorage.getItem('access_token'));
    const [editId, setEditId] = useState(null);
    const [editBody, setEditBody] = useState('');

    const load = useCallback(async () => {
        const res = await fetch(`${API}/api/posts/${postId}/comments`);
        const data = await res.json();
        setItems(data.items || []);
    }, [postId]);

    useEffect(() => { load(); }, [load]);

    // Realtime updates over WebSocket.
    useEffect(() => {
        let ws;
        const connect = () => {
            ws = new WebSocket(WS_URL);
            ws.onmessage = (e) => {
                const msg = JSON.parse(e.data);
                if (msg.type === 'new_comment' && msg.comment.post_id === postId) {
                    setItems(prev => prev.some(c => c.id === msg.comment.id) ? prev : [...prev, msg.comment]);
                } else if (msg.type === 'update_comment') {
                    setItems(prev => prev.map(c => c.id === msg.comment.id ? { ...c, body: msg.comment.body } : c));
                } else if (msg.type === 'delete_comment') {
                    setItems(prev => prev.filter(c => c.id !== msg.comment_id));
                } else if (msg.type === 'user_renamed') {
                    setItems(prev => prev.map(c => c.author_id === msg.user_id ? { ...c, author_name: msg.new_name } : c));
                }
            };
            ws.onclose = () => { ws = null; setTimeout(connect, 3000); };
        };
        connect();
        return () => { if (ws) { ws.onclose = null; ws.close(); } };
    }, [postId]);

    // Attaches the bearer token; on 401 it silently refreshes once and retries.
    const authedFetch = async (url, options = {}) => {
        const call = (tok) => fetch(url, {
            ...options,
            headers: { ...(options.headers || {}), 'Authorization': 'Bearer ' + tok },
        });
        let res = await call(sessionStorage.getItem('access_token'));
        if (res.status === 401) {
            const fresh = await window.boardyAuth.refreshToken();
            if (!fresh) return null;
            setToken(fresh);
            res = await call(fresh);
        }
        return res;
    };

    const add = async (e) => {
        e.preventDefault();
        if (!body.trim()) return;
        const res = await authedFetch(`${API}/api/posts/${postId}/comments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body, author_name: userName }),
        });
        if (res && res.ok) setBody('');
    };

    const saveEdit = async (id) => {
        const res = await authedFetch(`${API}/api/comments/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body: editBody }),
        });
        if (res && res.ok) { setEditId(null); setEditBody(''); }
    };

    const del = async (id) => {
        if (!confirm('Удалить комментарий?')) return;
        await authedFetch(`${API}/api/comments/${id}`, { method: 'DELETE' });
    };

    return (
        <div>
            {token ? (
                <form onSubmit={add} className="mb-3">
                    <textarea className="form-control mb-2" rows="2" value={body}
                        onChange={e => setBody(e.target.value)} placeholder="Ваш комментарий..." />
                    <button className="btn btn-primary btn-sm" type="submit">Отправить</button>
                </form>
            ) : (
                <div className="alert alert-secondary">
                    <button className="btn btn-primary btn-sm" onClick={() => window.boardyAuth.startLogin()}>
                        Войти через OAuth, чтобы комментировать
                    </button>
                </div>
            )}

            {items.map(c => (
                <div className="card mb-2 shadow-sm" key={c.id}>
                    <div className="card-body py-2">
                        {editId === c.id ? (
                            <div>
                                <textarea className="form-control mb-2" value={editBody}
                                    onChange={e => setEditBody(e.target.value)} />
                                <button className="btn btn-success btn-sm me-1" onClick={() => saveEdit(c.id)}>Сохранить</button>
                                <button className="btn btn-secondary btn-sm" onClick={() => setEditId(null)}>Отмена</button>
                            </div>
                        ) : (
                            <div>
                                <p className="mb-1" style={{ whiteSpace: 'pre-wrap' }}>{c.body}</p>
                                <small className="text-muted"><strong>{c.author_name}</strong></small>
                                {Number(c.author_id) === Number(userId) && (
                                    <div className="mt-1">
                                        <button className="btn btn-link btn-sm p-0 me-2"
                                            onClick={() => { setEditId(c.id); setEditBody(c.body); }}>изменить</button>
                                        <button className="btn btn-link btn-sm p-0 text-danger"
                                            onClick={() => del(c.id)}>удалить</button>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            ))}
        </div>
    );
}

const root = document.getElementById('comments-root');
if (root) {
    ReactDOM.createRoot(root).render(
        <Comments
            postId={parseInt(root.dataset.postId)}
            userId={root.dataset.userId}
            userName={root.dataset.userName}
        />
    );
}
