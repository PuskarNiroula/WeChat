{{-- resources/views/profile/edit.blade.php --}}
@extends('Layouts.layout')

@section('title', 'Edit Profile')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card profile-card shadow-sm p-4" style="max-width: 500px; width: 100%; border-radius: 15px;">
        <h3 class="text-center mb-4">Update Profile</h3>

        <form id="profileForm" method="POST" enctype="multipart/form-data">
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>
</div>

@endsection

@section('styles')

@endsection

@section('scripts')
<script>

</script>
@endsection
