<?php
echo "=== Simple Backend Test ===\n\n";

echo "1. Checking if server is running...\n";
echo "   Current directory: " . getcwd() . "\n";
echo "   PHP version: " . phpversion() . "\n";
echo "   Server time: " . date('Y-m-d H:i:s') . "\n\n";

echo "2. Checking if backend files exist...\n";
$files = [
    'create_task.php',
    'get_task.php',
    'update_todo.php',
    'delete_task.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file missing\n";
    }
}

echo "\n3. Testing if we can access localhost:8000...\n";
$testUrl = 'http://localhost:8000/simple_test.php';
$response = file_get_contents($testUrl);
if ($response !== false) {
    echo "   ✓ Can access localhost:8000\n";
} else {
    echo "   ✗ Cannot access localhost:8000\n";
    echo "   Make sure to run: php -S localhost:8000\n";
}

echo "\n4. Testing create_task.php directly...\n";
if (file_exists('create_task.php')) {
    echo "   ✓ create_task.php file exists\n";
    
    // Try to include it to check for syntax errors
    try {
        ob_start();
        include 'create_task.php';
        $output = ob_get_clean();
        echo "   ✓ create_task.php can be included\n";
    } catch (Exception $e) {
        echo "   ✗ Error including create_task.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ create_task.php not found\n";
}

echo "\n=== Test Complete ===\n";
echo "If you see any ✗ marks, fix those issues first.\n";
echo "Make sure to run: php -S localhost:8000 in this directory\n";
?>
