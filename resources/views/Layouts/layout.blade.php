<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Chat App')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('/js/sweet-alert-2.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('/css/sweet-alert-2.min.css')}}">
    <link rel="stylesheet" href="{{asset('/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('/css/group-chat.css')}}">
    @vite('resources/js/app.js')

    <script src="{{asset('/js/jquery.js')}}"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #111b21;
        }

        .wa-shell {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        .wa-nav {
            width: 60px;
            background: #1f2c33;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 0;
            gap: 0;
            flex-shrink: 0;
            border-right: 1px solid #2a3942;
            z-index: 10;
        }

        .wa-nav-top {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex: 1;
        }

        .wa-nav-bottom {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .wa-nav a,
        .wa-nav button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            color: #aebac1;
            background: transparent;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, color .15s;
            position: relative;
        }

        .wa-nav a:hover,
        .wa-nav button:hover {
            background: #2a3942;
            color: #e9edef;
        }

        .wa-nav a.active {
            color: #00a884;
        }

        .wa-nav a i,
        .wa-nav button i {
            font-size: 22px;
        }

        .wa-nav-divider {
            width: 32px;
            height: 1px;
            background: #2a3942;
            margin: 6px 0;
        }

        .wa-nav [title]:hover::after {
            content: attr(title);
            position: absolute;
            left: 56px;
            top: 50%;
            transform: translateY(-50%);
            background: #3b4a54;
            color: #e9edef;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 999;
        }

        .settings-wrap {
            position: relative;
        }

        .settings-dropdown {
            display: none;
            position: absolute;
            left: 52px;
            bottom: 0;
            background: #233138;
            border: 1px solid #2a3942;
            border-radius: 8px;
            overflow: hidden;
            min-width: 170px;
            z-index: 200;
            box-shadow: 4px 4px 18px rgba(0,0,0,.4);
        }

        .settings-dropdown a {
            display: block;
            padding: 11px 16px;
            font-size: 13.5px;
            color: #d1d7db;
            text-decoration: none;
            border-radius: 0;
            width: 100%;
            height: auto;
            transition: background .15s;
        }

        .settings-dropdown a:hover {
            background: #2a3942;
            color: #e9edef;
        }

        .wa-content {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            display: flex;
        }

        .wa-content > * {
            flex: 1;
            min-width: 0;
        }
    </style>
    @yield('styles')
</head>
<body>

<div class="wa-shell">

    <nav class="wa-nav">
        <div class="wa-nav-top">
            <a href="/dashboard" title="Chats" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-chat-dots-fill"></i>
            </a>
            <a href="/profile" title="Profile">
                <i class="bi bi-person-circle"></i>
            </a>
        </div>

        <div class="wa-nav-bottom">
            <div class="wa-nav-divider"></div>

            <div class="settings-wrap">
                <a href="javascript:void(0);" id="settingsToggle" title="Settings">
                    <i class="bi bi-gear-fill"></i>
                </a>
                <div class="settings-dropdown" id="settingsDropdown">
                    <a href="/change-password"><i class="bi bi-key me-2"></i>Change Password</a>
                    <a href="/change-email"><i class="bi bi-envelope me-2"></i>Change Email</a>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout">
                    <i class="bi bi-power"></i>
                </button>
            </form>
        </div>
    </nav>

    <div class="wa-content">
        @yield('content')
    </div>
</div>

<script src="{{asset('/js/script.js')}}"></script>
<script>
    const toggle = document.getElementById('settingsToggle');
    const dropdown = document.getElementById('settingsDropdown');

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    window.addEventListener('click', (e) => {
        if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
</script>

@yield('scripts')
</body>
</html>
