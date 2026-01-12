<?php
	require_once 'inc-env.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyborgWiki Syntax Guide - How to Use</title>
    <link href="css/cyborgBlogger.css" rel="stylesheet">
    <link href="css/cyborgGlobal.css" rel="stylesheet">
</head>
<body>
    <!-- Dark blue banner header -->
    <div style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 0.667rem 0; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <div class="gs-container-fluid">
            <?php
            // Display logo with title and tagline
            if (file_exists(LOGO_FILE)) 
			{
                echo '<div class="gs-d-flex gs-align-items-center gs-justify-content-center">';
                echo '<div class="gs-me-3">';
                echo '<img src="' . LOGO_FILE . '" style="width: 60px; height: 60px; border-radius: 0.5rem;">';
                echo '</div>';
                echo '<div>';
                echo '<a href="blogger.php" class="gs-text-decoration-none">';
                echo '<h1 class="gs-mb-0 gs-text-white">' . BLOGNAME . '</h1>';
                echo '<span class="gs-text-white" style="font-size: 0.875rem; opacity: 0.9;">' . TAGLINE . '</span>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            } 
			else 
			{
                echo '<h1 class="gs-mb-0 gs-text-center gs-text-white">' . BLOGNAME . '</h1>';
                echo '<p class="gs-text-center gs-text-white gs-mb-0" style="font-size: 0.875rem; opacity: 0.9;">' . TAGLINE . '</p>';
            }
            ?>
        </div>
    </div>
    
    <div class="gs-container-fluid">
        <div class="gs-row gs-g-3">
            <div class="gs-col">
                <!-- Header -->
                <div class="gs-card gs-mb-4">
                    <div class="gs-card-body">
                        <h1 class="gscb-title">CyborgWiki Syntax Guide</h1>
                        <p class="gs-text-muted">Learn how to format content using the CyborgWiki markup language</p>
                    </div>
                </div>

                <!-- Basic Formatting -->
                <div class="gs-card gs-mb-4">
                    <div class="gs-card-body">
                        <h2 class="gs-h5 gs-mb-3">Basic Text Formatting</h2>
                        
                        <div class="gs-row gs-g-3 gs-mb-4">
                            <div class="gs-col-md-4">
                                <h3 class="gs-h6 gs-mb-2">Bold Text</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <code>'''This text is bold'''</code>
                                </div>
                                <p class="gs-small gs-text-muted gs-mt-2">Result: <strong>This text is bold</strong></p>
                            </div>
                            
                            <div class="gs-col-md-4">
                                <h3 class="gs-h6 gs-mb-2">External Links</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <code>[http://www.example.com|Example Website]</code>
                                </div>
                                <p class="gs-small gs-text-muted gs-mt-2">Creates: <a href="#" class="gs-text-decoration-none">Example Website</a></p>
                            </div>
                            
                            <div class="gs-col-md-4">
                                <h3 class="gs-h6 gs-mb-2">Internal Links</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <code>[[page2|Page 2]]</code>
                                </div>
                                <p class="gs-small gs-text-muted gs-mt-2">Links to: Page 2 within the wiki</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="gs-card gs-mb-4">
                    <div class="gs-card-body">
                        <h2 class="gs-h5 gs-mb-3">Images</h2>
                        
                        <div class="gs-row gs-g-3">
                            <div class="gs-col-md-6">
                                <h3 class="gs-h6 gs-mb-2">External Images</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <code>[Image:https://example.com/photo.jpg|External Image]</code>
                                </div>
                                <p class="gs-small gs-text-muted gs-mt-2">Loads image from external URL</p>
                            </div>
                            
                            <div class="gs-col-md-6">
                                <h3 class="gs-h6 gs-mb-2">Internal Images</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <code>[[Image:example.jpg|Example]]</code>
                                </div>
                                <p class="gs-small gs-text-muted gs-mt-2">Loads image from wiki's image directory</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lists -->
                <div class="gs-card gs-mb-4">
                    <div class="gs-card-body">
                        <h2 class="gs-h5 gs-mb-3">Lists</h2>
                        
                        <div class="gs-row gs-g-3">
                            <div class="gs-col-md-6">
                                <h3 class="gs-h6 gs-mb-2">Unordered Lists</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <pre class="gs-mb-0">* Item 1
* Item 2
* Item 3</pre>
                                </div>
                            </div>
                            
                            <div class="gs-col-md-6">
                                <h3 class="gs-h6 gs-mb-2">Ordered Lists</h3>
                                <div class="gs-bg-light gs-p-3 gs-border">
                                    <pre class="gs-mb-0"># First item
# Second item
# Third item</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Code Blocks -->
                <div class="gs-card gs-mb-4">
                    <div class="gs-card-body">
                        <h2 class="gs-h5 gs-mb-3">Code & Tables</h2>
                        
                        <div class="gs-mb-4">
                            <h3 class="gs-h6 gs-mb-2">Inline Code</h3>
                            <div class="gs-bg-light gs-p-3 gs-border">
                                <code>&lt;code&gt;print("Hello World!")&lt;/code&gt;</code>
                            </div>
                            <p class="gs-small gs-text-muted gs-mt-2">Displays: <code>print("Hello World!")</code></p>
                        </div>
                        
                        <div>
                            <h3 class="gs-h6 gs-mb-2">Tables</h3>
                            <div class="gs-bg-light gs-p-3 gs-border">
                                <pre>{|
! Header 1 !! Header 2 !! Header 3
|-
| Cell 1 || Cell 2 || Cell 3
|-
| Cell 4 || Cell 5 || Cell 6
|}</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Headings & Structure -->
                <div class="gs-card gs-mb-4">
                    <div class="gs-card-body">
                        <h2 class="gs-h5 gs-mb-3">Headings & Structure</h2>
                        
                        <div class="gs-bg-light gs-p-3 gs-border gs-mb-3">
                            <pre class="gs-mb-0">= Main Heading =
== Section ==
=== Subsection ===
==== Subsubsection ====</pre>
                        </div>
                        
                        <div class="gs-alert gs-alert-info gs-mb-0">
                            <strong>Note:</strong> Use horizontal rules with <code>- - - -</code> to separate sections
                        </div>
                    </div>
                </div>

                <!-- Quick Reference Table -->
                <div class="gs-card">
                    <div class="gs-card-body">
                        <h2 class="gs-h5 gs-mb-3">Quick Reference</h2>
                        
                        <div class="gs-table-responsive">
                            <table class="gs-w-100" style="border-collapse: collapse;">
                                <thead>
                                    <tr class="gs-bg-light">
                                        <th class="gs-p-2 gs-border" style="text-align: left;">Element</th>
                                        <th class="gs-p-2 gs-border" style="text-align: left;">Syntax</th>
                                        <th class="gs-p-2 gs-border" style="text-align: left;">Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Bold</td>
                                        <td class="gs-p-2 gs-border"><code>'''text'''</code></td>
                                        <td class="gs-p-2 gs-border"><code>'''Important'''</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">External Link</td>
                                        <td class="gs-p-2 gs-border"><code>[url|label]</code></td>
                                        <td class="gs-p-2 gs-border"><code>[http://example.com|Example]</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Internal Link</td>
                                        <td class="gs-p-2 gs-border"><code>[[page|label]]</code></td>
                                        <td class="gs-p-2 gs-border"><code>[[homepage|Home]]</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Image</td>
                                        <td class="gs-p-2 gs-border"><code>[[Image:file.jpg|alt]]</code></td>
                                        <td class="gs-p-2 gs-border"><code>[[Image:logo.png|Logo]]</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Unordered List</td>
                                        <td class="gs-p-2 gs-border"><code>* Item</code></td>
                                        <td class="gs-p-2 gs-border"><code>* First item</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Ordered List</td>
                                        <td class="gs-p-2 gs-border"><code># Item</code></td>
                                        <td class="gs-p-2 gs-border"><code># Step 1</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Code</td>
                                        <td class="gs-p-2 gs-border"><code>&lt;code&gt;code&lt;/code&gt;</code></td>
                                        <td class="gs-p-2 gs-border"><code>&lt;code&gt;echo "test"&lt;/code&gt;</code></td>
                                    </tr>
                                    <tr>
                                        <td class="gs-p-2 gs-border">Horizontal Rule</td>
                                        <td class="gs-p-2 gs-border"><code>- - - -</code></td>
                                        <td class="gs-p-2 gs-border"><code>- - - -</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="gs-mt-4 gs-mb-4">
                    <p class="gs-text-muted gs-small">Powered By CyborgWiki</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>