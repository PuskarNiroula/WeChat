import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
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

        if (toString(e.receiver_id)===toString(myId)){
            loadSidebar();
        }
        if(e.conversation_id===conId){
            loadMessages(conId);
        }
    });
