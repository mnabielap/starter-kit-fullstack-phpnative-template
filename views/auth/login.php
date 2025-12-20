<form id="loginForm">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" placeholder="Enter email" required value="admin@example.com">
    </div>

    <div class="mb-3">
        <label class="form-label" for="password-input">Password</label>
        <input type="password" class="form-control" id="password" placeholder="Enter password" required value="password1">
    </div>

    <div class="mt-4">
        <button class="btn btn-primary w-100" type="submit">Sign In</button>
    </div>
    
    <div id="alertMessage" class="mt-3"></div>
</form>

<?php ob_start(); ?>
<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const alertBox = document.getElementById('alertMessage');

        alertBox.innerHTML = '';

        try {
            const response = await API.fetch('/v1/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok) {
                API.saveTokens(data.tokens);
                window.location.href = API.baseUrl + '/'; // Redirect to Dashboard
            } else {
                alertBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        } catch (error) {
            console.error(error);
            alertBox.innerHTML = `<div class="alert alert-danger">An error occurred connecting to server</div>`;
        }
    });
</script>
<?php $script = ob_get_clean(); ?>