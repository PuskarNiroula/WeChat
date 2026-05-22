
async function generateKeysForGroups(users) {
    console.log(users);

    let myKey = await getMyPublicKey();
    const roomKey = crypto.getRandomValues(new Uint8Array(16));

    const myId = String(localStorage.getItem('user_id'));

    let userList = {};

    userList[myId] =
        await encryptWithPublicKey(roomKey, myKey.public_key);

    await Promise.all(
        users
            .filter(user => String(user.id) !== myId)
            .map(async (user) => {

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
