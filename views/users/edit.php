<?php use App\Utils\Helper; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit User</h4>
            </div>
            <div class="card-body">
                <form id="editUserForm">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update User</button>
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
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    async function loadUser() {
        if (!userId) return;
        const response = await API.fetch(`/v1/users/${userId}`);
        const data = await response.json();
        
        if (response.ok) {
            document.getElementById('userId').value = data.id;
            document.getElementById('name').value = data.name;
            document.getElementById('email').value = data.email;
        } else {
             document.getElementById('alertMessage').innerHTML = `<div class="alert alert-danger">User not found</div>`;
        }
    }

    document.getElementById('editUserForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const alertBox = document.getElementById('alertMessage');
        alertBox.innerHTML = '';

        const data = {};
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        if (name) data.name = name;
        if (email) data.email = email;
        if (password) data.password = password;

        try {
            const response = await API.fetch(`/v1/users/${userId}`, {
                method: 'PATCH',
                body: JSON.stringify(data)
            });
            const resData = await response.json();

            if (response.ok) {
                window.location.href = API.baseUrl + '/users';
            } else {
                let errorHtml = resData.message;
                if (typeof resData.message === 'object' && resData.message !== null) {
                    errorHtml = '<ul class="mb-0 ps-3">';
                    for (const msg of Object.values(resData.message)) {
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

    loadUser();
</script>
<?php $script = ob_get_clean(); ?>