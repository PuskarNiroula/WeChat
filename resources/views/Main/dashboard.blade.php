
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/custom.css')}}">
    <script src="{{asset('js/script.js')}}"></script>






    {{--    @vite('resources/js/app.js')--}}
</head>

<body>

<div class="chat-container">

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between p-3 border-bottom">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-2 text-success fw-bold w-50">We Chat</h5>
                <div class="position-relative w-100">
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control ps-5 pe-3 py-2 rounded-pill shadow-sm"
                        placeholder="Search users..."
                        autocomplete="off"
                    >
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                    <div id="searchResults" class="position-absolute w-100 bg-white border rounded shadow-sm" style="top: 100%; left: 0; z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                </div>
            </div>
            <i class="bi bi-three-dots-vertical fs-4 text-secondary"></i>
        </div>


        <div class="chat-list">

{{--            here i want the list of chats--}}
        </div>
    </div>

    {{-- Chat Area --}}
    <div class="chat-area">
        <div class="chat-header">
            <img src="https://i.pravatar.cc/50?img=1" alt="Avatar">
            <div>
                <h6 class="m-0" id="chat_user">Samana</h6>
                <small>Online</small>
            </div>
        </div>

        <div class="chat-messages d-flex flex-column">

        </div>

        <div class="chat-input">
            <input type="text" placeholder="Type a message" name="message" id="message_to_be_sent" >
            <button onclick="sendMessage()"><i class="bi bi-send"></i></button>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@vite('resources/js/app.js')


<script>


    let conId=null;
    const myId=`{{Auth::id()}}`;
    let ChatUser=document.getElementById('chat_user');

    async function loadMessages(conversationId) {
        try {
            conId = conversationId;
            const res = await secureFetch(`/getMessages/${conversationId}`, { method: "GET" });
            // Handle both array and object responses
            const messages = res.data || res.messages || res;

            const chatMessages = document.querySelector('.chat-messages');
            chatMessages.innerHTML = ''; // Clear previous messages

            if (!messages || messages.length === 0) {
                chatMessages.innerHTML = `<div class="text-muted text-center mt-4">No messages yet</div>`;
                return;
            }

            messages.slice().reverse().forEach(msg => {
                const isOwnMessage = msg.sender_id === parseInt(myId); // myId = logged-in user id
                const messageClass = isOwnMessage ? 'sent' : 'received';

                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message-bubble', messageClass);
                messageDiv.innerHTML = `
        <p class="mb-0">${msg.message}</p>
        <small class="text-muted">
            ${new Date(msg.time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
        </small>
    `;
                chatMessages.appendChild(messageDiv);
            });


            chatMessages.scrollTop = chatMessages.scrollHeight;



        } catch (err) {
            console.error("Error fetching messages:", err.message);
        }
    }


    async function loadSidebar() {
        try {
            const users = await secureFetch('/getSidebarMembers'); // your protected API call
            const chatList = document.querySelector('.chat-list');
            chatList.innerHTML = '';

            users.forEach(user => {
                const username = user.chat_member  || 'Unknown User';
                const lastMessage = user.last_message || 'No message yet';
                const avatar = `https://i.pravatar.cc/50?u=${user.chat_member_id}`; // generates unique avatar per user

                const isUnread = user.last_message_sender !== 'Myself' && user.is_read===0;
                const messageColor = isUnread ? 'red' : 'black';
                const messageFont=isUnread ? 'bold' : 'normal';

                const chatItem = document.createElement('div');
                chatItem.classList.add('chat-item');
                chatItem.innerHTML = `
        <img src="${avatar}" alt="Avatar">
        <div class="chat-info">
            <h6>${username}</h6>
            <small style="color:${messageColor}; font-weight: ${messageFont}">${lastMessage}</small>
        </div>
    `;
                chatItem.addEventListener('click', () => {
                    createOrOpenChat(user.chat_member_id);
                });

                chatList.appendChild(chatItem);
            });

        } catch (error) {
            console.error('Error loading sidebar:', error);
        }
    }

    // Call the function when page loads
    document.addEventListener('DOMContentLoaded', loadSidebar);


    async function sendMessage() {
        const message = document.getElementById("message_to_be_sent").value.trim();
        if (!message) {
            return;
        }
        try {
            await secureFetch(`/sendMessage`, {
                method: 'POST',
                body: {
                    message:message,
                    conversation_id:conId,
                }
            });
            loadMessages(conId);
            loadSidebar();
            document.getElementById("message_to_be_sent").value = "";
    }catch (Exception){
        console.log(Exception);
        }
    }

    async function createOrOpenChat(user_id) {
        try {
            const res = await secureFetch(`/openChat/${user_id}`, { method: "GET" });
            ChatUser.innerHTML = res.name;
            loadMessages(res.conversation_id);
            loadSidebar();
        } catch (err) {
            console.error("Error fetching messages:", err.message);
        }
    }


    // Debounce function: delays execution until user stops typing
    function debounce(fn, delay) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    async function search(query) {
        if (!query) {
            resultsContainer.style.display = 'none';
            resultsContainer.innerHTML = '';
            return;
        }
        resultsContainer.innerHTML = '<div class="px-3 py-2 text-muted">Searching...</div>';
        resultsContainer.style.display = 'block';

        try {
            const results = await secureFetch(`/search/${encodeURIComponent(query)}`, {
                method: 'GET'
            });

            // Clear previous results
            resultsContainer.innerHTML = '';

            if (results.length === 0) {
                resultsContainer.style.display = 'none';
                return;
            }

            // Populate results
            Object.entries(results).forEach(([user_id, name]) => {
                const div = document.createElement('div');
                div.textContent = name; // show the user's name
                div.className = 'px-3 py-2 search-item';
                div.style.cursor = 'pointer';

                div.addEventListener('click', () => {
                    createOrOpenChat(user_id);
                });

                resultsContainer.appendChild(div);
            });



            resultsContainer.style.display = 'block';
        } catch (err) {
            console.error('Search error:', err.message);
        }
    }
    const resultsContainer = document.getElementById('searchResults');

    // Attach debounced input listener
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', debounce((e) => {
        search(e.target.value);
    }, 500));
    resultsContainer.addEventListener('mousedown', (e) => {
        e.preventDefault(); // prevent blur before click
    });

    searchInput.addEventListener('blur', () => {
        // Small timeout to allow click to register
        setTimeout(() => {
            resultsContainer.style.display = 'none';
        }, 100);
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim() !== '') {
            search(searchInput.value);
        }
    });
</script>
</body>

</html>

