<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeChat - Forgot Password</title>
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
        .forgot-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .forgot-box h1 {
            margin-bottom: 30px;
            color: #128c7e;
        }
        .forgot-box input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
        }
        .forgot-box button {
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
        .forgot-box button:hover {
            background-color: #128c7e;
        }
        .forgot-box .success {
            color: green;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .forgot-box .error {
            color: red;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .forgot-box .footer {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #888;
        }
        .forgot-box .footer a {
            color: #128c7e;
            text-decoration: none;
        }
        .forgot-box .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="forgot-box">
    <h1>Forgot Password</h1>

    <div id="message"></div>

    <form id="forgot-form">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <div class="footer">
        <a href="{{ url('/login') }}">Back to Login</a>
    </div>
</div>

<script>
    let csrf = `{{ csrf_token() }}`;

    document.getElementById('forgot-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = this.email.value;

        try {
            const response = await fetch('{{route('password.email')}}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = '';

            if (response.ok) {
                messageDiv.innerHTML = `<div class="success">${data.message}</div>`;
            } else {
                messageDiv.innerHTML = `<div class="error">${data.message || 'Error sending reset email'}</div>`;
            }
        } catch (err) {
            console.error(err);
            document.getElementById('message').innerHTML = `<div class="error">Something went wrong. Check console.</div>`;
        }
    });
</script>
</body>
</html>
