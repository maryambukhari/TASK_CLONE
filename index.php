<?php
// index.php - Homepage
session_start();
include 'db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch popular categories
$stmt = $conn->prepare("SELECT * FROM categories LIMIT 5");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch top freelancers
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'freelancer' ORDER BY rating DESC LIMIT 5");
$stmt->execute();
$freelancers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskRabbit Clone - Homepage</title>
    <style>
        /* Internal CSS - Amazing colors, effects, fonts */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 0; }
        header { background: #007bff; color: white; padding: 20px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; }
        .category, .freelancer { background: #e9f7ff; border: 1px solid #007bff; border-radius: 8px; padding: 15px; margin: 10px; transition: transform 0.3s; }
        .category:hover, .freelancer:hover { transform: scale(1.05); box-shadow: 0 6px 12px rgba(0,123,255,0.2); }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #218838; }
        @media (max-width: 768px) { .container { padding: 10px; } }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to TaskRabbit Clone</h1>
        <?php if ($isLoggedIn): ?>
            <p>Hello, <?php echo $_SESSION['username']; ?>! <button onclick="logout()">Logout</button></p>
        <?php else: ?>
            <button onclick="goToLogin()">Login</button>
            <button onclick="goToSignup()">Signup</button>
        <?php endif; ?>
    </header>
    <div class="container">
        <div class="section">
            <h2>Popular Services</h2>
            <?php foreach ($categories as $cat): ?>
                <div class="category">
                    <h3><?php echo $cat['name']; ?></h3>
                    <p><?php echo $cat['description']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="section">
            <h2>Top Freelancers</h2>
            <?php foreach ($freelancers as $fl): ?>
                <div class="freelancer">
                    <h3><?php echo $fl['full_name']; ?> (Rating: <?php echo $fl['rating']; ?>)</h3>
                    <p>Skills: <?php echo $fl['skills']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($isLoggedIn): ?>
            <button onclick="goToPostTask()">Post a Task</button>
            <button onclick="goToBrowseTasks()">Browse Tasks</button>
            <button onclick="goToProfile()">Profile</button>
        <?php endif; ?>
    </div>
    <script>
        // Internal JS for redirections
        function goToLogin() { location.href = 'login.php'; }
        function goToSignup() { location.href = 'signup.php'; }
        function logout() { location.href = 'logout.php'; }
        function goToPostTask() { location.href = 'post_task.php'; }
        function goToBrowseTasks() { location.href = 'browse_tasks.php'; }
        function goToProfile() { location.href = 'profile.php'; }
    </script>
</body>
</html>
