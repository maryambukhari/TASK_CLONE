<?php
// post_task.php - Post a Task
session_start();
if (!isset($_SESSION['user_id'])) { // Only check if user is logged in
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

// Fetch categories
$stmt = $conn->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $budget = $_POST['budget'];
    $location = $_POST['location'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, category_id, title, description, budget, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $category_id, $title, $description, $budget, $location]);
    echo "<script>location.href = 'browse_tasks.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Task</title>
    <style>
        /* Internal CSS - Amazing colors, effects, fonts */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        form { max-width: 600px; margin: auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #28a745; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #218838; }
        @media (max-width: 768px) { form { padding: 10px; } }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Post a New Task</h2>
        <input type="text" name="title" placeholder="Task Title" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="number" name="budget" placeholder="Budget" required>
        <input type="text" name="location" placeholder="Location" required>
        <select name="category_id" required>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Post Task</button>
    </form>
    <button onclick="goToHome()">Back to Home</button>
    <script>
        function goToHome() { location.href = 'index.php'; }
    </script>
</body>
</html>
