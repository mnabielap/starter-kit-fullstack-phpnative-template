<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Dashboard</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Welcome</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">This is the Fullstack PHP Native Starter Kit.</p>
                <div class="alert alert-info">
                    <strong>Logged in as:</strong> <span id="user-email-display">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    // Fetch User Profile to test Token
    const token = localStorage.getItem('accessToken');
    if(token) {
        const payload = JSON.parse(atob(token.split('.')[1]));
        document.getElementById('user-email-display').innerText = `User ID: ${payload.sub}`;
    }
</script>
<?php $script = ob_get_clean(); ?>