<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskify API Backend</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .endpoint {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .method {
            font-weight: bold;
            color: #007bff;
        }
        .url {
            font-family: monospace;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 3px;
            color: #495057;
        }
        .description {
            margin-top: 10px;
            color: #666;
        }
        .status {
            text-align: center;
            padding: 20px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .test-link {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .test-link:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Taskify API Backend</h1>
        
        <div class="status">
            ✅ Server is running successfully!<br>
            <small>Base URL: http://localhost:8000</small>
        </div>

        <h2>Available API Endpoints</h2>

        <div class="endpoint">
            <div class="method">GET</div>
            <div class="url">/get_tasks.php</div>
            <div class="description">Retrieve all tasks</div>
            <a href="get_tasks.php" class="test-link" target="_blank">Test Endpoint</a>
        </div>

        <div class="endpoint">
            <div class="method">POST</div>
            <div class="url">/create_task.php</div>
            <div class="description">Create a new task (requires: title, description, startDate, endDate, startTime, endTime)</div>
        </div>

        <div class="endpoint">
            <div class="method">POST</div>
            <div class="url">/update_task.php</div>
            <div class="description">Update an existing task (requires: id, and any fields to update)</div>
        </div>

        <div class="endpoint">
            <div class="method">POST</div>
            <div class="url">/delete_task.php</div>
            <div class="description">Delete a task (requires: id)</div>
        </div>

        <div class="endpoint">
            <div class="method">POST</div>
            <div class="url">/signup.php</div>
            <div class="description">User registration (requires: name, email, password)</div>
        </div>

        <div class="endpoint">
            <div class="method">POST</div>
            <div class="url">/login.php</div>
            <div class="description">User login (requires: email, password)</div>
        </div>

        <div class="endpoint">
            <div class="method">POST</div>
            <div class="url">/chatbot.php</div>
            <div class="description">Chatbot API endpoint</div>
        </div>

        <h2>Android App Configuration</h2>
        <p>For Android emulator, use: <code>http://10.0.2.2:8000</code></p>
        <p>For physical device, use your computer's IP address: <code>http://YOUR_IP:8000</code></p>

        <h2>Testing</h2>
        <p>You can test the API endpoints using:</p>
        <ul>
            <li>Click the "Test Endpoint" links above</li>
            <li>Use tools like Postman or curl</li>
            <li>Run the test script: <code>php test_backend.php</code></li>
        </ul>

        <h2>Data Storage</h2>
        <p>Tasks are stored in <code>tasks_data.json</code> file for demonstration purposes.</p>
        <p>In production, you should use a proper database like MySQL or PostgreSQL.</p>
    </div>
</body>
</html>
