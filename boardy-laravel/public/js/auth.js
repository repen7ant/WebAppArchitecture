import { generateVerifier, generateChallenge, generateState } from './pkce.js';

const CLIENT_ID = document.querySelector('meta[name="passport-client-id"]')?.content;
const REDIRECT_URI = window.location.origin + '/oauth/callback';

export function getAccessToken() {
    return sessionStorage.getItem('access_token');
}

function setAccessToken(token) {
    sessionStorage.setItem('access_token', token);
}

// Step 1: build the PKCE authorization request and redirect to Passport.
export async function startLogin() {
    const verifier = generateVerifier();
    const challenge = await generateChallenge(verifier);
    const state = generateState();

    sessionStorage.setItem('pkce_verifier', verifier);
    sessionStorage.setItem('oauth_state', state);
    sessionStorage.setItem('post_login_redirect', window.location.pathname);

    const params = new URLSearchParams({
        client_id: CLIENT_ID,
        response_type: 'code',
        redirect_uri: REDIRECT_URI,
        code_challenge: challenge,
        code_challenge_method: 'S256',
        state: state,
        scope: '',
    });
    window.location = '/oauth/authorize?' + params;
}

// Step 2: on the callback page, validate state and exchange code for tokens.
export async function handleCallback() {
    const params = new URLSearchParams(window.location.search);
    const code = params.get('code');
    const state = params.get('state');
    if (!code) return null;

    const savedState = sessionStorage.getItem('oauth_state');
    if (state !== savedState) {
        throw new Error('Invalid state — возможна CSRF-атака');
    }
    const verifier = sessionStorage.getItem('pkce_verifier');
    if (!verifier) throw new Error('Нет code_verifier');

    const res = await fetch('/oauth/token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
            grant_type: 'authorization_code',
            client_id: CLIENT_ID,
            code: code,
            code_verifier: verifier,
            redirect_uri: REDIRECT_URI,
        }),
    });
    const data = await res.json();

    sessionStorage.removeItem('pkce_verifier');
    sessionStorage.removeItem('oauth_state');

    if (data.access_token) setAccessToken(data.access_token);
    return data.access_token;
}

// Silent refresh: refresh_token lives in an HttpOnly cookie; the Laravel
// middleware injects it into the request, so we only send grant_type + client_id.
export async function refreshToken() {
    const res = await fetch('/oauth/token', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            grant_type: 'refresh_token',
            client_id: CLIENT_ID,
        }),
    });
    if (!res.ok) {
        startLogin();
        return null;
    }
    const data = await res.json();
    if (data.access_token) setAccessToken(data.access_token);
    return data.access_token;
}

// Expose for the (Babel-compiled, non-module) comments component.
window.boardyAuth = { startLogin, handleCallback, refreshToken, getAccessToken };
