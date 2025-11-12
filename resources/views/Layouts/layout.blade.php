{{-- resources/views/layouts/vertical-whatsapp.blade.php --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Chat App')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @yield('styles')
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .main-container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 80px;
            background-color: #0d6efd;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .sidebar .top-links, .sidebar .bottom-links {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            display: flex;
            justify-content: center;
        }

        .sidebar a:hover {
            color: #cce5ff;
        }

        /* Main content */
        .content {
            flex-grow: 1;
            background-color: #f0f2f5;
            overflow-y: auto;
        }

    </style>
</head>
<body>

<div class="main-container">

    {{-- Vertical Sidebar --}}
    <div class="sidebar">
        <div class="top-links">
            <a href="/dashboard"><i class="bi bi-chat-dots"></i></a>
            <a href="/profile"><i class="bi bi-person-circle"></i></a>
            <a href="#"><i class="bi bi-bell"></i></a>
        </div>

        <div class="bottom-links">
            <a href="#"><i class="bi bi-gear"></i></a>
            <a href="#"><i class="bi bi-person"></i></a>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="content">
        @yield('content')
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{asset('/js/script.js')}}"></script>

@yield('scripts')
</body>
</html>
