@extends('Layouts.layout')

@section('title', 'Add Members')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card shadow-sm p-4 group-card"
             style="max-width: 600px; width: 100%; border-radius: 15px; border-top: 4px solid #198754;">

            <h3 class="text-center mb-4 text-success">Add Members</h3>

            <form id="addMemberForm">

                <div class="mb-3 position-relative">
                    <label class="form-label">Search Users</label>
                    <input type="text" id="userSearch" class="form-control" placeholder="Search users...">

                    <div id="searchResults"
                         class="list-group position-absolute w-100 shadow-sm"
                         style="z-index: 1000; display:none; max-height: 200px; overflow-y:auto;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Selected Users</label>
                    <div id="selectedUsers" class="d-flex flex-wrap gap-2"></div>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    Add Members
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

        const searchInput = document.getElementById('userSearch');
        const resultsContainer = document.getElementById('searchResults');
        const groupId = "{{$groupChatId}}";

        function renderSelectedUsers() {
            const container = document.getElementById('selectedUsers');
            container.innerHTML = '';

            selectedUsers.forEach(user => {
                const div = document.createElement('div');
                div.className = 'selected-chip';

                div.innerHTML = `
                ${user.name}
                <button onclick="removeUser(${user.userId})">×</button>
            `;

                container.appendChild(div);
            });
        }

        function addUser(user) {
            if (!selectedUsers.find(u => u.userId === user.userId)) {
                selectedUsers.push(user);
                renderSelectedUsers();
            }
        }

        function removeUser(id) {
            selectedUsers = selectedUsers.filter(u => u.userId !== id);
            renderSelectedUsers();
        }

        async function searchUsers(query) {
            if (!query) {
                resultsContainer.style.display = 'none';
                return;
            }

            try {
                const users = await secureFetch(
                    `/api/group-chat/new-member/${groupId}/search?user=${encodeURIComponent(query)}`
                );

                resultsContainer.innerHTML = '';

                if (!users || users.length === 0) {
                    resultsContainer.innerHTML = `
                    <div class="list-group-item text-muted">
                        No users found
                    </div>`;
                } else {
                    users.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';

                        div.innerHTML = `
                        <img src="${user.avatar}" width="30" height="30" style="border-radius:50%" alt="user image">
                        <span>${user.name}</span>
                    `;

                        div.onclick = () => {
                            addUser(user);
                            resultsContainer.style.display = 'none';
                            searchInput.value = '';
                        };

                        resultsContainer.appendChild(div);
                    });
                }

                resultsContainer.style.display = 'block';

            } catch (err) {
                console.error(err);
            }
        }

        searchInput.addEventListener('input', debounce((e) => searchUsers(e.target.value), 500));


        searchInput.addEventListener('blur', () => {
            setTimeout(() => resultsContainer.style.display = 'none', 150);
        });

        function debounce(fn, delay) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        $('#addMemberForm').submit(async function (e) {
            e.preventDefault();

            if (selectedUsers.length < 1) {
                alert('Please select at least one user');
                return;
            }

            const groupId = "{{$groupChatId}}";

            try {

                const oldMembers = await getOldMemberIds(groupId);

                const allUsersMap = new Map();

                selectedUsers.forEach(u => allUsersMap.set(String(u.userId), { id: u.userId }));
                oldMembers.forEach(id => allUsersMap.set(String(id), { id }));

                const allUsers = Array.from(allUsersMap.values());

                const keyData = await generateKeysForGroups(allUsers);

              const response=  await secureFetch('/api/group-chat/add-members', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversationId: groupId,
                        userData: keyData,
                    })
                });

                if(response.status==="success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Members added successfully',
                    }).then(() => {
                        window.location.href = `/group-chat/${groupId}/details`;
                    });
                }

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'An error occurred',
                });
            }
        });

        async function getOldMemberIds(conversationId) {
            const response = await secureFetch(`/api/group-chat/${conversationId}/get-old-members`);
            return response.map(member => member.userId);
        }
    </script>
@endsection
