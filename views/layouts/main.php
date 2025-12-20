<?php use App\Utils\Helper; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Dashboard'; ?> - Starter Kit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link href="<?php echo Helper::baseUrl('assets/css/style.css'); ?>" rel="stylesheet">
    <?php include __DIR__ . '/../partials/head-css.php'; ?>
</head>
<body>

    <div class="layout-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main-content">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <div class="page-content">
                <div class="container-fluid">
                    <!-- Content Injection -->
                    <?php echo $content; ?>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="<?php echo Helper::baseUrl('assets/js/api-client.js'); ?>"></script>
    
    <?php if (isset($script)) echo $script; ?>
</body>
</html>