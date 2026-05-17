@extends('Layouts.layout')

@section('title', 'We Chat')
@section("styles")
    <link rel="stylesheet" href="{{asset('/css/custom.css')}}">
@endsection

@section('content')
    <div class="d-flex w-100 h-100">
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
               <form id="sendMessageForm" style="width: 100%">
                   <input type="text" placeholder="Type a message" id="message_to_be_sent" style="width: 93%" class=" me-2">
                   <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
               </form>
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
        let selectedUserId=null;
        let conId=null;
        const myId=`{{Auth::id()}}`;

        let ChatUser=document.getElementById('chat_user');
        const chatMessages = document.querySelector('.chat-messages');


        $("#sendMessageForm").submit( function (e){
            e.preventDefault();
            sendMessage();
        });

        function addMessageToChatList(message,time,className='received'){

            let messageDiv = document.createElement('div');
            messageDiv.classList.add('message-bubble', className);

            let messageText = document.createElement('p');
            messageText.classList.add('mb-0');
            messageText.textContent = message;

            let timeEl = document.createElement('small');
            timeEl.classList.add('text-muted');
            timeEl.textContent = new Date(time).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            messageDiv.appendChild(messageText);
            messageDiv.appendChild(timeEl);
            chatMessages.appendChild(messageDiv);

            chatMessages.scrollTop=chatMessages.scrollHeight;
        }
            function loadDashboard() {
                if (conId != null && selectedUserId != null) {
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
                    const res = await secureFetch(`/getMessages/${conversationId}`, {method: "GET"});
                    const messages =  res.messages ;


                    chatMessages.innerHTML = '';

                    if (!messages || messages.length === 0) {
                        chatMessages.innerHTML = `<div class="text-muted text-center mt-4">No messages yet</div>`;
                        return;
                    }
                    let sharedKey = await getSharedKey(conId);


                    const decryptedMessages = await Promise.all(
                        messages.slice().reverse().map(async (msg) => {
                            try {
                                const decrypted = await decryptMessage(
                                    msg.message,
                                    msg.iv,
                                    sharedKey
                                );

                                return {
                                    ...msg,
                                    decrypted
                                };
                            } catch (e) {

                                return {
                                    ...msg,
                                    decrypted: null
                                };
                            }
                        })
                    );


                    for (const msg of decryptedMessages) {

                        const isOwnMessage = msg.sender_id === parseInt(myId);
                        const messageClass = isOwnMessage ? 'sent' : 'received';

                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message-bubble', messageClass);

                        const messageText = document.createElement('p');
                        messageText.classList.add('mb-0');
                        messageText.textContent = msg.decrypted;

                        const timeEl = document.createElement('small');
                        timeEl.classList.add('text-muted');
                        timeEl.textContent = new Date(msg.time).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        messageDiv.appendChild(messageText);
                        messageDiv.appendChild(timeEl);
                        chatMessages.appendChild(messageDiv);
                    }


                    chatMessages.scrollTop = chatMessages.scrollHeight;


                } catch (err) {
                    console.error("Error fetching messages:", err.message);
                }
            }


        async function loadSidebar() {
            try {
                const users = await secureFetch('/getSidebarMembers');
                const chatList = document.querySelector('.chat-list');
                chatList.innerHTML = '';

                for (const user of users) {

                    const username = user.chat_member || 'Unknown User';
                    const lastMessage = user.last_message || '';
                    const iv = user.iv;

                    const avatarPath = "/images/avatars/" + user.avatar;

                    const sharedKey = await getSharedKey(user.conversation_id);


                    let previewMessage = lastMessage;

                    try {
                        if (lastMessage && iv) {
                            previewMessage = await decryptMessage(
                                lastMessage,
                                iv,
                                sharedKey
                            );
                        }
                    } catch (e) {
                        console.warn("Sidebar decrypt failed:", e);
                    }

                    const chatItem = document.createElement('div');
                    chatItem.classList.add('chat-item');

                    const avatarImg = document.createElement('img');
                    avatarImg.src = avatarPath;

                    const chatInfo = document.createElement('div');
                    chatInfo.classList.add('chat-info');

                    const userNameEl = document.createElement('h6');
                    userNameEl.textContent = username;

                    const lastMsgEl = document.createElement('small');
                    lastMsgEl.textContent = previewMessage;

                    chatInfo.appendChild(userNameEl);
                    chatInfo.appendChild(lastMsgEl);

                    chatItem.appendChild(avatarImg);
                    chatItem.appendChild(chatInfo);

                    chatItem.addEventListener('click', () => {
                        selectedUserId = user.chat_member_id;
                        createOrOpenChat(user.chat_member_id);
                    });

                    chatList.appendChild(chatItem);
                }

            } catch (error) {
                console.error('Error loading sidebar:', error);
            }
        }
            document.addEventListener('DOMContentLoaded', loadSidebar);


        async function sendMessage() {
            const input = document.getElementById("message_to_be_sent");
            const message = input.value.trim();

            if (!message) return;

            try {
                const sharedKey = await getSharedKey(conId);

                const encrypted = await encryptMessage(message, sharedKey);

                await secureFetch(`/sendMessage`, {
                    method: 'POST',
                    body: {
                        conversation_id: conId,
                        encrypted_message: encrypted.data,
                        iv: encrypted.iv
                    }
                });

                addMessageToChatList(message, `{{now()}}`, "sent");

                input.value = "";
                loadSidebar();

            } catch (err) {
                console.error("Send message error:", err);
            }
        }

        async function createOrOpenChat(user_id) {
            try {
                selectedUserId = user_id;

                let response = await secureFetch(`/api/conversation/${user_id}/check`, {
                    method: "GET"
                });

                let data = await response;

                if (!data || !data.conversationId) {

                    response = await createConversation(user_id);
                    data = await response;
                }
                conId=data.conversationId;

                ChatUser.innerHTML = data.name;

                if (data.avatar != null) {
                    $('#avatar-pic').attr('src', '/images/avatars/' + data.avatar);
                }

                loadMessages(conId);
            } catch (err) {
                console.error("Error fetching messages:", err.message);
            }
        }


            function debounce(fn, delay) {
                let timer;
                return function (...args) {
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
                    results.forEach(({id, name}) => {
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

            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', debounce((e) => {
                search(e.target.value);
            }, 500));
            resultsContainer.addEventListener('mousedown', (e) => {
                e.preventDefault();
            });

            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    resultsContainer.style.display = 'none';
                }, 100);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim() !== '') {
                    search(searchInput.value);
                }
            });
            async function createConversation(){
                const roomKey = crypto.getRandomValues(new Uint8Array(16));

                const senderRes = await getMyPublicKey();
                const receiverRes = await getPublicKey(selectedUserId);

                const senderPublicKey = senderRes.public_key;
                const receiverPublicKey = receiverRes.public_key;

                const encryptedRoomKeyForSender = await encryptWithPublicKey(roomKey, senderPublicKey);

                const encryptedRoomKeyForReceiver = await encryptWithPublicKey(roomKey, receiverPublicKey);

                const response= await secureFetch(`/api/conversation/create-private-conversation`, {
                    method: 'POST',
                    body: {
                        sender_id: myId,
                        receiver_id: selectedUserId,
                        encrypted_room_key_for_sender: encryptedRoomKeyForSender,
                        encrypted_room_key_for_receiver: encryptedRoomKeyForReceiver,
                    },
                });
                const conversation =await response;
                const roomKeys= new Map();
                roomKeys.set(conversation.id,encryptedRoomKeyForSender);
                return conversation;
            }


    </script>

@endsection
