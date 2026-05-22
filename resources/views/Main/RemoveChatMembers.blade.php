@extends('Layouts.layout')

@section('title', 'Remove Members')

@section('content')



    <div class="gd-page">

        <div class="gd-hero">

            <a href="{{ url()->previous() }}" class="gd-back">
                <i class="bi bi-arrow-left"></i> Back
            </a>

            <div class="gd-avatar-ring">
                <div class="gd-avatar-inner">
                    <img id="groupPreview"
                         src="/images/avatars/default_group_image.png"
                         alt="Group avatar"
                         style="display:none;">

                    <span id="groupInitials"></span>
                </div>
            </div>

            <h1 class="gd-group-name">Remove Members</h1>

            <p class="gd-group-meta" id="group_meta">
                Loading...
            </p>

        </div>

        <div class="gd-section">

            <div class="gd-section-header">
                <span class="gd-section-title">Select Members To Remove</span>
            </div>

            <ul class="list-unstyled mb-0" id="memberList"></ul>

        </div>

        <div class="remove-btn-wrapper">

            <button class="remove-btn" id="removeMembersBtn">
                Remove Selected Members
            </button>

        </div>

    </div>

@endsection

@section('scripts')

    <script>

        const conId = {{ $groupChatId }};
        const currentUserId = localStorage.getItem('user_id');
        const groupId = {{$groupChatId}};

        let selectedUsers = [];
        let members = [];
        let leftUsers = [];

        const AVATAR_COLORS = [
            { bg: '#EEEDFE', color: '#534AB7' },
            { bg: '#E1F5EE', color: '#0F6E56' },
            { bg: '#FAECE7', color: '#993C1D' },
            { bg: '#E6F1FB', color: '#185FA5' },
            { bg: '#FBEAF0', color: '#993556' },
        ];

        function initials(name) {
            if (!name) return '?';
            return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        document.addEventListener('DOMContentLoaded', async () => {

            try {

                const meta = await secureFetch(`/api/conversation/${conId}/meta`);

                const groupInitials = document.getElementById('groupInitials');
                const groupPreview = document.getElementById('groupPreview');

                groupInitials.textContent = initials(meta.name);

                if (meta.avatar) {
                    groupPreview.src = '/images/avatars/' + meta.avatar;
                    groupPreview.style.display = 'block';
                    groupInitials.style.display = 'none';
                }

                // LOAD MEMBERS
                members = await secureFetch(`/api/group-chat/${conId}/get-old-members`);

                document.getElementById('group_meta').textContent =
                    `${members.length} members`;

                const list = document.getElementById('memberList');
                list.innerHTML = '';

                members.forEach((member, i) => {

                    const userId = String(member.userId);

                    if (userId === String(currentUserId)) return;

                    const c = AVATAR_COLORS[i % AVATAR_COLORS.length];
                    const name = member.name ?? 'Unknown';

                    list.insertAdjacentHTML('beforeend', `
                <li class="gd-member-item">

                    <input type="checkbox"
                           class="remove-checkbox"
                           value="${userId}">

                    <div class="gd-member-avatar"
                         style="background:${c.bg}; color:${c.color}">
                        ${initials(name)}
                    </div>

                    <div class="gd-member-info">
                        <p class="gd-member-name">${name}</p>
                    </div>

                </li>
            `);
                });

                document.querySelectorAll('.remove-checkbox')
                    .forEach(box => {

                        box.addEventListener('change', function () {

                            const id = String(this.value);

                            if (this.checked) {
                                if (!selectedUsers.includes(id)) {
                                    selectedUsers.push(id);
                                }
                            } else {
                                selectedUsers = selectedUsers.filter(u => u !== id);
                            }
                        });
                    });

                document.getElementById('removeMembersBtn')
                    .addEventListener('click', async () => {

                        if (selectedUsers.length < 1) {

                            Swal.fire({
                                icon: 'warning',
                                title: 'No Members Selected',
                                text: 'Please select at least one member.'
                            });

                            return;
                        }

                        leftUsers = members
                            .filter(m =>
                                !selectedUsers.includes(String(m.userId)) &&
                                String(m.userId) !== String(currentUserId)
                            )
                            .map(m => ({
                                id: m.userId,
                                name: m.name
                            }));

                        if (leftUsers.length < 2) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Not Allowed',
                                text: 'Group must have at least 2 members.'
                            });

                            return;
                        }

                        const confirm = await Swal.fire({
                            title: 'Are you sure?',
                            text: 'Selected members will be removed from the group.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, remove them!'
                        });

                        if (!confirm.isConfirmed) return;

                        try {
                            const keyData = await generateKeysForGroups(leftUsers);


                            await secureFetch('/api/group-chat/remove-members', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    conversationId: groupId,
                                    userData: keyData,
                                    removedUserIds: selectedUsers
                                })
                            });

                            await Swal.fire({
                                icon: 'success',
                                title: 'Removed',
                                text: 'Members removed successfully.'
                            });

                            location.reload();

                        } catch (err) {

                            console.error(err);

                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Failed to remove members.'
                            });
                        }
                    });

            } catch (err) {

                console.error(err);

                document.getElementById('group_meta').textContent =
                    'Failed to load members';
            }
        });

    </script>

@endsection
