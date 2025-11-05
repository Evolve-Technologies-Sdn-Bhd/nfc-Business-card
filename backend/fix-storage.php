<?php
// fix-storage-v2.php - Enhanced version for Windows

$targetFolder = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public';
$linkFolder = __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'storage';

echo "Target folder: " . $targetFolder . "\n";
echo "Link folder: " . $linkFolder . "\n\n";

// Function to recursively delete directory
function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

// Check current status
if (file_exists($linkFolder)) {
    if (is_link($linkFolder)) {
        echo "Found existing symlink. Removing...\n";
        unlink($linkFolder);
    } else if (is_dir($linkFolder)) {
        echo "Found existing directory. Removing...\n";
        if (deleteDirectory($linkFolder)) {
            echo "Directory removed successfully.\n";
        } else {
            echo "Failed to remove directory. Please delete it manually.\n";
            exit(1);
        }
    } else {
        echo "Found existing file. Removing...\n";
        unlink($linkFolder);
    }
}

// Ensure target directory exists
if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0755, true);
    echo "Created target directory.\n";
}

echo "\nAttempting to create storage link...\n";

// On Windows, we need to use the correct command
if (PHP_OS_FAMILY === 'Windows') {
    // Clean paths for Windows
    $targetFolderWindows = str_replace('/', '\\', $targetFolder);
    $linkFolderWindows = str_replace('/', '\\', $linkFolder);

    // Try mklink command
    $command = sprintf('mklink /D "%s" "%s" 2>&1', $linkFolderWindows, $targetFolderWindows);
    echo "Running: " . $command . "\n";

    $output = [];
    $return = 0;
    exec($command, $output, $return);

    echo "Command output: " . implode("\n", $output) . "\n";

    if ($return === 0) {
        echo "\nSuccess! Storage link created.\n";
    } else {
        echo "\nFailed to create symlink. Return code: " . $return . "\n";

        // Try PowerShell as alternative
        echo "\nTrying PowerShell method...\n";
        $psCommand = sprintf(
            'powershell -Command "New-Item -ItemType SymbolicLink -Path \'%s\' -Target \'%s\'"',
            $linkFolderWindows,
            $targetFolderWindows
        );

        exec($psCommand, $output, $return);

        if ($return === 0) {
            echo "Success with PowerShell!\n";
        } else {
            echo "PowerShell also failed.\n";
            echo "\nIMPORTANT: You need to run this script as Administrator!\n";
            echo "Right-click on PowerShell/Command Prompt and select 'Run as Administrator'\n";
        }
    }
} else {
    // Unix/Linux/Mac
    if (symlink($targetFolder, $linkFolder)) {
        echo "Storage link created successfully!\n";
    } else {
        echo "Failed to create storage link.\n";
    }
}

// Verify the result
echo "\nVerifying...\n";
if (file_exists($linkFolder)) {
    if (is_link($linkFolder)) {
        echo "✓ Storage link exists and is a valid symlink!\n";
    } else {
        echo "✗ Storage folder exists but is NOT a symlink.\n";
    }
} else {
    echo "✗ Storage link does not exist.\n";
}

// Test if we can access files through the link
$testFile = $targetFolder . DIRECTORY_SEPARATOR . 'test-' . time() . '.txt';
file_put_contents($testFile, 'test');

if (file_exists($linkFolder . DIRECTORY_SEPARATOR . basename($testFile))) {
    echo "✓ Files are accessible through the storage link!\n";
    unlink($testFile);
} else {
    echo "✗ Files are NOT accessible through the storage link.\n";
    if (file_exists($testFile)) {
        unlink($testFile);
    }
}
