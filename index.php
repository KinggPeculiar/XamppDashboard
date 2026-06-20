<?php
// ==========================================
// 1. BACKEND: PHP API ENDPOINT
// ==========================================
// If the JS frontend requests the API, return the projects as JSON and stop execution.
if (isset($_GET['api']) && $_GET['api'] === 'projects') {
    header('Content-Type: application/json');
    // Prevent the browser from caching the API response so that new
    // folders and index-file changes are picked up immediately.
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    
    $baseDir = __DIR__ . '/../projects'; // The current directory (htdocs)
    
    // System folders and files to ignore in XAMPP
    $ignoredItems = array(
        '.', '..', 'dashboard', 'img', 'webalizer', 'xampp', 'phpmyadmin', 
        'index.php', 'favicon.ico', '.git', '.vscode', '.idea'
    );
    
    $projects = array();

    if (is_dir($baseDir)) {
        $items = scandir($baseDir);
        foreach ($items as $item) {
        // Only include directories that aren't in the ignore list
            if (!in_array(strtolower($item), $ignoredItems) && is_dir($baseDir . DIRECTORY_SEPARATOR . $item)) {

                // ----------------------------------------------------------------
                // NEW: Auto-detect the index file inside each project folder
                // ----------------------------------------------------------------
                // We maintain a list of common entry-point filenames, ordered by
                // priority (most preferred first).  When a new folder is added to
                // htdocs, this code scans it automatically — no manual linking needed.
                // ----------------------------------------------------------------
                $indexFiles = ['index.php', 'index.html', 'index.htm', 'default.php', 'default.html'];
                $detectedIndex = null; // Will hold the filename if found

                // Build the full path to the project folder once
                $projectPath = $baseDir . DIRECTORY_SEPARATOR . $item;

                // Loop through each possible index filename and check if it exists
                // inside this project folder.  We stop at the first match (highest
                // priority) so index.php wins over index.html, etc.
                foreach ($indexFiles as $indexFile) {
                    if (file_exists($projectPath . DIRECTORY_SEPARATOR . $indexFile)) {
                        $detectedIndex = $indexFile;
                        break; // Found the best match — no need to check the rest
                    }
                }

                // ----------------------------------------------------------------
                // Build the URL and description based on what we found
                // ----------------------------------------------------------------
                if ($detectedIndex !== null) {
                    // An index file was found — link directly to it so the user
                    // lands on the actual project page instead of a directory listing.
                    $projectUrl = '../projects/' . rawurlencode($item) . '/' . rawurlencode($detectedIndex);
                    $description = 'Entry: ' . $detectedIndex;
                } else {
                    // No known index file was found — fall back to the folder root.
                    // Apache/Nginx may still serve a directory listing or a 403.
                    $projectUrl = '/' . rawurlencode($item) . '/';
                    $description = 'No index file detected';
                }

                // Add the project to the response array
                $projects[] = array(
                    'name'        => $item,
                    'url'         => $projectUrl,
                    'description' => $description,
                );
            }
        }
    }
    
    // Output JSON and exit so the rest of the HTML doesn't render in the API response
    echo json_encode($projects);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Local Dashboard</title>
    
    <!-- Google Fonts: Oswald -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Section -->
    <header class="glass">
        <h1>Local Development</h1>
        <div class="header-widgets">
            <div class="sync-status" title="Checking htdocs for updates...">
                <div class="sync-dot" id="syncDot"></div>
                <span>Live Sync</span>
            </div>
            <div class="clock-widget">
                <div class="time" id="time">00:00:00</div>
                <div class="date" id="date">Loading date...</div>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="layout">
        
        <!-- Sidebar: XAMPP Utilities -->
        <aside class="glass">
            <h2>XAMPP Tools</h2>
            <ul class="tools-list">
                <li>
                    <a href="/phpmyadmin" target="_blank">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path><path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6z"></path></svg>
                        phpMyAdmin
                    </a>
                </li>
                <li>
                    <a href="/dashboard/phpinfo.php" target="_blank">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        PHP Info
                    </a>
                </li>
                <li>
                    <a href="/dashboard/" target="_blank">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        Original Dashboard
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Search / Filter -->
            <div class="search-container glass">
                <input type="text" id="searchInput" placeholder="Filter projects by name...">
            </div>

            <!-- Active Projects Grid (Dynamically Populated) -->
            <div class="projects-grid" id="projectsGrid">
                <!-- Data will be injected here via JS -->
            </div>
        </main>
    </div>

  <script src="script.js?v=<?php echo filemtime(__DIR__ . '/script.js'); ?>"></script>
</body>
</html>