{{-- resources/views/layouts/vertical-whatsapp.blade.php --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Chat App')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{asset('/css/custom.css')}}">
    @yield('styles')
</head>
<body>

<div class="main-container">

    {{-- Vertical Sidebar --}}
    <div class="sidebar">
        <div class="top-links">
            <a href="/dashboard" title="chats"><i class="bi bi-chat-dots"></i></a>
            <a href="/profile" title="profile"><i class="bi bi-person-circle"></i></a>
            <a href="#" title="notification"><i class="bi bi-bell"></i></a>
        </div>

        <div class="bottom-links">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button id="logout_buton" type="submit" title="Logout">
                    <i class="bi bi-power"></i>
                </button>
            </form>


            <div class="settings">
                <a href="javascript:void(0);" id="settingsToggle" title="settings">
                    <i class="bi bi-gear-fill" style="font-size: 24px;"></i>
                </a>

                <div id="settingsDropdown">
                    <a id="dropdownLinks" href="/change-password">Change Password</a>
                    <a id="dropdownLinks" href="/change-email">Change Email</a>
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
        console.log('clicked')
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
