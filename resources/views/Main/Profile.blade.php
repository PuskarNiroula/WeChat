{{-- resources/views/profile/edit.blade.php --}}
@extends('Layouts.layout')

@section('title', 'Edit Profile')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card profile-card shadow-sm p-4" style="max-width: 500px; width: 100%; border-radius: 15px;">
            <h3 class="text-center mb-4">Update Profile</h3>

            <form id="profileForm" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <label for="profile_picture" class="profile-picture-wrapper">
                        <img id="profilePreview" src="/images/avatars/{{Auth::user()->avatar}}" class="rounded-circle"
                             style="object-fit: cover; border: 2px solid #0d6efd;  cursor: pointer;">

                        <div class="overlay">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                    </label>
                    <input type="file" name="avatar" id="profile_picture" class="d-none" accept="image/*" onchange="previewImage(event)">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            </form>
        </div>
    </div>

@endsection

@section('styles')
    <style>
        .profile-card {
            max-width: 700px; /* bigger card */
        }
        /* Card styling */
        .profile-card {
            background-color: #ffffff;
            transition: all 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }

        /* Profile picture overlay */
        .profile-picture-wrapper {
            position: relative;
            display: inline-block;
        }
        .profile-picture-wrapper .overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #0d6efd;
            color: #fff;
            border-radius: 50%;
            padding: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .profile-picture-wrapper:hover .overlay {
            background: #0b5ed7;
        }
        #profilePreview{
            height: 300px;
            width: 300px;
        }

        /* Responsive adjustments */
        @media(max-width: 576px){
            .profile-card {
                padding: 2rem 1rem;
            }
            .profile-picture-wrapper img {
                width: 400px;
                height: 400px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('profilePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        $('#profileForm').submit(async function(e) {
            e.preventDefault();

            const name = $('#name').val(); // Get name
            const fileInputElement = $('#profile_picture')[0]; // File input


            const formData = new FormData();
            formData.append('name', name);
            formData.append('avatar', fileInputElement.files[0]);

            const response = await secureFetchWithFiles('/updateProfile', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json' // Do NOT set Content-Type manually
                },
                body: formData
            });

            console.log( response);
        });

    </script>
@endsection
