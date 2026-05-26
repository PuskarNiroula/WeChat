@extends('Layouts.layout')

@section('title', 'Change Password')

@section('content')
    <div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;">
        <div class="card shadow border-0"
             style="max-width: 500px; width: 100%; border-radius: 18px; overflow: hidden;">

            <div class="bg-primary text-white text-center py-4">
                <h3 class="mb-1 fw-bold">Change Password</h3>
                <p class="mb-0 small opacity-75">
                    Update your account password securely
                </p>
            </div>

            <div class="card-body p-4">

                <div id="messageBox" class="alert d-none"></div>

                <form id="profileForm">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            New Password
                        </label>
                        <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Enter new password"
                                required
                            >
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Confirm Password
                        </label>
                        <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                placeholder="Confirm new password"
                                required
                            >
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn btn-primary w-100 py-2 fw-semibold"
                        id="submitBtn"
                    >
                        Save Changes
                    </button>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card {
            transition: 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .form-control {
            border-radius: 0 8px 8px 0 !important;
            padding: 10px 14px;
        }

        .input-group-text {
            border-radius: 8px 0 0 8px !important;
            background: #f8f9fa;
        }

        .btn-primary {
            border-radius: 10px;
        }

        .alert {
            border-radius: 10px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('submitBtn');
            const messageBox = document.getElementById('messageBox');

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            messageBox.classList.add('d-none');
            messageBox.classList.remove('alert-success', 'alert-danger');


            if (password !== confirmPassword) {
                messageBox.classList.remove('d-none');
                messageBox.classList.add('alert-danger');
                messageBox.innerText = 'Passwords do not match.';
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving...';

            try {
                const response = await secureFetch('/api/change-password', {
                    method: 'POST',
                    body:{
                        new_password: password,
                        password_confirmation: confirmPassword
                    },
                });

                const data = await response.json();

                if (response.ok) {
                    messageBox.classList.remove('d-none');
                    messageBox.classList.add('alert-success');
                    messageBox.innerText = data.message || 'Password changed successfully.';

                    form.reset();
                } else {
                    messageBox.classList.remove('d-none');
                    messageBox.classList.add('alert-danger');
                    messageBox.innerText = data.message || 'Something went wrong.';
                }

            } catch (error) {
                messageBox.classList.remove('d-none');
                messageBox.classList.add('alert-danger');
                messageBox.innerText = 'Server error. Please try again.';
            }

            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Changes';
        });
    </script>
@endsection
