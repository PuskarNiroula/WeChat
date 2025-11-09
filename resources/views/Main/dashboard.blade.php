<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/custom.css')}}">
    <script src="{{asset('js/script.js')}}"></script>
</head>

<body>
<div class="chat-container">

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between p-3 border-bottom">
            <div class="d-flex align-items-center ">
                <h5 class="mb-0 me-2 text-success fw-bold w-50">We Chat</h5>
                <div class="position-relative w-100">
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control ps-5 pe-3 py-2 rounded-pill shadow-sm"
                        placeholder="Search users..."
                    >
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                </div>
            </div>
            <i class="bi bi-three-dots-vertical fs-4 text-secondary"></i>
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

    // Debounce function: delays execution until user stops typing
    function debounce(fn, delay) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // Search function that calls the API using secureFetch
    async function search(query) {
        if (!query) return; // skip empty queries
        try {
            const results = await secureFetch(`/search?query=${encodeURIComponent(query)}`, {
                method: 'GET'
            });
            console.log('Search results:', results);
            // TODO: render results in the UI
        } catch (err) {
            console.error('Search error:', err.message);
        }
    }

    // Attach debounced search to input
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', debounce((e) => {
        search(e.target.value);
    }, 500));
</script>

</html>

