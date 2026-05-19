
async function generateKeysForGroups(users) {
    let myKey = await getMyPublicKey();
    const roomKey = crypto.getRandomValues(new Uint8Array(16));

    let userList = {};

    userList[localStorage.getItem('user_id')] =
        await encryptWithPublicKey(roomKey, myKey.public_key);

    await Promise.all(
        users.map(async (user) => {
            let userKey = await window.getPublicKey(user.id);
            userList[user.id] =
                await encryptWithPublicKey(roomKey, userKey.public_key);
        })
    );

    return userList;
}
function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

window.generateKeysForGroups=generateKeysForGroups;
window.debounce=debounce;
