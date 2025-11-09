<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Layout</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #eae6df;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            width: 100%;
            height: 100vh;
            display: flex;
        }

        /* Left Sidebar */
        .sidebar {
            background-color: #fff;
            width: 30%;
            border-right: 1px solid #ccc;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            background-color: #f0f2f5;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f2f2f2;
            cursor: pointer;
        }

        .chat-item:hover {
            background-color: #f0f0f0;
        }

        .chat-item img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .chat-info h6 {
            margin: 0;
            font-size: 16px;
        }

        .chat-info small {
            color: gray;
        }

        /* Chat Area */
        .chat-area {
            background-color: #efeae2;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background-color: #f0f2f5;
            padding: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-size: cover;
        }

        .message {
            margin-bottom: 15px;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 10px;
            display: inline-block;
        }

        .sent {
            background-color: #d9fdd3;
            align-self: flex-end;
        }

        .received {
            background-color: #fff;
            align-self: flex-start;
        }

        .chat-input {
            background-color: #f0f2f5;
            padding: 10px;
            display: flex;
            align-items: center;
        }

        .chat-input input {
            flex: 1;
            border: none;
            outline: none;
            border-radius: 20px;
            padding: 10px 15px;
            margin-right: 10px;
        }

        .chat-input button {
            border: none;
            background-color: #0b8457;
            color: white;
            padding: 10px 15px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
<div class="chat-container">

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-header">
            <h5>WhatsApp</h5>
            <i class="bi bi-three-dots-vertical"></i>
        </div>

        <div class="chat-list">
            <div class="chat-item">
                <img src="https://i.pravatar.cc/50?img=1" alt="Avatar">
                <div class="chat-info">
                    <h6>Samana</h6>
                    <small>Hey, how are you?</small>
                </div>
            </div>
            <div class="chat-item">
                <img src="https://i.pravatar.cc/50?img=2" alt="Avatar">
                <div class="chat-info">
                    <h6>John</h6>
                    <small>See you tomorrow!</small>
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

<!-- Bootstrap Icons and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

