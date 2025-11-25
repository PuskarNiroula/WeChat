import sodium from 'libsodium-wrappers';

export async function initCrypto() {
    await sodium.ready;
    return sodium;
}

export function generateIdentityKey() {
    const keyPair = sodium.crypto_sign_keypair(); // ✅ signing keys
    return {
        publicKey: sodium.to_base64(keyPair.publicKey),
        privateKey: sodium.to_base64(keyPair.privateKey)
    };
}


export function generateSignedPreKey(identityPrivateKeyBase64) {
    const signedKey = sodium.crypto_sign_keypair(); // signing key
    const signature = sodium.crypto_sign_detached(
        signedKey.publicKey,
        sodium.from_base64(identityPrivateKeyBase64)
    );

    return {
        publicKey: sodium.to_base64(signedKey.publicKey),
        privateKey: sodium.to_base64(signedKey.privateKey),
        signature: sodium.to_base64(signature)
    };
}


// One-time pre-keys
export function generateOneTimePreKeys(numKeys = 50) {
    const keys = [];
    for (let i = 0; i < numKeys; i++) {
        const key = sodium.crypto_box_keypair();
        keys.push({
            publicKey: sodium.to_base64(key.publicKey),
            privateKey: sodium.to_base64(key.privateKey)
        });
    }
    return keys;
}

