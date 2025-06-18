<?php
// This script creates the necessary directory for logo uploads

$uploadPath = public_path('uploads/logos');

if (!file_exists($uploadPath)) {
    if (mkdir($uploadPath, 0755, true)) {
        echo "Successfully created directory: " . $uploadPath;
    } else {
        echo "Failed to create directory: " . $uploadPath;
        echo "Please manually create the directory with the following commands:";
        echo "\n\nmkdir -p " . $uploadPath;
        echo "\nchmod 755 " . $uploadPath;
    }
} else {
    echo "Directory already exists: " . $uploadPath;
}

echo "\n\nEnsure the web server has write permissions to this directory.";
?> 