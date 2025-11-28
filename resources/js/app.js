import Echo from 'laravel-echo';
import {generateIdentityKey, generateOneTimePreKeys, generateSignedPreKey, initCrypto} from './crypto.js';

import Pusher from 'pusher-js';

async function sFetch(url, options = {}) {
    options = options || {}
    options.headers = { ...(options.headers || {}) }

    const token = localStorage.getItem('token');
    if (token) options.headers.Authorization = `Bearer ${token}`

    // Only stringify if it's a plain object, leave FormData as-is
    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        options.body = JSON.stringify(options.body)
        options.headers['Content-Type'] = 'application/json'
    }

    const response = await fetch(url, options)
    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || 'Request failed')
    }
    return response.json();
}

window.setupUserKeys = async function() {
    await initCrypto();

    const identity = generateIdentityKey();
    localStorage.setItem('privateIdentityKey', identity.privateKey);

    const signedKey = generateSignedPreKey(identity.privateKey);
    localStorage.setItem('signedPreKeyPrivate', signedKey.privateKey);

    const oneTimeKeys = generateOneTimePreKeys();
    localStorage.setItem('oneTimePreKeys', JSON.stringify(oneTimeKeys));

   return await sFetch('/api/save-public-keys', {
       method: 'POST',
       headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
       body: JSON.stringify({
           public_identity_key: identity.publicKey,
           signed_pre_key: signedKey.publicKey,
           signed_pre_key_signature: signedKey.signature,
           one_time_pre_keys: oneTimeKeys.map(k => k.publicKey)
       })
   });
};


window.Pusher = Pusher;

let token =localStorage.getItem('token');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    },
});


window.Echo.channel('Test-Channel')
    .listen(".test-event", (e) => {
        console.log('💬 Event received:',e.message);
    });
window.Echo.private(`Message-Channel.${myId}`)
    .listen(".message-sent", (e) => {
        console.log('received');

        if (String(e.receiver_id)===String(myId)){
            loadSidebar();
            if(e.conversation_id===conId){
                loadMessages(conId);
            }
        }

    });

function encryptMessage(signedPreKey,message){
    const sharedSecret = sodium.crypto_scalarmult(
        sodium.from_base64(localStorage.getItem("privateIdentityKey")),
        sodium.from_base64(signedPreKey)
    );

    // 2. Encrypt message
    const nonce = sodium.randombytes_buf(sodium.crypto_secretbox_NONCEBYTES);

    const cipherText = sodium.crypto_secretbox_easy(
        message,
        nonce,
        sharedSecret
    );

    const encrypted = {
        nonce: sodium.to_base64(nonce),
        ciphertext: sodium.to_base64(cipherText),
    };

}
