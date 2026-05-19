@extends('Layouts.layout')

@section('title', 'Create Group Chat')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card shadow-sm p-4 group-card" style="max-width: 600px; width: 100%; border-radius: 15px; border-top: 4px solid #198754;">
            <h3 class="text-center mb-4 text-success">Create Group Chat</h3>

            <form id="groupChatForm">

                <div class="mb-3">
                    <label class="form-label">Group Name</label>
                    <input type="text" id="groupName" class="form-control" placeholder="Enter group name" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Add Members</label>
                    <input type="text" id="userSearch" class="form-control" placeholder="Search users...">

                    <div id="searchResults" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display:none;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Selected Members</label>
                    <div id="selectedUsers" class="d-flex flex-wrap gap-2"></div>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    Create Group
                </button>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .group-card {
            background: #f8fff9;
            transition: 0.3s ease;
        }
        .group-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(25,135,84,0.15);
        }

        .selected-chip {
            background: #d1e7dd;
            color: #0f5132;
            padding: 5px 10px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .selected-chip button {
            border: none;
            background: transparent;
            color: #0f5132;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
@endsection

@section('scripts')
    <script>
        let selectedUsers = [];

        function renderSelectedUsers() {
            const container = document.getElementById('selectedUsers');
            container.innerHTML = '';

            selectedUsers.forEach(user => {
                const div = document.createElement('div');
                div.className = 'selected-chip';
                div.innerHTML = `
            ${user.name}
            <button onclick="removeUser(${user.id})">×</button>
        `;
                container.appendChild(div);
            });
        }

        function removeUser(id) {
            selectedUsers = selectedUsers.filter(u => u.id !== id);
            renderSelectedUsers();
        }

        function addUser(user) {
            if (!selectedUsers.find(u => u.id === user.id)) {
                selectedUsers.push(user);
                renderSelectedUsers();
            }
        }

        const resultsContainer = document.getElementById('searchResults');
        const searchInput = document.getElementById('userSearch');

        function debounce(fn, delay) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        async function searchUsers(query) {
            if (!query) {
                resultsContainer.style.display = 'none';
                return;
            }

            const users = await secureFetch(`/search/${encodeURIComponent(query)}`);

            resultsContainer.innerHTML = '';

            users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'list-group-item list-group-item-action';
                div.textContent = user.name;

                div.onclick = () => {
                    addUser(user);
                    resultsContainer.style.display = 'none';
                    searchInput.value = '';
                };

                resultsContainer.appendChild(div);
            });

            resultsContainer.style.display = 'block';
        }

        searchInput.addEventListener('input', debounce(e => {
            searchUsers(e.target.value);
        }, 400));

        searchInput.addEventListener('blur', () => {
            setTimeout(() => resultsContainer.style.display = 'none', 150);
        });

        async function generateKeysForGroups() {
            let myKey = await getMyPublicKey();
            const roomKey = crypto.getRandomValues(new Uint8Array(16));

            let userList = {};

            userList[localStorage.getItem('user_id')] =
                await encryptWithPublicKey(roomKey, myKey.public_key);

            await Promise.all(
                selectedUsers.map(async (user) => {
                    let userKey = await window.getPublicKey(user.id);
                    userList[user.id] =
                        await encryptWithPublicKey(roomKey, userKey.public_key);
                })
            );

            return userList;
        }

        $('#groupChatForm').submit(async function(e) {
            e.preventDefault();

            const name = $('#groupName').val().trim();

            if (!name || selectedUsers.length < 1) {
                alert('Group name and at least one member required');
                return;
            }
            const keyData = await generateKeysForGroups();

            const response = await secureFetch('/api/group-chat/create', {
                method: 'POST',
                body: {
                    name,
                    userData: keyData,
                }
            });
            const keyName = localStorage.getItem('user_id')+'-'+response.conversationId+'-'+response.latestKeyVersion;

            localStorage.setItem(keyName, keyData[localStorage.getItem('user_id')]);

        });
    </script>
@endsection
