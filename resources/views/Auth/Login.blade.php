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
    <div class="footer">
        <div><a href="/forgetPassword">Forget Password?</a></div>
       <div> Don't have an account? <a href="/register">Register</a></div>
    </div>

</div>
<script>
    let csrf = `{{ csrf_token() }}`;

    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = this.email.value;
        const password = this.password.value;

        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (!response.ok) {
                alert(data.message || 'Login failed!');
                return;
            }

            // 🔐 Save token
            localStorage.setItem('token', data.token);

            // =========================
            // 🔐 E2EE KEY LOGIC START
            // =========================

            if (data.encryption?.needs_key_setup) {

                console.log("Generating RSA keys...");

                const keyPair = await window.crypto.subtle.generateKey(
                    {
                        name: "RSA-OAEP",
                        modulusLength: 2048,
                        publicExponent: new Uint8Array([1, 0, 1]),
                        hash: "SHA-256"
                    },
                    true,
                    ["encrypt", "decrypt"]
                );

                const publicKey = await window.crypto.subtle.exportKey(
                    "spki",
                    keyPair.publicKey
                );

                // Export private key
                const privateKey = await window.crypto.subtle.exportKey(
                    "pkcs8",
                    keyPair.privateKey
                );

                // Convert to base64
                const publicKeyBase64 = btoa(String.fromCharCode(...new Uint8Array(publicKey)));
                const privateKeyBase64 = btoa(String.fromCharCode(...new Uint8Array(privateKey)));

                // Store PRIVATE KEY locally ONLY
                localStorage.setItem('private_key', privateKeyBase64);

                // Send PUBLIC KEY to backend
                await fetch('/api/user/public-key', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${data.token}`,
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        public_key: publicKeyBase64
                    })
                });

                console.log("RSA keys generated and public key sent!");
            }

            // =========================
            // 🔐 E2EE KEY LOGIC END
            // =========================

            window.location.href = '/dashboard';

        } catch (err) {
            console.error(err);
            alert('An error occurred. Check console for details.');
        }
    });
</script>
</body>
</html>
