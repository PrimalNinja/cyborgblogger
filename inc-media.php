<?php
        function isAdmin() 
		{
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
        
        function handleFileUpload() 
		{
            if (!isAdmin() || !isset($_FILES['file'])) 
			{
                return;
            }
            
            $objFile = $_FILES['file'];
            $strOriginalName = basename($objFile['name']);
            $strExtension = strtolower(pathinfo($strOriginalName, PATHINFO_EXTENSION));
            $arrAllowedExt = explode(',', ALLOWED_EXTENSIONS);
            
            // Validate extension
            if (!in_array($strExtension, $arrAllowedExt)) 
			{
                header("Location: media.php?error=invalid_type");
                exit;
            }
            
            // Validate size
            if ($objFile['size'] > MAX_FILE_SIZE) 
			{
                header("Location: media.php?error=file_too_large");
                exit;
            }
            
            // Ensure directory exists
            if (!is_dir(MEDIA_DIR)) 
			{
                mkdir(MEDIA_DIR, 0755, true);
            }
            
            // Generate unique filename
            $strTargetFile = MEDIA_DIR . $strOriginalName;
            $intCounter = 1;
            while (file_exists($strTargetFile)) 
			{
                $strBaseName = pathinfo($strOriginalName, PATHINFO_FILENAME);
                $strTargetFile = MEDIA_DIR . $strBaseName . '_' . $intCounter . '.' . $strExtension;
                $intCounter++;
            }
            
            if (move_uploaded_file($objFile['tmp_name'], $strTargetFile)) 
			{
                header("Location: media.php?uploaded=true");
                exit;
            } 
			else 
			{
                header("Location: media.php?error=upload_failed");
                exit;
            }
        }
        
        function handleFileDelete() 
		{
            if (!isAdmin() || !isset($_POST['delete_file']) || !isset($_POST['filename'])) 
			{
                return;
            }
            
            $strFilename = $_POST['filename'];
            $strFilePath = MEDIA_DIR . basename($strFilename);
            
            // Security check
            $strRealMediaDir = realpath(MEDIA_DIR);
            $strRealFilePath = realpath($strFilePath);
            
            if ($strRealFilePath && 
                strpos($strRealFilePath, $strRealMediaDir) === 0 && 
                file_exists($strFilePath)) 
				{
                unlink($strFilePath);
                header("Location: media.php?deleted=true");
                exit;
            }
        }
        
        function displayMessages() 
		{
            if (isset($_GET['uploaded']) && $_GET['uploaded'] === 'true') 
			{
                echo '<div class="gs-alert gs-alert-success gs-alert-dismissible" role="alert">';
                echo 'File uploaded successfully.';
                echo '<button type="button" class="gs-btn-close" aria-label="Close"></button>';
                echo '</div>';
            }
            if (isset($_GET['deleted']) && $_GET['deleted'] === 'true') 
			{
                echo '<div class="gs-alert gs-alert-success gs-alert-dismissible" role="alert">';
                echo 'File deleted successfully.';
                echo '<button type="button" class="gs-btn-close" aria-label="Close"></button>';
                echo '</div>';
            }
            if (isset($_GET['error'])) 
			{
                $strError = $_GET['error'];
                $arrErrors = array(
                    'invalid_type' => 'Invalid file type. Allowed: ' . ALLOWED_EXTENSIONS,
                    'file_too_large' => 'File too large. Maximum: ' . (MAX_FILE_SIZE / 1048576) . 'MB',
                    'upload_failed' => 'Upload failed. Please try again.'
                );
                $strMessage = isset($arrErrors[$strError]) ? $arrErrors[$strError] : 'An error occurred.';
                echo '<div class="gs-alert gs-alert-danger gs-alert-dismissible" role="alert">';
                echo $strMessage;
                echo '<button type="button" class="gs-btn-close" aria-label="Close"></button>';
                echo '</div>';
            }
        }
        
		function displayAdminControls() 
		{
			if (isAdmin()) 
			{
				echo '<div class="gscb-container">';
				echo '<div class="gscb-title">Admin Controls</div>';
				echo '<div class="gscb-controls">';
				echo '<a href="blogger.php" class="gs-btn gs-btn-outline-primary gs-btn-sm gs-me-2">Back to Blog</a>';
				echo '<form method="post" class="gs-d-inline">';
				echo '<button type="submit" name="logout" class="gs-btn gs-btn-outline-danger gs-btn-sm">Logout</button>';
				echo '</form>';
				echo '</div>';
				
				// Upload section
				echo '<div class="gscb-controls">';
				echo '<form method="post" enctype="multipart/form-data">';
				echo '<div class="gs-inline-form">';
				echo '<input type="file" name="file" class="gs-form-control" required>';
				echo '<button type="submit" name="upload_file" class="gs-btn gs-btn-primary">Upload</button>';
				echo '</div>';
				echo '<small class="gs-text-muted" style="display: block; margin-top: 5px;">Allowed: ' . ALLOWED_EXTENSIONS . ' (Max: ' . (MAX_FILE_SIZE / 1048576) . 'MB)</small>';
				echo '</form>';
				echo '</div>';
				
				echo '</div>';
			}
		}
        
        function displayLoginForm() 
		{
            if (!isAdmin()) 
			{
				echo '<div class="ge-loginbox gscb-container" style="display:none;">';
				echo '<div class="gscb-title">Admin Login</div>';
				echo '<form method="post">';
				echo '<div class="gs-mb-3">';
				echo '<input type="password" class="ge-admincode-field gs-form-control" name="content" placeholder="Enter admin code" required>';
				echo '</div>';
				echo '<button type="submit" class="gs-btn gs-btn-success gs-w-100">Login</button>';
				echo '</form>';
				echo '</div></div>';
            }
        }
        
        function getMediaFiles() 
		{
            $arrFiles = array();
            
            if (!is_dir(MEDIA_DIR)) 
			{
                return json_encode(array('fields' => array(), 'operations' => array(), 'data' => array()));
            }
            
            $arrAllFiles = scandir(MEDIA_DIR);
            $arrData = array();
            $intCounter = 1;
            
            foreach ($arrAllFiles as $strFile) 
			{
                if ($strFile === '.' || $strFile === '..') 
				{
                    continue;
                }
                
                $strFilePath = MEDIA_DIR . $strFile;
                if (!is_file($strFilePath)) 
				{
                    continue;
                }
                
                $strExtension = strtolower(pathinfo($strFile, PATHINFO_EXTENSION));
                $intSize = filesize($strFilePath);
                $intModified = filemtime($strFilePath);
                $blnIsImage = in_array($strExtension, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'));
                
                $arrData[] = array(
                    'ID' . $intCounter++, // ID - unique prefixed like demo
                    $strFile, // FILENAME
                    $blnIsImage ? MEDIA_DIR . $strFile : '', // IMAGEURL - web path
                    $strExtension, // TYPE
                    round($intSize / 1024, 2), // SIZE_KB
                    date('Y-m-d H:i:s', $intModified) // MODIFIED
                );
            }
            
            // Sort by modified date (newest first)
            usort($arrData, function($a, $b) 
			{
                return strcmp($b[5], $a[5]);
            });
            
            // Build operations based on admin status
            $arrOperations = array();
            if (isAdmin()) 
			{
                $arrOperations[] = array(
                    'code' => 'delete',
                    'caption' => 'Delete',
                    'requiresselection' => true
                );
                $arrOperations[] = array(
                    'code' => 'copy_url',
                    'caption' => 'Copy URL',
                    'requiresselection' => true
                );
            } 
			else 
			{
                $arrOperations[] = array(
                    'code' => 'view',
                    'caption' => 'View',
                    'requiresselection' => true
                );
            }
            
            $objResult = array(
                'fields' => array(
                    array('code' => 'ID', 'type' => 'text', 'caption' => 'ID'),
                    array('code' => 'FILENAME', 'type' => 'text', 'caption' => 'Filename'),
                    array('code' => 'IMAGEURL', 'type' => 'text', 'caption' => 'Image'),
                    array('code' => 'TYPE', 'type' => 'text', 'caption' => 'Type'),
                    array('code' => 'SIZE_KB', 'type' => 'number', 'caption' => 'Size (KB)'),
                    array('code' => 'MODIFIED', 'type' => 'text', 'caption' => 'Modified')
                ),
                'operations' => $arrOperations,
                'data' => $arrData
            );
            
            return json_encode($objResult);
        }
?>