<?php
$host = 'localhost';
$db = 'extension_packager';
$user = 'root';
$pass = '';

// Create a PostgreSQL connection
$conn = pg_connect("host=$host dbname=$db user=$user password=$pass");

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Handle form submission for adding paths
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_path'])) {
    // Insert file paths into the database
    $extension_name = $_POST['extension_name'];
    $opencart_version = $_POST['opencart_version'];
    $file_paths = $_POST['file_path'];
    $zip_name = $_POST['zip_name'];

    // Clear existing paths for this extension and version before adding new ones
    $query = "DELETE FROM file_paths WHERE extension_name=$1 AND opencart_version=$2";
    $result = pg_query_params($conn, $query, [$extension_name, $opencart_version]);

    foreach ($file_paths as $file_path) {
        $query = "INSERT INTO file_paths (extension_name, opencart_version, file_path, zip_name) VALUES ($1, $2, $3, $4)";
        $result = pg_query_params($conn, $query, [$extension_name, $opencart_version, $file_path, $zip_name]);
        if (!$result) {
            die("Error in SQL query: " . pg_last_error());
        }
    }
}

// Handle packaging
if (isset($_GET['package'])) {
    // Fetch all file paths for the specified extension and version
    $extension_name = $_GET['extension_name'];
    $opencart_version = $_GET['opencart_version'];
    $zip_name = $_GET['zip_name'] ?: "{$extension_name}_{$opencart_version}.zip"; // Default zip name if not provided

    $result = pg_query_params($conn, "SELECT file_path FROM file_paths WHERE extension_name=$1 AND opencart_version=$2", [$extension_name, $opencart_version]);

    // Prepare upload folder
    $upload_folder = "All-Extensions/{$extension_name}" . DIRECTORY_SEPARATOR . "{$opencart_version}" . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR;
    $zip_folder = "All-Extensions/{$extension_name}" . DIRECTORY_SEPARATOR . "{$opencart_version}" . DIRECTORY_SEPARATOR;
    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    // Create a new zip archive
    $zip = new ZipArchive();
    if ($zip->open("{$zip_folder}{$zip_name}", ZipArchive::CREATE) !== TRUE) {
        exit("Cannot open <$zip_name>\n");
    }

    // Base path for maintaining the directory structure
    $base_path = 'C:/laragon/www/dev-oc3'; // Adjust this base path as needed
    $base_path_2 = 'C:\laragon\www\dev-oc3'; // Adjust this base path as needed

    while ($row = pg_fetch_assoc($result)) {
        $file_path = $row['file_path'];

        // Get relative path based on the base path
        $relative_path = str_replace([$base_path, $base_path_2], '', $file_path);
        $upload_path = $upload_folder . ltrim($relative_path, '/');

        // Create necessary directories
        if (!is_dir(dirname($upload_path))) {
            mkdir(dirname($upload_path), 0777, true);
        }

        // Check if the path is a file or a directory
        if (is_file($file_path)) {
            // Only copy if the file exists
            if (copy($file_path, $upload_path)) {
                // Add file to zip
                $relative_path = 'upload\\'.ltrim($relative_path, '\\');
                $zip->addFile($upload_path, $relative_path);
            } else {
                echo "Failed to copy file: {$file_path} to {$upload_path}\n";
            }
        } elseif (is_dir($file_path)) {
            // Get all files from the directory
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file_path));
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    // Get the relative path for the upload directory
                    $relative_file_path = str_replace([$base_path, $base_path_2], '', $file);
                    $upload_path = $upload_folder . ltrim($relative_file_path, '/');

                    // Create necessary directories
                    if (!is_dir(dirname($upload_path))) {
                        mkdir(dirname($upload_path), 0777, true);
                    }

                    // Copy the file to the upload folder
                    if (copy($file, $upload_path)) {
                        // Add file to zip using its real path
                        $relative_file_path = 'upload\\'.ltrim($relative_file_path, '\\');
                        $zip->addFile($upload_path, $relative_file_path);
                    } else {
                        echo "Failed to copy file: {$file} to {$upload_path}\n";
                    }
                }
            }
        }
    }

    $zip->close();
    echo "<div class=\"alert alert-success\">Files packaged successfully into {$zip_name}.</div>";
}

// Clean up file paths on page load
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['clear'])) {
    $query = "DELETE FROM file_paths WHERE extension_name=$1 AND opencart_version=$2";
    $result = pg_query_params($conn, $query, [$_GET['extension_name'], $_GET['opencart_version']]);
}

// Fetch existing paths for editing
$existing_paths = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['extension_name'], $_GET['opencart_version'])) {
    $extension_name = $_GET['extension_name'];
    $opencart_version = $_GET['opencart_version'];
    $result = pg_query_params($conn, "SELECT * FROM file_paths WHERE extension_name=$1 AND opencart_version=$2", [$extension_name, $opencart_version]);
    while ($row = pg_fetch_assoc($result)) {
        $existing_paths[] = $row;
    }
}

// Fetch all extensions for the list
$extensions = [];
$extension_result = pg_query($conn, "SELECT DISTINCT extension_name, opencart_version, zip_name FROM file_paths");
while ($row = pg_fetch_assoc($extension_result)) {
    $extensions[] = $row;
}

pg_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Extension Packager</title>
    <script>
        function addPathField() {
            const pathContainer = document.getElementById('pathContainer');
            const newField = document.createElement('div');
            newField.className = 'form-group row';
            newField.innerHTML = `
                <div class="col">
                    <input type="text" name="file_path[]" class="form-control" placeholder="File/Folder Path" required>
                </div>
                <div class="col">
                    <button type="button" class="btn btn-danger" onclick="removePathField(this)">Remove</button>
                </div>
            `;
            pathContainer.appendChild(newField);
        }

        function removePathField(button) {
            button.parentElement.parentElement.remove();
        }
    </script>
</head>
<body class="container mt-5">
    <h2>Extension Packager</h2>

    <!-- List of existing extensions -->
    <h3>Existing Extensions</h3>
    <ul class="list-group mb-4">
        <?php foreach ($extensions as $ext): ?>
            <li class="list-group-item">
                <a href="?extension_name=<?= urlencode($ext['extension_name']) ?>&opencart_version=<?= urlencode($ext['opencart_version']) ?>&zip_name=<?= urlencode($ext['zip_name']) ?>">
                    <?= htmlspecialchars($ext['extension_name']) ?> (Version: <?= htmlspecialchars($ext['opencart_version']) ?>)
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <form method="POST" class="mb-4">
        <div class="form-group">
            <label for="extension_name">Extension Name</label>
            <input type="text" name="extension_name" class="form-control" value="<?= isset($_GET['extension_name']) ? htmlspecialchars($_GET['extension_name']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="opencart_version">OpenCart Version</label>
            <input type="text" name="opencart_version" class="form-control" value="<?= isset($_GET['opencart_version']) ? htmlspecialchars($_GET['opencart_version']) : '' ?>" required>
        </div>

        <div id="pathContainer" class="mb-3">
            <?php if (!empty($existing_paths)): ?>
                <?php foreach ($existing_paths as $path): ?>
                    <div class="form-group row">
                        <div class="col">
                            <input type="text" name="file_path[]" class="form-control" placeholder="File/Folder Path" value="<?= htmlspecialchars($path['file_path']) ?>" required>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-danger" onclick="removePathField(this)">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="form-group row">
                    <div class="col">
                        <input type="text" name="file_path[]" class="form-control" placeholder="File/Folder Path" required>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-danger" onclick="removePathField(this)">Remove</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-secondary mb-3" onclick="addPathField()">Add Another Path</button>

        <div class="form-group">
            <label for="zip_name">Zip File Name</label>
            <input type="text" name="zip_name" class="form-control" value="<?= isset($_GET['zip_name']) ? htmlspecialchars($_GET['zip_name']) : '' ?>" placeholder="Zip File Name" required>
        </div>
        <button type="submit" name="add_path" class="btn btn-primary">Save Paths</button>
    </form>

    <form method="GET" class="mb-4">
        <h3>Package Extension</h3>
        <input type="hidden" name="extension_name" value="<?= isset($_GET['extension_name']) ? htmlspecialchars($_GET['extension_name']) : '' ?>">
        <input type="hidden" name="opencart_version" value="<?= isset($_GET['opencart_version']) ? htmlspecialchars($_GET['opencart_version']) : '' ?>">
        <div class="form-group">
            <label for="zip_name">Zip File Name</label>
            <input type="text" name="zip_name" class="form-control" value="<?= isset($_GET['zip_name']) ? htmlspecialchars($_GET['zip_name']) : '' ?>" placeholder="Zip File Name" required>
        </div>
        <button type="submit" name="package" class="btn btn-success">Package</button>
    </form>

    <form method="GET" class="mb-4">
        <h3>Clear Existing Paths</h3>
        <input type="hidden" name="extension_name" value="<?= isset($_GET['extension_name']) ? htmlspecialchars($_GET['extension_name']) : '' ?>">
        <input type="hidden" name="opencart_version" value="<?= isset($_GET['opencart_version']) ? htmlspecialchars($_GET['opencart_version']) : '' ?>">
        <button type="submit" name="clear" class="btn btn-danger">Clear Paths</button>
    </form>
</body>
</html>
