// PKCE helpers (Proof Key for Code Exchange) for the public SPA client.

export function generateVerifier() {
    const arr = new Uint8Array(32);
    crypto.getRandomValues(arr);
    return base64UrlEncode(arr);
}

// code_challenge = base64url( SHA-256(verifier) )
export async function generateChallenge(verifier) {
    const data = new TextEncoder().encode(verifier);
    const hash = await crypto.subtle.digest('SHA-256', data);
    return base64UrlEncode(new Uint8Array(hash));
}

// state — random value protecting the callback against CSRF.
export function generateState() {
    return generateVerifier();
}

function base64UrlEncode(bytes) {
    return btoa(String.fromCharCode(...bytes))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}
