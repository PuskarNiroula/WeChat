@extends('Layouts.layout')

@section('title', 'We Chat')

@section('styles')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body, html {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: #efeae2;
        }

        .chat-sidebar {
            width: 350px;
            min-width: 350px;
            background: #fff;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            background: #075e54;
            height: 62px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-header h5 {
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            margin: 0;
            letter-spacing: .3px;
        }

        .sidebar-header .bi-three-dots-vertical {
            color: #d0e8e5;
            font-size: 20px;
            cursor: pointer;
        }

        /* Search */
        .search-wrap {
            padding: 8px 12px;
            background: #f0f2f5;
        }

        .search-wrap input {
            width: 100%;
            background: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 16px 8px 38px;
            font-size: 14px;
            color: #333;
            outline: none;
        }

        .search-wrap .search-icon {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: #8696a0;
            font-size: 14px;
        }

        #searchResults {
            position: absolute;
            top: calc(100% + 4px);
            left: 0; right: 0;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,.15);
            max-height: 280px;
            overflow-y: auto;
            z-index: 100;
            display: none;
        }

        .search-item {
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background .15s;
        }

        .search-item:hover { background: #f5f5f5; }

        .chat-list { overflow-y: auto; flex: 1; }

        .chat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background .15s;
        }

        .chat-item:hover, .chat-item.active { background: #f0f2f5; }

        .chat-item img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .chat-info { flex: 1; min-width: 0; }

        .chat-info h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-info small {
            font-size: 13px;
            color: #667781;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .chat-item-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
            flex-shrink: 0;
        }

        .chat-item-time {
            font-size: 11px;
            color: #667781;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: #075e54;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            cursor: pointer;
        }

        .chat-header h6 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
        }

        .chat-header small {
            font-size: 12px;
            color: #b2dfdb;
        }

        .chat-header .bi-three-dots-vertical {
            color: #d0e8e5;
            font-size: 20px;
            cursor: pointer;
        }


        .chat-messages {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 2px;
            background-color: #efeae2;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4c5a9' fill-opacity='0.3'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            max-width: 75%;
        }

        .msg-row.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .msg-row.received {
            align-self: flex-start;
        }

        .msg-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            margin-bottom: 2px;
        }

        .msg-avatar.hidden { visibility: hidden; }

        .msg-bubble {
            position: relative;
            padding: 6px 10px 18px 10px;
            border-radius: 8px;
            font-size: 14.2px;
            line-height: 1.45;
            word-break: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,.13);
        }

        .msg-bubble.sent {
            background: #dcf8c6;
            min-width: 75px;
            border-top-right-radius: 2px;
        }

        .msg-bubble.sent::after {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            width: 0; height: 0;
            border-left: 8px solid #dcf8c6;
            border-top: 8px solid transparent;
        }

        .msg-bubble.received {
            background: #ffffff;
            min-width: 75px;
            border-top-left-radius: 2px;
        }

        .msg-bubble.received::after {
            content: '';
            position: absolute;
            top: 0;
            left: -8px;
            width: 0; height: 0;
            border-right: 8px solid #ffffff;
            border-top: 8px solid transparent;
        }

        .msg-text {
            color: #111;
            padding-right: 4px;
        }

        .msg-meta {
            position: absolute;
            bottom: 4px;
            right: 8px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .msg-time {
            font-size: 10.5px;
            color: #667781;
            white-space: nowrap;
        }

        .msg-ticks {
            display: flex;
            align-items: center;
        }

        .tick-icon {
            font-size: 13px;
            color: #53bdeb;
        }

        .msg-sender-name {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 2px;
            color: #00a884;
        }

        .date-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0 8px;
        }

        .date-divider span {
            background: #d9fdd3;
            color: #54656f;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,.1);
        }

        .msg-decrypt-error {
            color: #c0392b;
            font-style: italic;
            font-size: 13px;
        }

        .chat-input-area {
            background: #f0f2f5;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .chat-input-area input {
            flex: 1;
            background: #fff;
            border: none;
            border-radius: 20px;
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
            color: #333;
        }

        .chat-input-area input::placeholder { color: #8696a0; }

        .btn-send {
            background: #075e54;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .2s;
        }

        .btn-send:hover { background: #128c7e; }

        .btn-send i { color: #fff; font-size: 18px; }

        .icon-btn {
            color: #8696a0;
            font-size: 22px;
            cursor: pointer;
            transition: color .2s;
            flex-shrink: 0;
        }

        .icon-btn:hover { color: #075e54; }

        .chat-logo {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            gap: 20px;
        }

        .chat-logo img {
            width: 220px;
            opacity: .5;
        }

        .chat-logo p {
            color: #667781;
            font-size: 14px;
            letter-spacing: .3px;
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

        .dropdown-menu { font-size: 14px; }

        @media (max-width: 640px) {
            .chat-sidebar { width: 100%; }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex w-100 h-100">

        <div class="chat-sidebar">

            <div class="sidebar-header">
                <h5>We Chat</h5>
                <div class="dropdown">
                    <i class="bi bi-three-dots-vertical"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"></i>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="/group-chat/create">
                                <i class="bi bi-people me-2"></i>Create Group Chat
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="search-wrap position-relative">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search or start new chat" autocomplete="off">
                <div id="searchResults"></div>
            </div>

            <div class="chat-list" id="chatList"></div>
        </div>

        <div id="start-chatting" class="chat-area" style="display:none;">

            <div class="chat-header">
                <div class="d-flex align-items-center flex-grow-1">
                    <img id="avatar-pic" src="{{ asset('/images/avatars/avatar.jpg') }}" alt="avatar">
                    <div>
                        <h6 id="chat_user">User</h6>
                        <small id="chat_status">online</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div id="group-options" class="dropdown d-none">
                        <i class="bi bi-three-dots-vertical icon-btn"
                           role="button"
                           data-bs-toggle="dropdown"></i>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" onclick="gotoGroupChatEditPage()">
                                    <i class="bi bi-pencil me-2"></i>Edit Group
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#">
                                    <i class="bi bi-box-arrow-right me-2"></i>Leave Group
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages"></div>

            <div class="chat-input-area">
                <i class="bi bi-paperclip icon-btn"></i>
                <input type="text" id="message_to_be_sent" placeholder="Type a message">
                <button class="btn-send" id="sendBtn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>

        <div id="logo-image-div" class="chat-logo">
            <img id="logo-image" src="{{ asset('/images/logo.png') }}" alt="We Chat">
            <p>Click on a conversation to start chatting</p>
        </div>

    </div>
@endsection

@section('scripts')
    <script>

        let selectedUserId = null;
        let conId          = null;
        const myId         = `{{ Auth::id() }}`;

        const chatMessages   = document.getElementById('chatMessages');
        const chatUserEl     = document.getElementById('chat_user');
        const sendBtn        = document.getElementById('sendBtn');
        const msgInput       = document.getElementById('message_to_be_sent');


        function formatTime(ts) {
            return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function formatDate(ts) {
            const d   = new Date(ts);
            const now = new Date();
            const yesterday = new Date(now);
            yesterday.setDate(now.getDate() - 1);

            if (d.toDateString() === now.toDateString())       return 'Today';
            if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
            return d.toLocaleDateString([], { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function isSameDay(a, b) {
            const da = new Date(a), db = new Date(b);
            return da.getFullYear() === db.getFullYear() &&
                da.getMonth()    === db.getMonth()    &&
                da.getDate()     === db.getDate();
        }

        function avatarUrl(avatar) {
            return avatar ? `/images/avatars/${avatar}` : `/images/avatars/avatar.jpg`;
        }

        function debounce(fn, delay) {
            let t;
            return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
        }


        function buildBubble({ text, time, isSent, avatar, senderName, showAvatar, decryptFailed, isGroup }) {
            const row = document.createElement('div');
            row.classList.add('msg-row', isSent ? 'sent' : 'received');

            if (!isSent && isGroup) {
                const img = document.createElement('img');
                img.src       = avatarUrl(avatar);
                img.className = 'msg-avatar' + (showAvatar ? '' : ' hidden');
                img.alt       = '';
                row.appendChild(img);
            }

            const bubble = document.createElement('div');
            bubble.classList.add('msg-bubble', isSent ? 'sent' : 'received');


                if(isGroup){
                    const nameEl       = document.createElement('div');
                    nameEl.className   = 'msg-sender-name';
                    nameEl.textContent = senderName;
                    bubble.appendChild(nameEl);
                }


            const textEl       = document.createElement('div');
            textEl.className   = 'msg-text' + (decryptFailed ? ' msg-decrypt-error' : '');
            textEl.textContent = decryptFailed ? '🔒 Encrypted message' : text;
            bubble.appendChild(textEl);

            const meta     = document.createElement('div');
            meta.className = 'msg-meta';

            const timeEl       = document.createElement('span');
            timeEl.className   = 'msg-time';
            timeEl.textContent = formatTime(time);
            meta.appendChild(timeEl);

            if (isSent) {
                const ticks = document.createElement('span');
                ticks.className  = 'msg-ticks';
                ticks.innerHTML  = '<i class="bi bi-check2-all tick-icon"></i>';
                meta.appendChild(ticks);
            }

            bubble.appendChild(meta);
            row.appendChild(bubble);
            return row;
        }

        async function loadMessages(conversationId) {
            try {
                document.getElementById('start-chatting').style.display = 'flex';
                document.getElementById('logo-image-div').style.display  = 'none';

                const meta = await secureFetch(`/api/conversation/${conversationId}/meta`);
                document.getElementById('avatar-pic').src = avatarUrl(meta.avatar);
                chatUserEl.textContent = meta.name;

                const groupOptions = document.getElementById('group-options');
                meta.is_group
                    ? groupOptions.classList.remove('d-none')
                    : groupOptions.classList.add('d-none');

                conId = conversationId;

                const res      = await secureFetch(`/getMessages/${conversationId}`, { method: 'GET' });
                const messages = res.messages || [];

                chatMessages.innerHTML = '';

                if (!messages.length) {
                    chatMessages.innerHTML = `
                    <div class="date-divider">
                        <span>No messages yet</span>
                    </div>`;
                    return;
                }

                const sharedKey = await getSharedKey(conId);
                const decrypted = await Promise.all(
                    messages.slice().reverse().map(async (msg) => {
                        try {
                            const text = await decryptMessage(msg.message, msg.iv, sharedKey);
                            return { ...msg, text, failed: false };
                        } catch {
                            return { ...msg, text: null, failed: true };
                        }
                    })
                );

                let lastDate   = null;
                let lastSender = null;

                for (let i = 0; i < decrypted.length; i++) {
                    const msg    = decrypted[i];
                    const isSent = msg.sender_id === parseInt(myId);

                    if (!lastDate || !isSameDay(lastDate, msg.time)) {
                        const div = document.createElement('div');
                        div.className   = 'date-divider';
                        div.innerHTML   = `<span>${formatDate(msg.time)}</span>`;
                        chatMessages.appendChild(div);
                        lastDate   = msg.time;
                        lastSender = null;
                    }

                    const showAvatar = msg.sender_id !== lastSender;
                    lastSender       = msg.sender_id;

                    const bubble = buildBubble({
                        text:         msg.text,
                        time:         msg.time,
                        isSent,
                        avatar:       msg.avatar,
                        senderName:   null,
                        showAvatar,
                        decryptFailed: msg.failed,
                        isGroup:      meta.is_group,
                    });

                    chatMessages.appendChild(bubble);
                }

                chatMessages.scrollTop = chatMessages.scrollHeight;

            } catch (err) {
                console.error('loadMessages error:', err);
            }
        }

        async function sendMessage() {
            const message = msgInput.value.trim();
            if (!message || !conId) return;

            msgInput.value = '';

            const tempBubble = buildBubble({
                text:    message,
                time:    new Date().toISOString(),
                isSent:  true,
                showAvatar: false,
                decryptFailed: false,
                isGroup: false,
            });
            chatMessages.appendChild(tempBubble);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                const sharedKey = await getSharedKey(conId);
                const encrypted = await encryptMessage(message, sharedKey);

                await secureFetch('/sendMessage', {
                    method: 'POST',
                    body: {
                        conversation_id:   conId,
                        encrypted_message: encrypted.data,
                        iv:                encrypted.iv,
                    }
                });

                loadSidebar();
            } catch (err) {
                console.error('sendMessage error:', err);
                tempBubble.querySelector('.msg-text').classList.add('msg-decrypt-error');
                tempBubble.querySelector('.msg-text').textContent = '⚠ Failed to send';
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        msgInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) sendMessage(); });

        async function loadSidebar() {
            try {
                const users    = await secureFetch('/getSidebarMembers');
                const chatList = document.getElementById('chatList');
                chatList.innerHTML = '';

                for (const user of users) {
                    const sharedKey = await getSharedKey(user.conversation_id);
                    let preview     = '';

                    try {
                        if (user.last_message && user.iv) {
                            preview = await decryptMessage(user.last_message, user.iv, sharedKey);
                        }
                    } catch { preview = '🔒 Encrypted'; }

                    const item = document.createElement('div');
                    item.className = 'chat-item' + (conId === user.conversation_id ? ' active' : '');

                    item.innerHTML = `
                    <img src="${avatarUrl(user.avatar)}" alt="">
                    <div class="chat-info">
                        <h6>${user.chat_name || 'Unknown'}</h6>
                        <small>${preview}</small>
                    </div>
                    <div class="chat-item-meta">
                        <span class="chat-item-time">${user.last_time ? formatTime(user.last_time) : ''}</span>
                    </div>`;

                    item.addEventListener('click', () => {
                        document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active'));
                        item.classList.add('active');

                        conId          = user.conversation_id;
                        selectedUserId = user.chat_member_id;
                        chatUserEl.textContent = user.chat_name;

                        loadMessages(conId);
                    });

                    chatList.appendChild(item);
                }
            } catch (err) {
                console.error('loadSidebar error:', err);
            }
        }


        const resultsContainer = document.getElementById('searchResults');
        const searchInput      = document.getElementById('searchInput');

        async function search(query) {
            if (!query) { resultsContainer.style.display = 'none'; return; }
            resultsContainer.innerHTML = '<div class="search-item text-muted">Searching…</div>';
            resultsContainer.style.display = 'block';

            try {
                const results = await secureFetch(`/search/${encodeURIComponent(query)}`, { method: 'GET' });
                resultsContainer.innerHTML = '';

                if (!results.length) { resultsContainer.style.display = 'none'; return; }

                results.forEach(({ id, name, avatar }) => {
                    const div = document.createElement('div');
                    div.className = 'search-item';
                    div.innerHTML = `
                    <img src="${avatarUrl(avatar)}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <span>${name}</span>`;
                    div.addEventListener('click', () => {
                        resultsContainer.style.display = 'none';
                        searchInput.value = '';
                        createOrOpenChat(id);
                    });
                    resultsContainer.appendChild(div);
                });
            } catch (err) {
                console.error('search error:', err);
            }
        }

        searchInput.addEventListener('input', debounce((e) => search(e.target.value), 500));
        searchInput.addEventListener('blur', () => setTimeout(() => resultsContainer.style.display = 'none', 150));
        searchInput.addEventListener('focus', () => { if (searchInput.value.trim()) search(searchInput.value); });
        resultsContainer.addEventListener('mousedown', (e) => e.preventDefault());

        async function createOrOpenChat(user_id) {
            try {
                selectedUserId = user_id;
                let data = await secureFetch(`/api/conversation/${user_id}/check`, { method: 'GET' });

                if (!data || !data.conversationId) {
                    data = await createConversation(user_id);
                }

                conId = data.conversationId;
                chatUserEl.textContent = data.name;

                if (data.avatar) {
                    document.getElementById('avatar-pic').src = avatarUrl(data.avatar);
                }

                loadMessages(conId);
            } catch (err) {
                console.error('createOrOpenChat error:', err);
            }
        }

        async function createConversation() {
            const roomKey = crypto.getRandomValues(new Uint8Array(16));

            const senderRes   = await getMyPublicKey();
            const receiverRes = await getPublicKey(selectedUserId);

            const encryptedRoomKeyForSender   = await encryptWithPublicKey(roomKey, senderRes.public_key);
            const encryptedRoomKeyForReceiver = await encryptWithPublicKey(roomKey, receiverRes.public_key);

         return await secureFetch('/api/conversation/create-private-conversation', {
                method: 'POST',
                body: {
                    sender_id:                    myId,
                    receiver_id:                  selectedUserId,
                    encrypted_room_key_for_sender:   encryptedRoomKeyForSender,
                    encrypted_room_key_for_receiver: encryptedRoomKeyForReceiver,
                }
            });


        }

        function gotoGroupChatEditPage() {
            window.location.href = `/group-chat/${conId}/edit`;
        }


        document.addEventListener('DOMContentLoaded', () => {
            loadSidebar();
            if (conId && selectedUserId) loadMessages(conId);
        });
    </script>
@endsection
