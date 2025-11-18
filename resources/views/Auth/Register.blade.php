<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeChat Register</title>
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
            color: #128c7e;
        }
        .login-box input[type="text"],
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
    <h1>Create Account</h1>

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <form id="register-form">
        @csrf
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <span id="password-message" class="error"></span>
        <button type="submit">Register</button>
    </form>

    <div class="footer">
        Already have an account? <a href="/login">Login</a>
    </div>

</div>

<script>
    let csrf = `{{ csrf_token() }}`;
    document.getElementById('register-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const name = this.name.value;
        const email = this.email.value;
        const password = this.password.value;
        const confirmation = this.confirm_password.value;
        const passError=this.querySelector('#password-message');

        if(password !== confirmation){
         passError.textContent="Passwords do not match";
            return;
        }

        try {
            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ name, email, password,confirmation })
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert("Registration successful! Please verify your email before logging in.");
                window.location.href = '/login';
            } else {
                alert(data.message || 'Registration failed!');
            }

        } catch (err) {
            console.error(err);
            alert('An error occurred. Check console for details.');
        }
    });
</script>

</body>
</html>
