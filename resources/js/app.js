import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

console.log('✅ Echo connected!');

window.Echo.channel('Test-Channel')
    .listen(".test-event", (e) => {
        console.log('💬 Event received:',e.message);
    });
