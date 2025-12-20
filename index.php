<?php

/**
 * Root Redirector
 * 
 * This file ensures that anyone accessing the root directory
 * is immediately redirected to the public/ directory.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// If user is at root, redirect to public
if ($uri !== '/public' && strpos($uri, '/public') === false) {
    header('Location: public/');
    exit;
}