<?php

require_once 'inc-env.php';
require_once 'inc-media.php';

// --- MAIN EXECUTION FLOW (GLUE) ---

handleAdminLogin();
handleLogout();
handleFileUpload();
handleFileDelete();

// Start output buffering to capture content
ob_start();

displayMessages();
displayAdminControls();

if (!isAdmin()) 
{
    echo '<div class="gs-text-center gs-mb-3">';
    echo '<a href="#" class="ge-medialogin-link gs-btn gs-btn-primary">Login to Manage Files</a>';
    echo '</div>';
    displayLoginForm();
}

// Gallery Container
echo '<div class="gscb-container">';
echo '<div class="ge-gallery-container"></div>';
echo '</div>';

// Capture content
$strContent = ob_get_clean();

// Load HTML template
require_once 'inc-media.html';

?>