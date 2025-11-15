<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeChat - Reset Password</title>
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
        .reset-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .reset-box h1 {
            margin-bottom: 30px;
            color: #128c7e;
        }
        .reset-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
        }
        .reset-box button {
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
        .reset-box button:hover {
            background-color: #128c7e;
        }
        .reset-box .success {
            color: green;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .reset-box .error {
            color: red;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .reset-box .footer {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #888;
        }
        .reset-box .footer a {
            color: #128c7e;
            text-decoration: none;
        }
        .reset-box .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="reset-box">
    <h1>Reset Password</h1>

    <div id="message"></div>

    <form id="reset-form">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        <button type="submit">Reset Password</button>
    </form>

    <div class="footer">
        <a href="{{ url('/login') }}">Back to Login</a>
    </div>
</div>

<script>
    let csrf = `{{ csrf_token() }}`;

    // Extract token and email from URL
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');
    const token = window.location.pathname.split('/').pop(); // last segment = token

    document.getElementById('reset-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const password = this.password.value;
        const password_confirmation = this.password_confirmation.value;

        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = '';
        try {
            const response = await fetch(`/api/resetPassword/${token}/${email}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ password, password_confirmation })
            });

            const data = await response.json();

            if (response.ok) {
                messageDiv.innerHTML = `<div class="success">${data.message}</div>`;
                setTimeout(() => window.location.href = '/login', 3000);
            } else {
                messageDiv.innerHTML = `<div class="error">${data.message || 'Error resetting password'}</div>`;
            }
        } catch (err) {
            console.error(err);
            messageDiv.innerHTML = `<div class="error">Something went wrong. Check console.</div>`;
        }
    });
</script>
</body>
</html>
