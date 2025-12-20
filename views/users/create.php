<?php use App\Utils\Helper; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Create New User</h4>
            </div>
            <div class="card-body">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" required>
                        <div class="form-text">At least 8 chars, 1 letter, 1 number.</div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="<?php echo Helper::baseUrl('users'); ?>" class="btn btn-light">Cancel</a>
                    </div>
                    
                    <div id="alertMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    document.getElementById('createUserForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const alertBox = document.getElementById('alertMessage');
        alertBox.innerHTML = ''; 

        const formData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            role: document.getElementById('role').value
        };

        try {
            const response = await API.fetch('/v1/users', {
                method: 'POST',
                body: JSON.stringify(formData)
            });
            const data = await response.json();

            if (response.ok) {
                window.location.href = API.baseUrl + '/users';
            } else {
                let errorHtml = data.message;
                if (typeof data.message === 'object' && data.message !== null) {
                    errorHtml = '<ul class="mb-0 ps-3">';
                    for (const msg of Object.values(data.message)) {
                        errorHtml += `<li>${msg}</li>`;
                    }
                    errorHtml += '</ul>';
                }
                alertBox.innerHTML = `<div class="alert alert-danger">${errorHtml}</div>`;
            }
        } catch (error) {
            console.error(error);
            alertBox.innerHTML = `<div class="alert alert-danger">An unexpected error occurred.</div>`;
        }
    });
</script>
<?php $script = ob_get_clean(); ?>