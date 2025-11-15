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
            <form method="POST" action="{{route('logout')}}">
                @csrf
                <button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>

            <div style="position: relative; display: inline-block;">
                <!-- Settings Icon -->
                <a href="javascript:void(0);" id="settingsToggle">
                    <i class="fa fa-cog" style="font-size:24px; color:#128c7e;"></i>
                </a>

                <!-- Dropdown Menu -->
                <div id="settingsDropdown" style="
        display: none;
        position: absolute;
        right: 0;
        background-color: #fff;
        min-width: 180px;
        box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        z-index: 1000;
    ">
                    <a href="/change-password" style="
            display: block;
            padding: 12px 20px;
            color: #128c7e;
            text-decoration: none;
            border-bottom: 1px solid #eee;
        ">Change Password</a>
                    <a href="/change-email" style="
            display: block;
            padding: 12px 20px;
            color: #128c7e;
            text-decoration: none;
        ">Change Email</a>
                </div>
            </div>



        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="content">
        @yield('content')
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="{{asset('/js/script.js')}}"></script>
<script>
    const toggle = document.getElementById('settingsToggle');
    const dropdown = document.getElementById('settingsDropdown');

    toggle.addEventListener('click', () => {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown if clicked outside
    window.addEventListener('click', (e) => {
        if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
</script>

@yield('scripts')
</body>
</html>
