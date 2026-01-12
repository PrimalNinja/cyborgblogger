<?php

require_once 'inc-env.php';
require_once 'inc-blogger.php';

// --- MAIN EXECUTION FLOW (GLUE) ---

// Load topics first so we know which ones exist
$arrTopics = loadTopics();

// Admin login check must happen before post handling, as post content is checked for ADMIN_CODE
handleAdminLogin();
handleLogout();

// Deletion must happen early to redirect before content is displayed
handlePostDeletion();

// Update must also happen early to redirect before content is displayed
handlePostUpdate();

// Sitemap generation
handleSitemapGeneration();

// Topic creation should also happen early
$arrTopics = handleTopicCreation($arrTopics);

$strSelectedTopic = 'all';
if (isset($_GET['topic'])) 
{
    $strSelectedTopic = $_GET['topic'];
}

// Handle post submission
handlePost($strSelectedTopic);

// Start output buffering to capture content
ob_start();

// Display messages if any
displayMessages();

// Display admin controls
displayAdminControls($arrTopics);

// Display post form for admins
displayPostForm($strSelectedTopic, $arrTopics);

// Display topic filter
displayTopicFilter($arrTopics, $strSelectedTopic);

// Check for single post view
if (isset($_GET['post'])) 
{
    // Single post view
    $strPostId = $_GET['post'];
    $strTopicId = isset($_GET['topic']) ? $_GET['topic'] : '';
    displaySinglePost($strPostId, $strTopicId, $arrTopics);
} 
else 
{
    // List view with pagination
    $arrAllFiles = getPosts($arrTopics, $strSelectedTopic);
    
	$intPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $arrPosts = array_slice($arrAllFiles, ($intPage - 1) * POSTS_PER_PAGE, POSTS_PER_PAGE);
    
    displayPosts($arrPosts, $arrTopics);
    displayNavigation($intPage, count($arrAllFiles), $strSelectedTopic);
}

// Capture content
$strContent = ob_get_clean();

// Load HTML template
require_once 'inc-blogger.html';

?>