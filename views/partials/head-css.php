<?php use App\Middlewares\CSRFMiddleware; ?>

<meta name="csrf-token" content="<?= CSRFMiddleware::generateToken() ?>">