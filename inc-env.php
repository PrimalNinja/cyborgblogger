<?php

// blogger config

define('SANITIZECONTENT', 'FALSE');
//define('BLOCKEDWORDSFILE', 'blockedwords.txt');
define('BLOCKEDWORDSFILE', '');

define('POSTS_DIR', 'posts/');
define('POSTS_PER_PAGE', 5);
define('EXCERPT_LENGTH', 200);
define('ENABLE_MEDIA_MANAGER', 'TRUE');

define('AUTHOR', 'Julian Cassin');
define('BLOGNAME', 'Cyborg Unicorn');
define('TAGLINE', 'building web applications better, stronger, faster...');
define('LOGO_FILE', 'images/logo-noname-300x300.png');

define('ADMIN_CODE', 'admin123'); // CRITICAL: This should be handled by a secure password hash in a real app

define('DEBUGMODE', 'FALSE');

// media config

define('MEDIA_DIR', 'images/');
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,webp,svg,pdf,txt,md,json');
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes

?>