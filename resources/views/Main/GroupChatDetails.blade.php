@extends('Layouts.layout')

@section('title', 'Group Details')

@section('content')
    <style>
        html, body { height: 100%; margin: 0; }
        .gd-page { display: flex; flex-direction: column; min-height: 100vh; background: #f0f2f5; }
        .gd-hero { position: relative; background: #fff; padding: 2rem 1.5rem 1.5rem; display: flex; flex-direction: column; align-items: center; border-bottom: 1px solid #e9ecef; }
        .gd-back { position: absolute; top: 1.25rem; left: 1.25rem; background: none; border: none; cursor: pointer; color: #6c757d; display: flex; align-items: center; gap: 6px; font-size: 14px; padding: 6px 10px; border-radius: 8px; text-decoration: none; }
        .gd-back:hover { background: #f8f9fa; color: #212529; }
        .gd-avatar-ring { width: 96px; height: 96px; border-radius: 50%; background: linear-gradient(135deg, #1D9E75, #534AB7); padding: 3px; margin-bottom: 1rem; }
        .gd-avatar-inner { width: 100%; height: 100%; border-radius: 50%; background: #f0f2f5; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 500; color: #343a40; overflow: hidden; }
        .gd-avatar-inner img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .gd-group-name { font-size: 20px; font-weight: 600; color: #212529; margin-bottom: 4px; text-align: center; }
        .gd-group-meta { font-size: 13px; color: #6c757d; text-align: center; margin-bottom: 1.25rem; }
        .gd-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .gd-action-btn { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 10px 18px; background: #f8f9fa; border-radius: 12px; border: 1px solid #dee2e6; cursor: pointer; font-size: 12px; color: #6c757d; transition: background 0.15s; }
        .gd-action-btn:hover { background: #e9ecef; }
        .gd-action-btn i { font-size: 20px; color: #212529; }
        .gd-section { background: #fff; margin-top: 8px; padding: 0 1.25rem; }
        .gd-section-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 0 10px; border-bottom: 1px solid #e9ecef; }
        .gd-section-title { font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.06em; }
        .gd-section-link { font-size: 13px; color: #198754; cursor: pointer; border: none; background: none; padding: 0; }
        .gd-member-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f2f5; cursor: pointer; }
        .gd-member-item:last-child { border-bottom: none; }
        .gd-member-avatar { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; flex-shrink: 0; }
        .gd-member-info { flex: 1; min-width: 0; }
        .gd-member-name { font-size: 15px; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .gd-member-role { font-size: 12px; color: #6c757d; }
        .gd-status-dot { width: 10px; height: 10px; border-radius: 50%; background: #1D9E75; border: 2px solid #fff; flex-shrink: 0; }
        .gd-status-dot.offline { background: #adb5bd; }
        .badge-admin { font-size: 11px; padding: 2px 8px; border-radius: 999px; font-weight: 600; background: #EEEDFE; color: #534AB7; }
        .gd-media-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; padding: 12px 0; }
        .gd-media-thumb { aspect-ratio: 1; border-radius: 6px; background: #f0f2f5; display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 22px; }
        .gd-danger { background: #fff; margin-top: 8px; padding: 0 1.25rem 1rem; }
        .gd-danger-btn { display: flex; align-items: center; gap: 12px; padding: 14px 0; cursor: pointer; width: 100%; border: none; background: none; font-size: 15px; border-bottom: 1px solid #f0f2f5; }
        .gd-danger-btn:last-child { border-bottom: none; }
        .gd-danger-btn.red { color: #dc3545; }
        .gd-danger-btn.muted { color: #6c757d; }
        .gd-danger-btn i { font-size: 20px; }
        .gd-view-all { display: block; padding: 12px 0; font-size: 14px; color: #198754; background: none; border: none; cursor: pointer; }
    </style>

    <div class="gd-page">

        <div class="gd-hero">
            <a href="{{ url()->previous() }}" class="gd-back">
                <i class="bi bi-arrow-left"></i> Back
            </a>

            <div class="gd-avatar-ring">
                <div class="gd-avatar-inner">
                    <img id="groupPreview" src="/images/avatars/default_group_image.png" alt="Group avatar" style="display:none;">
                    <span id="groupInitials"></span>
                </div>
            </div>

            <h1 class="gd-group-name" id="group_name"></h1>
            <p class="gd-group-meta" id="group_meta">Loading...</p>

        </div>

        <div class="gd-section">
            <div class="gd-section-header">
                <span class="gd-section-title">Members</span>
                <button class="gd-section-link">Add member</button>
            </div>
            <ul class="list-unstyled mb-0" id="memberList"></ul>
            <button class="gd-view-all" id="viewAllBtn" style="display:none;">View all members</button>
        </div>


    </div>
@endsection

@section('scripts')
    <script>
        const conId = {{ $groupChatId }};

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

                document.getElementById('group_name').textContent = meta.name || 'Group';
                document.getElementById('groupInitials').textContent = initials(meta.name);

                if (meta.avatar) {
                    const img = document.getElementById('groupPreview');
                    img.src = '/images/avatars/' + meta.avatar;
                    img.style.display = 'block';
                    document.getElementById('groupInitials').style.display = 'none';
                }

                const members = await secureFetch(`/api/group-chat/${conId}/get-old-members`);

                document.getElementById('group_meta').textContent =
                    `${members.length} member${members.length !== 1 ? 's' : ''}`;

                if (members.length > 5) {
                    document.getElementById('viewAllBtn').style.display = 'block';
                    document.getElementById('viewAllBtn').textContent = `View all ${members.length} members`;
                }

                const list = document.getElementById('memberList');
                list.innerHTML = '';

                members.slice(0, 5).forEach((member, i) => {
                    const c = AVATAR_COLORS[i % AVATAR_COLORS.length];
                    const name = member.name ?? 'Unknown';
                    const isAdmin = member.is_admin;

                    list.insertAdjacentHTML('beforeend', `
                    <li class="gd-member-item">
                        <div class="gd-member-avatar" style="background:${c.bg};color:${c.color}">
                            ${initials(name)}
                        </div>
                        <div class="gd-member-info">
                            <p class="gd-member-name">${name}</p>
                        </div>
                    </li>
                `);
                });

            } catch (err) {
                console.error(err);
                document.getElementById('group_meta').textContent = 'Failed to load';
            }
        });
    </script>
@endsection
