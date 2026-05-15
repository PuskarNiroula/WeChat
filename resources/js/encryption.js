// Derive shared key between current user and another user
async function getSharedKey(otherUserId) {
    if (window._sharedKeyCache && window._sharedKeyCache[otherUserId]) {
        return window._sharedKeyCache[otherUserId];
    }
    const userId=localStorage.getItem('user_id');

    const privateKeyJwk = JSON.parse(localStorage.getItem(`private_key_${userId}`));
    if (!privateKeyJwk) throw new Error('No private key found in localStorage');

    const myPrivateKey = await crypto.subtle.importKey(
        'jwk',
        privateKeyJwk,
        { name: 'ECDH', namedCurve: 'P-256' },
        false,
        ['deriveKey']
    );

    const res = await secureFetch(`/api/user/${otherUserId}/public-key`);
    const otherPublicKeyBase64 = res.public_key;

    const otherPublicKey = await crypto.subtle.importKey(
        'raw',
        Uint8Array.from(atob(otherPublicKeyBase64), c => c.charCodeAt(0)),
        { name: 'ECDH', namedCurve: 'P-256' },
        false,
        []
    );

    const sharedKey = await crypto.subtle.deriveKey(
        { name: 'ECDH', public: otherPublicKey },
        myPrivateKey,
        { name: 'AES-GCM', length: 256 },
        false,
        ['encrypt', 'decrypt']
    );

    if (!window._sharedKeyCache) window._sharedKeyCache = {};
    window._sharedKeyCache[otherUserId] = sharedKey;

    return sharedKey;
}

async function encryptMessage(message, sharedKey) {
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const encoded = new TextEncoder().encode(message);

    const encrypted = await crypto.subtle.encrypt(
        { name: 'AES-GCM', iv },
        sharedKey,
        encoded
    );

    return {
        data: btoa(String.fromCharCode(...new Uint8Array(encrypted))),
        iv: btoa(String.fromCharCode(...iv))
    };
}

async function decryptMessage(encryptedBase64, ivBase64, sharedKey) {
    const encrypted = Uint8Array.from(atob(encryptedBase64), c => c.charCodeAt(0));
    const iv = Uint8Array.from(atob(ivBase64), c => c.charCodeAt(0));

    const decrypted = await crypto.subtle.decrypt(
        { name: 'AES-GCM', iv },
        sharedKey,
        encrypted
    );

    return new TextDecoder().decode(decrypted);
}



async function sendEncryptedKeyToServer(receiverId, rawKey) {

    const res = await secureFetch(`/api/user/${receiverId}/public-key`);
    const { public_key } = await res.json();

    const binaryKey = Uint8Array.from(atob(public_key), c => c.charCodeAt(0));

    const receiverPublicKey = await crypto.subtle.importKey(
        "spki",
        binaryKey,
        { name: "RSA-OAEP", hash: "SHA-256" },
        false,
        ["encrypt"]
    );

    const encryptedKey = await crypto.subtle.encrypt(
        { name: "RSA-OAEP" },
        receiverPublicKey,
        rawKey
    );

    const encryptedKeyBase64 = btoa(
        String.fromCharCode(...new Uint8Array(encryptedKey))
    );

    await secureFetch(`/api/conversation/share-key`, {
        method: "POST",
        body: {
            receiver_id: receiverId,
            encrypted_key: encryptedKeyBase64
        }
    });
}
window.sendEncryptedKeyToServer = sendEncryptedKeyToServer;
window.getSharedKey = getSharedKey;
window.encryptMessage = encryptMessage;
window.decryptMessage = decryptMessage;
