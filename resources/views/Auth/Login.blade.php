<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeChat Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@vite('resources/js/app.js')

    <!-- Optional CSS -->
    <link rel="stylesheet" href="{{ asset('/css/login.css') }}">
</head>
<body>

<div class="login-box">
    <h1>WeChat</h1>

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <form method="POST" id="login-form">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="footer">
        <div><a href="/forgetPassword">Forget Password?</a></div>
        <div>Don't have an account? <a href="/register">Register</a></div>
    </div>
</div>

<script>
        const form = document.getElementById('login-form');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = this.email.value;
            const password = this.password.value;
            const csrf = `{{ csrf_token() }}`;

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok) {
                    // Save bearer token
                    localStorage.setItem('token', data.token);

                    // Generate and send E2EE keys
                   let res2= await window.setupUserKeys();
                   if(res2.status ==="success"){
                       window.location.href = "/dashboard";
                   }

                } else {
                    alert(data.message || 'Login failed!');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred. Check console for details.');
            }
        });
</script>

</body>
</html>
