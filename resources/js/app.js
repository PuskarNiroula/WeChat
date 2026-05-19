import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
import './encryption.js';
import './helper.js';
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
    .listen(".test-event", () => {
    });

if (typeof myId !== "undefined") {
    window.Echo.private(`Message-Channel.${myId}`)
        .listen(".message-sent", (e) => {

            if (String(e.receiver_id) === String(myId)) {
                loadSidebar();
                if (e.conversation_id === conId) {
                    loadMessages(conId);
                }
            }

        });
}
