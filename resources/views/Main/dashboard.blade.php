<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Chat</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/custom.css')}}">
    <script src="{{asset('js/script.js')}}"></script>


</head>

<body>
<div class="chat-container">

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-header">
            <h5>We Chat</h5>
            <i class="bi bi-three-dots-vertical"></i>
        </div>

        <div class="chat-list">
            <div class="chat-item">
                <img src="https://i.pravatar.cc/50?img=1" alt="Avatar">
                <div class="chat-info">
                    <h6>Samana</h6>
                    <small>here comes the last message</small>
                </div>
            </div>
            <div class="chat-item">
                <img src="https://i.pravatar.cc/50?img=2" alt="Avatar">
                <div class="chat-info">
                    <h6>John</h6>
                    <small>Last message here too</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Chat Area --}}
    <div class="chat-area">
        <div class="chat-header">
            <img src="https://i.pravatar.cc/50?img=1" alt="Avatar">
            <div>
                <h6 class="m-0">Samana</h6>
                <small>Online</small>
            </div>
        </div>

        <div class="chat-messages d-flex flex-column">
            @yield('chat')
        </div>

        <div class="chat-input">
            <input type="text" placeholder="Type a message">
            <button><i class="bi bi-send"></i></button>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    async function loadMessages(id) {
        try {
            const response = await secureFetch(`/getMessages?conversation_id=${id}`, {
                method: "GET"
            });

            console.log(response);
        } catch (err) {
            console.error("Error fetching messages:", err.message);
        }
    }

</script>

</html>

