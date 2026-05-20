
async function getSharedKey(conversationId) {

const encryptedKey=await getEncryptedRoomKey(conversationId);

const userId=localStorage.getItem('user_id');


    const privateKey = await importPrivateKey(userId);

    const aesKeyBytes = await decryptRoomKey(encryptedKey, privateKey);

    return await crypto.subtle.importKey(
        "raw",
        aesKeyBytes,
        { name: "AES-GCM" },
        false,
        ["encrypt", "decrypt"]
    );
}

async function getSharedKeyByVersion(conversationId,keyVersion) {
    console.log("Key version",keyVersion);
    const encryptedKey= await getEncryptedRoomKeyByVersion(conversationId, keyVersion);
    console.log("Encrypted key",encryptedKey);
    const userId=localStorage.getItem('user_id');

    const privateKey = await importPrivateKey(userId);

    const aesKeyBytes = await decryptRoomKey(encryptedKey, privateKey);

    return await crypto.subtle.importKey(
        "raw",
        aesKeyBytes,
        { name: "AES-GCM" },
        false,
        ["encrypt", "decrypt"]
    );
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

async function decryptMessage(encryptedBase64,conversation_id, ivBase64,keyVersion) {
    const sharedKey = await getSharedKeyByVersion(conversation_id,keyVersion);
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
async function getMyPublicKey() {
    const userId=localStorage.getItem('user_id');
    return await secureFetch(`/api/user/${userId}/public-key`);
}
async function getPublicKey(receiverId) {
   return await secureFetch(`/api/user/${receiverId}/public-key`);
}

async function encryptWithPublicKey(roomKeyBytes, publicKeyBase64) {
    const binaryDer = Uint8Array.from(
        atob(publicKeyBase64),
        c => c.charCodeAt(0)
    );

    const publicKey = await crypto.subtle.importKey(
        "spki",
        binaryDer,
        {
            name: "RSA-OAEP",
            hash: "SHA-256"
        },
        false,
        ["encrypt"]
    );

    const encrypted = await crypto.subtle.encrypt(
        { name: "RSA-OAEP" },
        publicKey,
        roomKeyBytes
    );

    return arrayBufferToBase64(encrypted);
}

function arrayBufferToBase64(buffer) {
    let binary = '';
    const bytes = new Uint8Array(buffer);
    const chunkSize = 0x8000; // avoid stack overflow

    for (let i = 0; i < bytes.length; i += chunkSize) {
        binary += String.fromCharCode(...bytes.subarray(i, i + chunkSize));
    }

    return btoa(binary);
}

async function importPrivateKey(userId) {
    const jwk = JSON.parse(localStorage.getItem(`private_key_${userId}`));

    return await crypto.subtle.importKey(
        "jwk",
        jwk,
        {
            name: "RSA-OAEP",
            hash: "SHA-256"
        },
        false,
        ["decrypt"]
    );
}

async function decryptRoomKey(encryptedRoomKeyBase64,privateKey) {


    const encryptedBytes = Uint8Array.from(
        atob(encryptedRoomKeyBase64),
        c => c.charCodeAt(0)
    );

    const decrypted = await crypto.subtle.decrypt(
        { name: "RSA-OAEP" },
        privateKey,
        encryptedBytes
    );

    return new Uint8Array(decrypted);
}

async function getLatestKey(conversationId) {
    return await secureFetch(`/api/conversation/${conversationId}/latest-key`);
}
async function getEncryptedRoomKey(conversationId) {

    const userId = localStorage.getItem("user_id");

    const key_version = await getLatestKey(conversationId);


    const keyName = `${userId}-${conversationId}-${key_version}`;

    let encryptedKey = localStorage.getItem(keyName);
    console.log("Key version",keyName);
    console.log("Key version",encryptedKey);


    if (!encryptedKey) {
        console.log("Fetching room key from server");
       const response = await secureFetch(
            `/api/conversation/${conversationId}/key?version=${key_version}`
        );
       encryptedKey=response.room_key;
        console.log(response.room_key);


        if (encryptedKey) {
            localStorage.setItem(keyName, encryptedKey);
        } else {
            throw new Error("Room key not found on server");
        }
    }

    return encryptedKey;
}

async function getEncryptedRoomKeyByVersion(conversationId,keyVersion) {
    const userId = localStorage.getItem("user_id");
    const keyName = `${userId}-${conversationId}-${keyVersion}`;
    let encryptedKey = localStorage.getItem(keyName);

    if(encryptedKey){
        return encryptedKey;
    }

    const response= await secureFetch(`/api/conversation/${conversationId}/key?version=${keyVersion}`);
    localStorage.setItem(keyName,response.room_key);
    return response.room_key;

}

window.sendEncryptedKeyToServer = sendEncryptedKeyToServer;
window.getSharedKey = getSharedKey;
window.encryptMessage = encryptMessage;
window.decryptMessage = decryptMessage;
window.getMyPublicKey = getMyPublicKey;
window.getPublicKey = getPublicKey;
window.encryptWithPublicKey = encryptWithPublicKey;
window.decryptRoomKey = decryptRoomKey;
window.getLatestKey = getLatestKey;
window.getSharedKeyByVersion=getSharedKeyByVersion;

