<?php
	function generateSitemap() 
	{
		$arrTopics = loadTopics();
		
		$strSiteURL = 'https://' . $_SERVER['HTTP_HOST'];
		$strDir = dirname($_SERVER['PHP_SELF']);
		if ($strDir !== '/') 
		{
			$strSiteURL .= $strDir;
		}
		
		$strXML = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$strXML .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		// Add homepage
		$strXML .= '  <url>' . "\n";
		$strXML .= '    <loc>' . htmlspecialchars($strSiteURL) . '/blogger.php</loc>' . "\n";
		$strXML .= '    <changefreq>daily</changefreq>' . "\n";
		$strXML .= '    <priority>1.0</priority>' . "\n";
		$strXML .= '  </url>' . "\n";
		
		// Add all topics view
		$strXML .= '  <url>' . "\n";
		$strXML .= '    <loc>' . htmlspecialchars($strSiteURL) . '/blogger.php?topic=all</loc>' . "\n";
		$strXML .= '    <changefreq>daily</changefreq>' . "\n";
		$strXML .= '    <priority>0.9</priority>' . "\n";
		$strXML .= '  </url>' . "\n";
		
		// Add each topic
		foreach ($arrTopics as $strTopicId => $arrTopic) 
		{
			$strXML .= '  <url>' . "\n";
			$strXML .= '    <loc>' . htmlspecialchars($strSiteURL) . '/blogger.php?topic=' . urlencode($strTopicId) . '</loc>' . "\n";
			$strXML .= '    <changefreq>weekly</changefreq>' . "\n";
			$strXML .= '    <priority>0.8</priority>' . "\n";
			$strXML .= '  </url>' . "\n";
			
			// Add posts from this topic
			$arrFiles = glob(POSTS_DIR . $strTopicId . '/*.json');
			foreach ($arrFiles as $strFile) 
			{
				$arrPost = json_decode(file_get_contents($strFile), true);
				if ($arrPost) 
				{
					$strFilename = basename($strFile, '.json');
					$strLastMod = date('Y-m-d', $arrPost['time']);
					$strSeoSlug = generateSeoSlug($arrPost['content']);
					
					$strXML .= '  <url>' . "\n";
					$strXML .= '    <loc>' . htmlspecialchars($strSiteURL) . '/blogger.php?topic=' . urlencode($strTopicId) . '&amp;post=' . urlencode($strFilename) . '&amp;d=' . urlencode($strSeoSlug) . '</loc>' . "\n";
					$strXML .= '    <lastmod>' . $strLastMod . '</lastmod>' . "\n";
					$strXML .= '    <changefreq>monthly</changefreq>' . "\n";
					$strXML .= '    <priority>0.6</priority>' . "\n";
					$strXML .= '  </url>' . "\n";
				}
			}
		}
		
		$strXML .= '</urlset>';
		
		file_put_contents('sitemap-blog.xml', $strXML);
	}

	function generateSeoSlug($strContent_a) 
	{
		// Get first line only
		$strFirstLine = strtok($strContent_a, "\n");
		$strFirstLine = trim($strFirstLine);
		
		// Convert to lowercase
		$strSlug = strtolower($strFirstLine);
		
		// Replace non-alphanumeric with hyphens
		$strSlug = preg_replace('/[^a-z0-9]+/', '-', $strSlug);
		
		// Trim hyphens from ends
		$strSlug = trim($strSlug, '-');
		
		// Limit length
		if (strlen($strSlug) > 80) 
		{
			$strSlug = substr($strSlug, 0, 80);
			$strSlug = rtrim($strSlug, '-');
		}
		
		return $strSlug;
	}
	
	function handleSitemapGeneration() 
	{
		if (isAdmin() && isset($_POST['generate_sitemap'])) 
		{
			generateSitemap();
			header("Location: blogger.php?sitemap_generated=true");
			exit;
		}
	}
	
	if (DEBUGMODE == 'TRUE')
	{
		file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Session admin: " . (isAdmin() ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);
	}
	
	function isAdmin() 
	{
		// NOTE: Using a file for session is insecure. For a production app, use PHP sessions with proper expiration.
		return file_exists('.admin');
	}
	
	function loginAdmin() 
	{
		file_put_contents('.admin', time());
	}
	
	function logoutAdmin() 
	{
		if (file_exists('.admin')) unlink('.admin');
	}
	
	function sanitizeContent($strContent_a) 
	{
		if (strlen(BLOCKEDWORDSFILE) == 0)
		{
			return $strContent_a;
		}
		
		if (!file_exists(BLOCKEDWORDSFILE)) 
		{
			return $strContent_a;
		}
		
		$arrLines = explode("\n", file_get_contents(BLOCKEDWORDSFILE));
		
		foreach ($arrLines as $strLine) 
		{
			$strLine = trim($strLine);
			if ($strLine !== '') 
			{
				// Replace matched words with asterisks, preserving case/punctuation near the match
				$strContent_a = preg_replace_callback('/' . preg_quote($strLine, '/') . '/i', function($arrMatches) 
				{
					$strMatchedText = $arrMatches[0];
					// Replace word characters with asterisks, preserve spaces/punctuation
					return preg_replace('/\w/', '*', $strMatchedText);
				}, $strContent_a);
			}
		}
		
		return $strContent_a;
	}
	
	function hasBlockedWords($str_a)
	{
		$blnResult = false;
		
		if (strlen(BLOCKEDWORDSFILE) > 0)
		{
			$blnResult = matchesPatternFile(BLOCKEDWORDSFILE, $str_a);
		}
		
		return $blnResult;
	}
	
	function matchesPatternFile($strFilename_a, $str_a)
	{
		$blnResult = false;
		$arrPatterns = [];
		
		if (file_exists($strFilename_a)) 
		{
			$arrPatterns = explode("\n", file_get_contents($strFilename_a));
		}
		
		foreach ($arrPatterns as $strPattern)
		{
			if ((trim($strPattern) !== '') && (preg_match('/' . $strPattern . '/i', $str_a)))
			{
				$blnResult = true;
				break;
			}
		}
		
		return $blnResult;
	}
	
	function loadTopics() 
	{
		$strTopicsFile = POSTS_DIR . 'topics.json';
		
		if (!file_exists($strTopicsFile)) 
		{
			// Ensure POSTS_DIR exists before creating the file
			if (!is_dir(POSTS_DIR)) 
			{
				mkdir(POSTS_DIR, 0755, true); // Use safer 0755 permissions
			}
			
			$arrTopics = array('general' => array('name' => 'General Discussion', 'description' => 'General topics'));
			file_put_contents($strTopicsFile, json_encode($arrTopics, JSON_PRETTY_PRINT));
			// Create 'general' topic directory
			if (!is_dir(POSTS_DIR . 'general/')) 
			{
				mkdir(POSTS_DIR . 'general/', 0755, true);
			}
			return $arrTopics;
		}
		
		$arrResult = json_decode(file_get_contents($strTopicsFile), true);
		if (!$arrResult) 
		{
			return array();
		}
		return $arrResult;
	}
	
	function saveTopics($arrTopics_a) 
	{
		file_put_contents(POSTS_DIR . 'topics.json', json_encode($arrTopics_a, JSON_PRETTY_PRINT));
	}
	
	function handleAdminLogin() 
	{
		if (isset($_POST['content']) && $_POST['content']) 
		{
			$strCleanContent = trim($_POST['content']);
			$strCleanContent = preg_replace('/\s+/', '', $strCleanContent);
			
			if ($strCleanContent === ADMIN_CODE) 
			{
				loginAdmin(); 
			}
		}
	}
	
	function handleLogout() 
	{
		if (isset($_POST['logout'])) 
		{
			logoutAdmin(); 
		}
	}
	
	function handleTopicCreation($arrTopics_a) 
	{
		if (isAdmin() && isset($_POST['add_topic']) && isset($_POST['topic_id']) && isset($_POST['topic_name']) && $_POST['topic_id'] && $_POST['topic_name']) 
		{
			$strTopicId = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_POST['topic_id']));
			$strDescription = '';
			if (isset($_POST['topic_description'])) 
			{
				$strDescription = htmlspecialchars(trim($_POST['topic_description']));
			}
			$arrTopics_a[$strTopicId] = array(
				'name' => htmlspecialchars(trim($_POST['topic_name'])),
				'description' => $strDescription
			);
			saveTopics($arrTopics_a);
			
			$strTopicDir = POSTS_DIR . $strTopicId . '/';
			if (!is_dir($strTopicDir)) 
			{
				mkdir($strTopicDir, 0755, true); // Use safer 0755 permissions
			}
		}
		return $arrTopics_a;
	}
	
	function handlePost($strCurrentTopic_a) 
	{
		if (isset($_POST['content']) && $_POST['content']) 
		{
			$strContent = trim($_POST['content']);
			
			// Check if it's admin code - if so, don't post it
			$strCleanContent = preg_replace('/\s+/', '', $strContent);
			if ($strCleanContent === ADMIN_CODE) 
			{
				return; // Exit early, don't save as post
			}

			// Only allow posting if user is admin
			if (!isAdmin()) 
			{
				return; // Exit early, non-admins cannot post
			}

			// Check for blocked words before posting
			if (hasBlockedWords($strContent)) 
			{
				// Redirect back with an error to prevent the post from being saved
				header("Location: blogger.php?topic=" . urlencode($strCurrentTopic_a) . "&error=blocked");
				exit;
			}
			
			if (DEBUGMODE == 'TRUE')
			{
				file_put_contents('debug.log', "Posting message: '$strContent'\n", FILE_APPEND);
			}
			
			// Determine topic (only once)
			$strTopic = 'general';
			if ($strCurrentTopic_a != 'all' && isset($_GET['topic'])) 
			{
				$strTopic = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_GET['topic'])); // Sanitize topic ID
			}
			
			$strTopicDir = POSTS_DIR . $strTopic . '/';
			if (!is_dir($strTopicDir)) 
			{
				mkdir($strTopicDir, 0755, true);
			}
			
			$strBase = date('YmdHis');
			$intCounter = 0;
			while (file_exists($strTopicDir . $strBase . sprintf('%02d', $intCounter) . '.json')) 
			{
				$intCounter++;
			}
			$strFilename = $strTopicDir . $strBase . sprintf('%02d', $intCounter) . '.json';
			
			// Store the raw content; sanitization for viewing happens later
			$strPost = json_encode(array('content' => $strContent, 'time' => time()));
			if ($strPost) 
			{
				file_put_contents($strFilename, $strPost);
	
				// --- CRITICAL FIX: Proper redirection to prevent double submission on refresh ---
				$strRedirectUrl = "blogger.php?topic=" . urlencode($strTopic);
				if (DEBUGMODE == 'TRUE')
				{
					file_put_contents('debug.log', "Post saved, redirecting to: " . $strRedirectUrl . "\n", FILE_APPEND);
				}
				header("Location: " . $strRedirectUrl);
				exit; // Stop execution immediately after redirect
				// --- END FIX ---
			} 
			else 
			{
				if (DEBUGMODE == 'TRUE')
				{
					file_put_contents('debug.log', "JSON encoding failed\n", FILE_APPEND);
				}
			}
		}
	}
	
	function handlePostUpdate()
	{
		if (isAdmin() && isset($_POST['update_post']) && isset($_POST['edit_file']) && isset($_POST['content'])) 
		{
			$strEditFile = $_POST['edit_file'];
			$strContent = trim($_POST['content']);
			
			// Security check: ensure the file is within POSTS_DIR and is a .json file
			$strRealPostsDir = realpath(POSTS_DIR);
			$strRealEditFile = realpath($strEditFile);
			
			if ($strRealEditFile && 
				strpos($strRealEditFile, $strRealPostsDir) === 0 && 
				pathinfo($strEditFile, PATHINFO_EXTENSION) === 'json' &&
				file_exists($strEditFile))
			{
				// Load existing post to preserve time
				$arrPost = json_decode(file_get_contents($strEditFile), true);
				if ($arrPost) 
				{
					// Update content but keep original time
					$arrPost['content'] = $strContent;
					file_put_contents($strEditFile, json_encode($arrPost));
					
					// Redirect back to topic
					$strTopicId = basename(dirname($strEditFile));
					header('Location: blogger.php?topic=' . urlencode($strTopicId));
					exit;
				}
			}
		}
	}
	
    function displayAdminControls($arrTopics_a) 
    {
        if (isAdmin()) 
        {
            // Full-width admin control bar - consistent with inc-media.php
            echo '<div class="gscb-container">';
            echo '<div class="gscb-title">Admin Controls</div>';
            echo '<div class="gscb-controls">';
            if (ENABLE_MEDIA_MANAGER == 'TRUE') 
			{
                echo '<a href="media.php" target="_blank" class="gs-btn gs-btn-outline-primary gs-btn-sm gs-me-2">Media</a>';
            }
            echo '<form method="post" class="gs-d-inline gs-me-2">';
            echo '<button type="submit" name="generate_sitemap" class="gs-btn gs-btn-outline-secondary gs-btn-sm">Generate Sitemap</button>';
            echo '</form>';
            echo '<form method="post" class="gs-d-inline">';
            echo '<button type="submit" name="logout" class="gs-btn gs-btn-outline-danger gs-btn-sm">Logout</button>';
            echo '</form>';
            echo '</div>';
            
            // Create Topic section - using gscb styling
            echo '<div class="gscb-controls">';
            echo '<span class="gs-h6 gs-mb-0 gs-text-dark" style="font-weight: 600;">View Topic</span><br/><br/>';
            echo '<form method="post">';

			echo '<div class="gs-inline-form gs-blogger-topic-form">';
			echo '<div class="gs-form-group gs-me-2">'; // Keep margin-right
			echo '<label class="gs-form-label gs-small">Topic ID (e.g., tech)</label>';
			echo '<input name="topic_id" class="gs-form-control" placeholder="tech" pattern="[a-z0-9\-_]+" title="Lowercase letters, numbers, hyphens, and underscores only." required>';
			echo '</div>';
			echo '<div class="gs-form-group gs-me-2">'; // Keep margin-right
			echo '<label class="gs-form-label gs-small">Topic Name (e.g., Technology)</label>';
			echo '<input name="topic_name" class="gs-form-control" placeholder="Technology" required>';
			echo '</div>';
			echo '<div class="gs-form-group gs-me-2">'; // Keep margin-right
			echo '<label class="gs-form-label gs-small">Description (Optional)</label>';
			echo '<input name="topic_description" class="gs-form-control" placeholder="Tech Discussion">';
			echo '</div>';
			echo '<div class="gs-form-group">'; // No margin on button
			echo '<button type="submit" name="add_topic" class="gs-btn gs-btn-primary">Add Topic</button>';
			echo '</div>';
			echo '</div>';
			
            echo '</form>';
            echo '</div>';
            
            echo '</div>';
        }
    }
    
    function displayPostForm($strCurrentTopic_a, $arrTopics_a) 
    {
        // Display error message if exists
        if (isset($_GET['error']) && $_GET['error'] == 'blocked') 
        {
            echo '<div class="gs-alert gs-alert-danger">Your post contained unauthorized vocabulary.</div>';
        }
        
        // Check if we're in edit mode
        $blnEditMode = isset($_GET['edit']);
        $strEditContent = '';
        $strEditFile = '';
        
        if ($blnEditMode && isAdmin()) 
        {
            $strEditId = $_GET['edit'];
            $strEditFile = POSTS_DIR . $strCurrentTopic_a . '/' . $strEditId . '.json';
            if (file_exists($strEditFile)) 
            {
                $arrEditPost = json_decode(file_get_contents($strEditFile), true);
                if ($arrEditPost) 
                {
                    $strEditContent = $arrEditPost['content'];
                }
            }
        }
        
        if (isAdmin()) 
        {
            echo '<div class="gscb-container">';
            echo '<div class="gscb-title">' . ($blnEditMode ? 'Edit Post' : 'Create New Post') . '</div>';
            echo '<form method="post">'; 
            
            if ($blnEditMode) 
            {
                echo '<input type="hidden" name="edit_file" value="' . htmlspecialchars($strEditFile) . '">';
                echo '<p>Editing Post in: <strong>' . htmlspecialchars($strCurrentTopic_a) . '</strong></p>';
            } 
            else 
            {
				// Look up the topic name from the topics array
				$strTopicName = $strCurrentTopic_a;
				if (isset($arrTopics_a[$strCurrentTopic_a]['name'])) 
				{
					$strTopicName = $arrTopics_a[$strCurrentTopic_a]['name'];
				}

				echo '<div style="width: 100%; margin-bottom: 15px;">
						<span style="font-weight: bold;">Current Posting Context: ' . htmlspecialchars($strTopicName) . '</span>
						<a href="help.php" target="_blank" 
						   class="gs-btn gs-btn-primary" 
						   style="float: right;"
						   title="Help">Help</a>
						<div style="clear: both;"></div>
					  </div>';
            }
            
            echo '<div class="gs-form-group">';
            echo '<textarea name="content" class="gs-form-control gscb-newpost" rows="4" placeholder="Speak, if you dare..." required>' . htmlspecialchars($strEditContent) . '</textarea>';
            echo '</div>';
            
            echo '<div class="gscb-controls">';
            if ($blnEditMode) 
            {
                echo '<button type="submit" name="update_post" class="gs-btn gs-btn-success">Save as ' . AUTHOR . '</button>';
                echo '<a href="blogger.php?topic=' . urlencode($strCurrentTopic_a) . '" class="gs-btn gs-btn-secondary">Cancel</a>';
            } 
            else 
            {
                echo '<button type="submit" class="gs-btn gs-btn-success">Post as ' . AUTHOR . '</button>';
            }
            echo '</div>';
            
            echo '</form>';
            echo '</div>';
        } 
        else 
        {
            // Login mode - show password input, hidden by default
            echo '<div class="ge-loginbox gscb-container" style="display:none;">';
            echo '<div class="gscb-title">Admin Login</div>';
            echo '<form method="post">';
            echo '<div class="gs-form-group">';
            echo '<input type="password" class="ge-admincode-field gs-form-control" name="content" placeholder="Enter admin code" required>';
            echo '</div>';
            echo '<button type="submit" class="gs-btn gs-btn-success gs-w-100">Login</button>';
            echo '</form>';
            echo '</div>';
        }
    }
    
	function displayTopicFilter($arrTopics_a, $strSelectedTopic_a) 
	{
		echo '<div class="gscb-container gs-mb-3">';
		echo '<div class="gs-d-flex gs-justify-content-between gs-align-items-center gs-gap-3">';
		
		// Topic filter - left side
		echo '<div class="gs-d-flex gs-align-items-center gs-gap-3">';
		echo '<span class="gs-h6 gs-mb-0 gs-text-dark" style="font-weight: 600;">View Topic</span>'; // Using h6, bold, dark
		echo '<form method="get" class="gs-d-inline">';
		echo '<select name="topic" class="gs-form-select" onchange="this.form.submit()">';
		
		$strAllSelected = ($strSelectedTopic_a == 'all') ? ' selected' : '';
		echo '<option value="all"' . $strAllSelected . '>All Topics</option>';
		
		foreach ($arrTopics_a as $strId => $arrTopic) 
		{
			$strSelected = ($strSelectedTopic_a == $strId) ? ' selected' : '';
			echo '<option value="' . htmlspecialchars($strId) . '"' . $strSelected . '>' . htmlspecialchars($arrTopic['name']) . '</option>';
		}
		
		echo '</select>';
		echo '</form>';
		echo '</div>';
		
		// Login link - right side
		if (!isAdmin()) 
		{
			echo '<a href="#" class="ge-bloggerlogin-link">Login</a>';
		}
		
		echo '</div>';
		echo '</div>';
	}

	function getPosts($arrTopics_a, $strSelectedTopic_a) 
	{
		if ($strSelectedTopic_a == 'all') 
		{
			$arrAllFiles = array();
			foreach (array_keys($arrTopics_a) as $strTopicId) 
			{
				$arrFiles = glob(POSTS_DIR . $strTopicId . '/*.json');
				foreach ($arrFiles as $strFile)
				{
					// Extract timestamp from filename (YYYYMMDDHHiissXX)
					$strFilename = basename($strFile, '.json');
					// Use the full filename as the sort key for maximum granularity
					$arrAllFiles[] = $strFilename . ',' . $strFile; 
				}
			}
			// Sort by the timestamp part (first part before comma)
			rsort($arrAllFiles);
			
			// Convert back to just file paths
			$arrSortedFiles = array();
			foreach ($arrAllFiles as $strEntry)
			{
				$arrParts = explode(',', $strEntry, 2);
				$arrSortedFiles[] = $arrParts[1]; // The file path
			}
			$arrAllFiles = $arrSortedFiles;
		} 
		else 
		{
			// Ensure the topic is safe
			$strSafeTopic = preg_replace('/[^a-z0-9\-_]/', '', strtolower($strSelectedTopic_a));
			$arrAllFiles = glob(POSTS_DIR . $strSafeTopic . '/*.json');
			rsort($arrAllFiles);
		}
		
		if (DEBUGMODE == 'TRUE')
		{
			// LOG THE SORTING RESULTS
			$strLogEntry = date('Y-m-d H:i:s') . " - Topic: $strSelectedTopic_a\n";
			$strLogEntry .= "Files found: " . count($arrAllFiles) . "\n";
			foreach (array_slice($arrAllFiles, 0, 5) as $intI => $strFile) 
			{
				$strLogEntry .= "[$intI] " . basename($strFile) . " from " . basename(dirname($strFile)) . "\n";
			}
			$strLogEntry .= "---\n";
			file_put_contents('debug.log', $strLogEntry, FILE_APPEND);
		}
		
		return $arrAllFiles;
	}
	
	function handlePostDeletion() 
	{
		if (isAdmin() && isset($_POST['delete_post']) && isset($_POST['post_file'])) 
		{
			$strPostFile = $_POST['post_file'];
			
			// Security check: ensure the file is within POSTS_DIR and is a .json file
			$strRealPostsDir = realpath(POSTS_DIR);
			$strRealPostFile = realpath($strPostFile);
			
			if ($strRealPostFile && 
				strpos($strRealPostFile, $strRealPostsDir) === 0 && 
				pathinfo($strPostFile, PATHINFO_EXTENSION) === 'json' &&
				file_exists($strPostFile)) 
			{
				unlink($strPostFile);
				
				// Redirect back to current topic with success message
				$strTopic = 'all';
				if (isset($_GET['topic'])) 
				{
					$strTopic = $_GET['topic'];
				}
				header("Location: blogger.php?topic=" . urlencode($strTopic) . "&deleted=true");
				exit;
			}
		}
	} 
	
    function displaySinglePost($strPostId_a, $strTopicId_a, $arrTopics_a) 
    {
        $strFile = POSTS_DIR . $strTopicId_a . '/' . $strPostId_a . '.json';
        
        if (!file_exists($strFile)) 
        {
            echo '<div class="gs-alert gs-alert-warning">Post not found.</div>';
            echo '<div class="gscb-controls">';
            echo '<a href="blogger.php" class="gs-btn gs-btn-primary">← Back to Blog</a>';
            echo '</div>';
            return;
        }
        
        $arrPost = json_decode(file_get_contents($strFile), true);
        if (!$arrPost) 
        {
            echo '<div class="gs-alert gs-alert-warning">Invalid post data.</div>';
            echo '<div class="gscb-controls">';
            echo '<a href="blogger.php" class="gs-btn gs-btn-primary">← Back to Blog</a>';
            echo '</div>';
            return;
        }
        
        // Back button
        $strTopicName = isset($arrTopics_a[$strTopicId_a]['name']) ? $arrTopics_a[$strTopicId_a]['name'] : $strTopicId_a;
        echo '<div class="gscb-controls gs-mb-4">';
        echo '<a href="blogger.php?topic=' . urlencode($strTopicId_a) . '" class="gs-btn gs-btn-outline-primary gs-btn-sm">← Back to ' . htmlspecialchars($strTopicName) . '</a>';
        echo '<a href="blogger.php" class="gs-btn gs-btn-outline-secondary gs-btn-sm">← Back to All Topics</a>';
        echo '</div>';
        
        // Display the post
        $strDisplayContent = $arrPost['content'];
        if (SANITIZECONTENT == 'TRUE') 
        {
            $strDisplayContent = sanitizeContent(htmlspecialchars($strDisplayContent));
        }
        
        echo '<div class="gscb-container">';
        //echo '<div class="gscb-title">[' . htmlspecialchars($strTopicName) . ']</div>';
        // Wiki content - admin-only, allow raw HTML and wiki markup
        echo '<div class="wiki-content" data-wiki="' . str_replace('"', '&quot;', $strDisplayContent) . '" style="font-size: 1.1rem; line-height: 1.8;"></div>';
        echo '<div class="gscb-controls gs-border-top gs-pt-3">';
        echo '<small class="gs-text-muted">Posted: ' . date('Y-m-d H:i:s', $arrPost['time']) . ' by **' . AUTHOR . '**</small>';
        
        // Show delete button for admins
        if (isAdmin()) 
        {
            echo '<form method="post" class="gs-d-inline">';
            echo '<input type="hidden" name="post_file" value="' . htmlspecialchars($strFile) . '">';
            echo '<button type="submit" name="delete_post" class="gs-btn gs-btn-outline-danger gs-btn-sm">Delete</button>';
            echo '</form>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    function displayMessages() 
    {
        if (isset($_GET['deleted']) && $_GET['deleted'] === 'true') 
        {
            echo '<div class="gs-alert gs-alert-success gs-alert-dismissible" role="alert">';
            echo 'Post successfully erased.';
            echo '<button type="button" class="gs-btn-close" aria-label="Close"></button>';
            echo '</div>';
        }
        if (isset($_GET['sitemap_generated']) && $_GET['sitemap_generated'] === 'true') 
        {
            echo '<div class="gs-alert gs-alert-success gs-alert-dismissible" role="alert">';
            echo 'Sitemap successfully generated at <a href="sitemap-blog.xml" target="_blank">sitemap-blog.xml</a>';
            echo '<button type="button" class="gs-btn-close" aria-label="Close"></button>';
            echo '</div>';
        }
    }
    
	function displayPosts($arrPosts_a, $arrTopics_a) 
	{
		foreach ($arrPosts_a as $strFile) 
		{
			$arrPost = json_decode(file_get_contents($strFile), true);
			if ($arrPost) 
			{
				$strTopicId = basename(dirname($strFile));
				$strTopicName = $strTopicId;
				if (isset($arrTopics_a[$strTopicId]['name'])) 
				{
					$strTopicName = $arrTopics_a[$strTopicId]['name'];
				}
				
				$strDisplayContent = $arrPost['content'];

				// Sanitize content when displaying
				if (SANITIZECONTENT == 'TRUE')
				{
					$strDisplayContent = sanitizeContent(htmlspecialchars($strDisplayContent));
				}
				
				// Create excerpt (first EXCERPT_LENGTH chars)
				$strExcerpt = $strDisplayContent;
				if (strlen($strExcerpt) > EXCERPT_LENGTH) 
				{
					$strExcerpt = substr($strExcerpt, 0, EXCERPT_LENGTH) . '...';
				}
				
				$strPostId = basename($strFile, '.json');
				$strPermalink = 'blogger.php?topic=' . urlencode($strTopicId) . '&post=' . urlencode($strPostId);
				
				// START: Main container
				echo '<div class="gscb-container gs-mb-3">';
				
				// Wiki content with floating button
				echo '<div class="wiki-content" data-wiki="' . str_replace('"', '&quot;', $strExcerpt) . '">';
				echo '<div class="wiki-excerpt">' . htmlspecialchars($strExcerpt) . '</div>'; // Text content
				//echo '<div style="clear: both;"></div>'; // Clearfix
				echo '</div>'; // Close wiki-content

				// Post metadata
				echo '<div class="gscb-controls gs-border-top gs-pt-2">';
				//echo '<small class="gs-text-muted">Posted: ' . date('Y-m-d H:i:s', $arrPost['time']) . ' by **' . AUTHOR . '**</small>';
				echo '<small class="gs-text-muted">&nbsp;</small>';
				echo '<a href="' . htmlspecialchars($strPermalink) . '" style="float: right;" class="gs-btn gs-btn-sm gs-btn-outline-primary">Read more →</a>';
				echo '</div>'; // Close gscb-controls
				
				// Admin buttons (only for admins)
				if (isAdmin())
				{
					echo '<div class="gscb-controls gs-pt-2">';
					echo '<a href="blogger.php?topic=' . urlencode($strTopicId) . '&edit=' . urlencode(basename($strFile, '.json')) . '" class="gs-btn gs-btn-outline-primary gs-btn-sm gs-me-2">Edit</a>';
					
					echo '<form method="post" class="ge-bloggerdelete-form gs-d-inline">';
					echo '<input type="hidden" name="post_file" value="' . htmlspecialchars($strFile) . '">';
					echo '<button type="submit" name="delete_post" class="gs-btn gs-btn-outline-danger gs-btn-sm">Delete</button>';
					echo '</form>';
					echo '</div>'; // Close admin controls
				}
				
				echo '</div>'; // Close gscb-container
				// END: Main container
			}
		}
	}
    
	function displayNavigation($intPage_a, $intTotalPosts_a, $strSelectedTopic_a) 
	{
		// DEBUG - SHOWS ON PAGE
		//echo '<div style="background:yellow;padding:5px;border:1px solid red;margin:5px 0;">';
		//echo 'DEBUG: Page=' . $intPage_a . ', TotalPosts=' . $intTotalPosts_a . ', POSTS_PER_PAGE=' . POSTS_PER_PAGE . ', NextPageShows=' . (($intPage_a+1) * POSTS_PER_PAGE < $intTotalPosts_a ? 'YES' : 'NO');
		//echo '</div>';

		echo '<div class="gs-d-flex gs-justify-content-center gs-mt-4 gs-mb-4 gs-gap-2">';

		// Previous Button
		if ($intPage_a > 1) 
		{
			echo '<a class="gs-btn gs-btn-outline-primary" href="?topic=' . urlencode($strSelectedTopic_a) . '&page=' . ($intPage_a-1) . '">← Previous</a>';
		} 
		else 
		{
			echo '<button class="gs-btn gs-btn-outline-secondary" disabled>← Previous</button>';
		}

		// Next Button
		if ($intPage_a * POSTS_PER_PAGE < $intTotalPosts_a) 
		{
			echo '<a class="gs-btn gs-btn-outline-primary" href="?topic=' . urlencode($strSelectedTopic_a) . '&page=' . ($intPage_a+1) . '">Next →</a>';
		} 
		else 
		{
			echo '<button class="gs-btn gs-btn-outline-secondary" disabled>Next →</button>';
		}

		echo '</div>';
	}