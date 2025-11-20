@extends('Layouts.layout')

@section('title', 'We Chat')
@section("styles")
    <link rel="stylesheet" href="{{asset('/css/custom.css')}}">
@endsection

@section('content')
    <div class="d-flex w-100 h-100">

        {{-- Sidebar inside content (chat list + search) --}}
        <div class="chat-sidebar bg-white border-end d-flex flex-column" style="width: 350px; overflow-y: auto;">
            <div class="sidebar-header d-flex align-items-center justify-content-between p-3 border-bottom">
                <h5 class="mb-0 text-success fw-bold">We Chat</h5>
                <i class="bi bi-three-dots-vertical fs-4 text-secondary"></i>
            </div>

            <div class="p-3 position-relative">
                <input type="text" id="searchInput" class="form-control ps-5 pe-3 py-2 rounded-pill shadow-sm" placeholder="Search users..." autocomplete="off">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-4 text-muted"></i>
                <div id="searchResults" class="position-absolute w-100 bg-white border rounded shadow-sm" style="top: 100%; left: 0; max-height: 300px; overflow-y: auto; display: none;"></div>
            </div>

            <div class="chat-list flex-grow-1">
                {{-- List of chats --}}
            </div>
        </div>

        {{-- Chat area --}}
        <div id="start-chatting" class="chat-area flex-grow-1 d-flex flex-column" style="display: none ! important;">
            <div class="chat-header d-flex align-items-center p-3 border-bottom bg-light">
                <img id="avatar-pic" src="{{asset('/images/avatars/avatar.jpg')}}" alt="Avatar" class="rounded-circle me-2">
                <div>
                    <h6 class="m-0" id="chat_user"></h6>
                    <small></small>
                </div>
            </div>

            <div class="chat-messages flex-grow-1 d-flex flex-column p-3 overflow-auto">
                {{-- Messages will go here --}}
            </div>

            <div class="chat-input p-3 d-flex border-top bg-light">
                <input type="text" placeholder="Type a message" id="message_to_be_sent" class="form-control me-2">
                <button class="btn btn-primary" onclick="sendMessage()"><i class="bi bi-send"></i></button>
            </div>
        </div>
        <div id="logo-image-div" class="chat-logo">
            <img id="logo-image" src="{{asset('/images/logo.png')}}" alt="">
        </div>
    </div>
    @vite('resources/js/app.js')

@endsection


@section('scripts')


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let conId=null;
        const myId=`{{Auth::id()}}`;
        let ChatUser=document.getElementById('chat_user');

        function loadDashboard(){
            if(conId!=null){
                loadMessages(conId);
                loadSidebar();
            }
        }
        loadDashboard();
        async function loadMessages(conversationId) {
            try {
                $('#start-chatting').show();
               $("#logo-image-div").hide();

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

                    // Create main message container
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message-bubble', messageClass);

                    // Create message text node
                    const messageText = document.createElement('p');
                    messageText.classList.add('mb-0');
                    messageText.textContent = msg.message; // Safe (no HTML injection)

                    // Create time element
                    const timeEl = document.createElement('small');
                    timeEl.classList.add('text-muted');
                    timeEl.textContent = new Date(msg.time).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Append safely
                    messageDiv.appendChild(messageText);
                    messageDiv.appendChild(timeEl);
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
                    const username = user.chat_member || 'Unknown User';
                    const lastMessage = user.last_message || 'No message yet';
                    const avatarPath = "/images/avatars/" + user.avatar;

                    const isUnread = user.last_message_sender !== 'Myself' && user.is_read === 0;
                    const messageColor = isUnread ? 'red' : 'black';
                    const messageFont = isUnread ? 'bold' : 'normal';

                    // Create main chat item
                    const chatItem = document.createElement('div');
                    chatItem.classList.add('chat-item');

                    // Create and set avatar safely
                    const avatarImg = document.createElement('img');
                    avatarImg.src = avatarPath;
                    avatarImg.alt = 'Avatar';

                    // Chat info container
                    const chatInfo = document.createElement('div');
                    chatInfo.classList.add('chat-info');

                    // Username
                    const userNameEl = document.createElement('h6');
                    userNameEl.textContent = username;

                    // Last message
                    const lastMsgEl = document.createElement('small');
                    lastMsgEl.style.color = messageColor;
                    lastMsgEl.style.fontWeight = messageFont;
                    lastMsgEl.textContent = lastMessage;

                    // Append everything safely
                    chatInfo.appendChild(userNameEl);
                    chatInfo.appendChild(lastMsgEl);
                    chatItem.appendChild(avatarImg);
                    chatItem.appendChild(chatInfo);

                    // Add click event
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
            }catch (err){
                console.log(err);
            }
        }

        async function createOrOpenChat(user_id) {
            try {
                const res = await secureFetch(`/openChat/${user_id}`, { method: "GET" });
                ChatUser.innerHTML = res.name;
                if(res.avatar != null){
                    $('#avatar-pic').attr('src', '/images/avatars/' + res.avatar);
                }
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
                results.forEach(({ id, name }) => {
                    const div = document.createElement('div');
                    div.textContent = name;
                    div.className = 'px-3 py-2 search-item';
                    div.style.cursor = 'pointer';

                    div.addEventListener('click', () => createOrOpenChat(id));

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

@endsection
