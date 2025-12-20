<?php use App\Utils\Helper; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - Starter Kit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Helper::baseUrl('assets/css/style.css'); ?>" rel="stylesheet">
    <?php include __DIR__ . '/../partials/head-css.php'; ?>
</head>
<body>

    <div class="auth-page-wrapper">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h3>Starter Kit</h3>
                <p class="text-muted">Sign in to continue to Template.</p>
            </div>
            
            <!-- Content Injection -->
            <?php echo $content; ?>
            
        </div>
    </div>
    <script src="<?php echo Helper::baseUrl('assets/js/api-client.js'); ?>"></script>
    
    <!-- Custom Scripts for specific pages -->
    <?php if (isset($script)) echo $script; ?>
</body>
</html>