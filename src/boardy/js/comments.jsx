const { useState, useEffect } = React;

const API = 'https://boardy-api.emrysdev.xyz';
const POST_ID = 1;

function CommentApp() {
    const [items, setItems] = useState([]);
    const [text, setText] = useState('');
    const [editId, setEditId] = useState(null);
    const [editText, setEditText] = useState('');
    
    const [jwt, setJwt] = useState(null);

    const load = async () => {
        try {
            const res = await fetch(`${API}/api/posts/${POST_ID}/comments`);
            const data = await res.json();
            setItems(data.items);
        } catch (e) {
            console.error("Ошибка загрузки", e);
        }
    };

    useEffect(() => {
        load();

        fetch('/api/me.php', { credentials: 'include' })
            .then(r => {
                if (!r.ok) return null;
                return r.json();
            })
            .then(data => {
                if (data && data.token) {
                    setJwt(data.token);
                    console.log("JWT успешно получен:", data.token);
                }
            })
            .catch(() => setJwt(null));
    }, []);

    const getAuthHeaders = () => {
        const headers = { 'Content-Type': 'application/json' };
        if (jwt) {
            headers['Authorization'] = 'Bearer ' + jwt;
        }
        return headers;
    };

    const add = async () => {
        if (!text.trim()) return;

        const headers = {
            'Content-Type': 'application/json'
        };

        if (jwt) {
            headers['Authorization'] = 'Bearer ' + jwt;
        }

        await fetch(`${API}/api/posts/${POST_ID}/comments`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ body: text })
        });

        setText('');
        load();
    };

    const save = async (id) => {
        await fetch(`${API}/api/comments/${id}`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify({ body: editText })
        });
        setEditId(null);
        load();
    };

    const del = async (id) => {
        if (!confirm('Удалить этот комментарий?')) return;
        const headers = {};
        if (jwt) {
            headers['Authorization'] = 'Bearer ' + jwt;
        }

        await fetch(`${API}/api/comments/${id}`, { 
            method: 'DELETE',
            headers: headers
        });
        load();
    };

    return (
        <div className="card shadow-sm">
            <div className="card-header bg-primary text-white">
                <h3 className="mb-0">Комментарии</h3>
            </div>
            <div className="card-body">
                {items.map(item => (
                    <div key={item.id} className="card mb-3 comment-card">
                        <div className="card-body">
                            <div className="d-flex justify-content-between border-bottom pb-2 mb-2">
                                <strong className="text-primary">{item.author_name}</strong>
                                <small className="text-muted">{item.created_at}</small>
                            </div>

                            {editId === item.id ? (
                                <div className="input-group">
                                    <input 
                                        className="form-control" 
                                        value={editText} 
                                        onChange={e => setEditText(e.target.value)} 
                                    />
                                    <button className="btn btn-success" onClick={() => save(item.id)}>✅</button>
                                    <button className="btn btn-secondary" onClick={() => setEditId(null)}>❌</button>
                                </div>
                            ) : (
                                <div>
                                    <p className="card-text">{item.body}</p>
                                    {/* Показываем кнопки редактирования/удаления только авторизованным (jwt !== null) */}
                                    {/* В идеале тут еще проверять, что ID автора равен ID текущего юзера, но пока так */}
                                    {jwt && (
                                        <div className="d-flex gap-2">
                                            <button 
                                                className="btn btn-sm btn-outline-secondary" 
                                                onClick={() => { setEditId(item.id); setEditText(item.body); }}
                                            >Редактировать</button>
                                            <button 
                                                className="btn btn-sm btn-outline-danger" 
                                                onClick={() => del(item.id)}
                                            >Удалить</button>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                ))}

                <div className="mt-4 pt-3 border-top">
                    <h5>Оставить комментарий</h5>
                    {jwt ? (
                        <div className="input-group">
                            <input 
                                className="form-control" 
                                placeholder="Напишите что-нибудь..." 
                                value={text} 
                                onChange={e => setText(e.target.value)} 
                            />
                            <button className="btn btn-primary" onClick={add}>Отправить</button>
                        </div>
                    ) : (
                        <p className="text-muted">Пожалуйста, <a href="/login.php">войдите</a>, чтобы оставить комментарий.</p>
                    )}
                </div>
            </div>
        </div>
    );
}

const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(<CommentApp />);
