import './echo.js';

window.Echo.channel('Test-Channel')
    .listen(".test-event", (e) => {
        console.log('💬 Event received:',e.message);
    });
