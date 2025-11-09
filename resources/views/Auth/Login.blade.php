<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeChat Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .login-box h1 {
            margin-bottom: 30px;
            color: #128c7e; /* WhatsApp green */
        }
        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
        }
        .login-box button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 8px;
            background-color: #25d366;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .login-box button:hover {
            background-color: #128c7e;
        }
        .login-box .error {
            color: red;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .login-box .footer {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #888;
        }
        .login-box .footer a {
            color: #128c7e;
            text-decoration: none;
        }
        .login-box .footer a:hover {
            text-decoration: underline;
        }
    </style>
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

</div>
<script>
    let csrf=`{{csrf_token()}}`;
    console.log(csrf);
    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault(); // prevent normal form submission

        const email = this.email.value;
        const password = this.password.value;

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
                // Save bearer token in localStorage
                localStorage.setItem('token', data.token);
                alert('Login successful!');
                window.location.href = '/dashboard';
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
