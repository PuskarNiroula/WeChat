async function leaveGroupChat(conId) {

        const oldMembers = await secureFetch(`/api/group-chat/${conId}/get-old-members`);
        if(oldMembers.length<=2){
            swal.fire({
                title: "Are you sure?",
                text: `Only ${oldMembers.length} member left in this group. `,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, leave group!"
            });
        }
        const userId = localStorage.getItem('user_id');

        const allUsersMap = new Map();

        oldMembers.forEach(member => {
            if(String(member.userId) !== String(userId))
                allUsersMap.set(String(member.userId), {id: member.userId});
        });

        const allUsers = Array.from(allUsersMap.values());
        const keyData = await generateKeysForGroups(allUsers);



        return await secureFetch('/api/group-chat/leave-group', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversationId: conId,
                userData: keyData,
            })
        });

}
function gotoGroupChatEditPage() {
    window.location.href = `/group-chat/${conId}/edit`;
}
function gotoAddMemberPage() {
    window.location.href = `/group-chat/${conId}/add-members`;
}
function gotoGroupDetailsPage() {
    window.location.href = `/group-chat/${conId}/details`;
}
function gotoRemoveMembers(){
    window.location.href = `/group-chat/${conId}/remove-members`;
}
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

