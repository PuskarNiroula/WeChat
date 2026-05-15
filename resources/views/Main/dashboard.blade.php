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
        // Run on EACH tab — compare private key x with what server has for that user
        async function diagnoseKeyMismatch(myUserId) {
            // My private key in localStorage
            const jwk = JSON.parse(localStorage.getItem('private_key'));
            console.log('localStorage private key (x):', jwk.x);
            console.log('localStorage private key (y):', jwk.y);

            // My public key on server
            const res = await secureFetch(`/api/user/${myUserId}/public-key`);
            console.log('Server public key:', res.public_key);

            // Decode server public key to x,y to compare
            const raw = Uint8Array.from(atob(res.public_key), c => c.charCodeAt(0));
            const serverPub = await crypto.subtle.importKey(
                'raw', raw,
                { name: 'ECDH', namedCurve: 'P-256' },
                true, []
            );
            const serverJwk = await crypto.subtle.exportKey('jwk', serverPub);
            console.log('Server public key (x):', serverJwk.x);
            console.log('Server public key (y):', serverJwk.y);

            // ✅ These x values must match — if not, server has wrong public key
            console.log('Keys match:', jwk.x === serverJwk.x ? '✅ YES' : '❌ NO — server has stale key!');
        }

        // Run with YOUR OWN user ID on each tab
        // SENDER tab: debugSharedKey(receiverId)
        // RECEIVER tab: debugSharedKey(senderId)
        async function debugSharedKey(otherUserId) {
            const jwk = JSON.parse(localStorage.getItem('private_key'));
            console.log('My private key (x):', jwk.x); // compare these

            const res = await secureFetch(`/api/user/${otherUserId}/public-key`);
            console.log('Other public key:', res.public_key);

            const privKey = await crypto.subtle.importKey(
                'jwk', jwk,
                { name: 'ECDH', namedCurve: 'P-256' },
                false, ['deriveKey']
            );

            const pubKey = await crypto.subtle.importKey(
                'raw',
                Uint8Array.from(atob(res.public_key), c => c.charCodeAt(0)),
                { name: 'ECDH', namedCurve: 'P-256' },
                false, []
            );

            const sharedKey = await crypto.subtle.deriveKey(
                { name: 'ECDH', public: pubKey },
                privKey,
                { name: 'AES-GCM', length: 256 },
                true, ['encrypt', 'decrypt']
            );

            const raw = await crypto.subtle.exportKey('raw', sharedKey);
            const hex = [...new Uint8Array(raw)]
                .map(b => b.toString(16).padStart(2, '0')).join('');

            console.log('🔑 Shared key hex:', hex);
            // This MUST be identical on both tabs
        }


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
                    let sharedKey = await getSharedKey(selectedUserId);


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
                                console.log("Decrypt failed for message:", msg, e);

                                return {
                                    ...msg,
                                    decrypted: "[unable to decrypt]"
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

                    const sharedKey = await getSharedKey(user.chat_member_id);

                    console.log("shared key: "+sharedKey);

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
                const sharedKey = await getSharedKey(selectedUserId);

                const encrypted = await encryptMessage(message, sharedKey);
                console.log(encrypted);
                console.log(
                    "IV bytes:",
                    Uint8Array.from(atob(encrypted.iv), c => c.charCodeAt(0)).length
                );

                await secureFetch(`/sendMessage`, {
                    method: 'POST',
                    body: {
                        conversation_id: conId,
                        encrypted_message: encrypted.data,
                        iv: encrypted.iv
                    }
                });

                // show instantly
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
                    const res = await secureFetch(`/openChat/${user_id}`, {method: "GET"});
                    ChatUser.innerHTML = res.name;
                    if (res.avatar != null) {
                        $('#avatar-pic').attr('src', '/images/avatars/' + res.avatar);
                    }
                    loadMessages(res.conversation_id);
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
