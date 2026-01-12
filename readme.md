# CyborgBlogger v20260113

A lightweight, file-based PHP blogging system with wiki-style markup support and integrated media management.

## Features

- **File-Based Storage**: No database required - posts stored as JSON files
- **Wiki Markup**: Support for CyborgWiki syntax including bold text, links, images, lists, tables, and code blocks
- **Topic Organization**: Categorize posts into custom topics
- **Media Manager**: Upload and manage images and files with visual gallery
- **Admin Controls**: Simple password-based authentication system
- **SEO-Friendly**: Automatic sitemap generation for search engines
- **Responsive Design**: Built with custom CSS framework
- **Content Moderation**: Optional word filtering with customizable blocklist
- **Pagination**: Configurable posts per page

## Requirements

- PHP 5.4 or higher
- Web server (Apache/Nginx)
- Write permissions for:
  - `posts/` directory
  - `images/` directory
  - `.admin` file (for session management)

## Installation

1. Clone or download the repository to your web server
2. Ensure the web server has write permissions:
   ```bash
   chmod 755 posts/
   chmod 755 images/
   ```
3. Configure settings in `inc-env.php`:
   - Set `ADMIN_CODE` (default: `admin123`)
   - Customize `AUTHOR`, `BLOGNAME`, and `TAGLINE`
   - Adjust `POSTS_PER_PAGE` and other preferences
4. Access `blogger.php` in your browser

## Configuration

Edit `inc-env.php` to customize:

```php
// Blog Settings
define('AUTHOR', 'Your Name');
define('BLOGNAME', 'Your Blog Name');
define('TAGLINE', 'Your tagline here');
define('LOGO_FILE', 'images/logo.png');

// Security
define('ADMIN_CODE', 'your-secure-password');
define('SANITIZECONTENT', 'FALSE');
define('BLOCKEDWORDSFILE', 'blockedwords.txt'); // or '' to disable

// Performance
define('POSTS_PER_PAGE', 5);
define('EXCERPT_LENGTH', 200);

// Media Manager
define('MEDIA_DIR', 'images/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,webp,svg,pdf,txt,md,json');
```

## Usage

### Admin Access

1. Click "Login" link on the blog
2. Enter your admin code (configured in `inc-env.php`)
3. Access admin controls for posting, editing, and managing content

### Creating Posts

1. Login as admin
2. Write content using CyborgWiki syntax
3. Posts are automatically saved to the current topic
4. View help guide at `help.php` for markup syntax

### CyborgWiki Syntax Quick Reference

Online help available while adding or editing posts.

- **Bold**: `'''text'''`
- **External Links**: `[http://example.com|Link Text]`
- **Internal Links**: `[[page-name|Link Text]]`
- **Images**: `[[Image:filename.jpg|Alt Text]]`
- **Lists**: Start lines with `*` (unordered) or `#` (ordered)
- **Code**: `<code>code here</code>`
- **Headings**: `= H1 =`, `== H2 ==`, `=== H3 ===`
- **Tables**: Use `{|` and `|}` with `!!` for headers and `||` for cells

### Topic Management

1. Admin panel includes "Add Topic" form
2. Create topic with ID (lowercase, alphanumeric), name, and description
3. Posts are organized by topic directories in `posts/`

### Media Management

Access `media.php` to:
- Upload images and files
- View media gallery
- Delete files (admin only)
- Copy URLs for use in posts

### Sitemap Generation

Click "Generate Sitemap" in admin controls to create:
- `sitemap-blog.xml` - Dynamic blog content
- Should be referenced in main `sitemap.xml`

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled for wiki markup rendering
- jQuery 3.7.1 included

## A Plain Text Password?

- It's designed for you to use as you like, if someone can read your password they means they can change your password, they have access to the server anyway.
- We don't recommmend encryption anyway, hashing isn't going to make much difference unless you are using your bank password for your blog which is not good practice.
- If you want real security, consider making a ZOSCII security layer: https://github.com/PrimalNinja/cyborgzoscii

