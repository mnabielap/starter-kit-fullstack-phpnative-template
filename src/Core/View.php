<?php

namespace App\Core;

use App\Utils\Helper;

class View
{
    public static function render(string $viewName, array $params = [], string $layoutName = 'main'): void
    {
        // Extract parameters to variables
        extract($params);

        // Path to view file
        $viewFile = __DIR__ . "/../../views/$viewName.php";
        
        // Start buffering to capture view content
        ob_start();
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "View [$viewName] not found.";
        }
        $content = ob_get_clean();

        // Path to layout
        $layoutFile = __DIR__ . "/../../views/layouts/$layoutName.php";

        // Render layout with content
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content; // Fallback if layout missing
        }
    }
}