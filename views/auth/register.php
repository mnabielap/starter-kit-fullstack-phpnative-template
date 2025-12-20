<form id="registerForm">
    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" placeholder="Enter your name" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" placeholder="Enter email" required>
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" placeholder="Enter password" required>
        <div class="form-text">Must contain at least 8 characters, 1 letter and 1 number.</div>
    </div>

    <div class="mt-4">
        <button class="btn btn-success w-100" type="submit">Register</button>
    </div>
    
    <div class="mt-4 text-center">
        <p class="mb-0">Already have an account? <a href="/login" class="fw-semibold text-primary text-decoration-underline"> Signin </a> </p>
    </div>

    <div id="alertMessage" class="mt-3"></div>
</form>

<?php ob_start(); ?>
<script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const alertBox = document.getElementById('alertMessage');
        alertBox.innerHTML = '';

        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const response = await API.fetch('/v1/auth/register', {
                method: 'POST',
                body: JSON.stringify({ name, email, password })
            });

            const data = await response.json();

            if (response.ok) {
                API.saveTokens(data.tokens);
                window.location.href = API.baseUrl + '/';
            } else {
                let errorHtml = data.message;
                if (typeof data.message === 'object' && data.message !== null) {
                    errorHtml = '<ul class="mb-0 ps-3 text-start">';
                    for (const msg of Object.values(data.message)) {
                        errorHtml += `<li>${msg}</li>`;
                    }
                    errorHtml += '</ul>';
                }
                alertBox.innerHTML = `<div class="alert alert-danger">${errorHtml}</div>`;
            }
        } catch (error) {
            alertBox.innerHTML = `<div class="alert alert-danger">An error occurred</div>`;
        }
    });
</script>
<?php $script = ob_get_clean(); ?>