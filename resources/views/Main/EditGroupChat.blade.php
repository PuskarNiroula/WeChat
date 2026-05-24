@extends('Layouts.layout')

@section('title', 'Edit Group')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card p-4 shadow-sm" style="max-width: 500px; width: 100%; border-radius: 15px;">

            <h3 class="text-center mb-4">Update Group</h3>

            <form id="groupForm" enctype="multipart/form-data">

                <div class="text-center mb-4">
                    <label for="group_image" style="position: relative; display: inline-block;">
                        <img id="groupPreview" src="/images/avatars/default_group_image.png" style="width:300px;height:300px;border-radius:50%;object-fit:cover;border:2px solid #198754;cursor:pointer;">
                        <div style="position:absolute;bottom:0;right:0;background:#198754;color:#fff;border-radius:50%;padding:8px;">
                            <i class="bi bi-camera-fill"></i>
                        </div>
                    </label>

                    <input type="file" name="image" id="group_image" class="d-none" accept="image/*" onchange="previewGroupImage(event)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Group Name</label>
                    <input type="text" id="group_name" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Save Changes</button>

            </form>

        </div>
    </div>
@endsection

@section('scripts')
    <script>

        let conId = @json($groupChatId);

        function previewGroupImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('groupPreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const meta = await secureFetch(`/api/conversation/${conId}/meta`);
            $('#group_name').val(meta.name);
            if (meta.avatar) {
                $('#groupPreview').attr('src', '/images/avatars/' + meta.avatar);
            }
        });

        $('#groupForm').submit(async function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('name', $('#group_name').val());

            const fileInput = $('#group_image')[0];
            if (fileInput.files && fileInput.files[0]) {
                formData.append('image', fileInput.files[0]);
            }

          const response=  await secureFetch(`/api/group/${conId}/update`, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            });
            if(response.status==="success"){
                Swal.fire({
                    icon: 'success',
                    title: 'Group updated successfully',
                    showConfirmButton: true,
                    timer: 1500
                }).then(()=>{
                    window.location.href = `/group-chat/${conId}/details`;
                });
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    showConfirmButton: true,
                    timer: 1500
                })

            }


        });

    </script>
@endsection
