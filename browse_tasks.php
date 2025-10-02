<?php
// browse_tasks.php - Browse Tasks with Search and Filters
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$min_budget = isset($_GET['min_budget']) ? (float)$_GET['min_budget'] : 0;

try {
    // Fetch tasks with search and filters
    $query = "SELECT t.*, c.name as category_name, u.username as client, 
                     (SELECT COUNT(*) FROM applications a WHERE a.task_id = t.id) as application_count 
              FROM tasks t 
              JOIN categories c ON t.category_id = c.id 
              JOIN users u ON t.user_id = u.id 
              WHERE t.title LIKE ? AND (c.id = ? OR ? = 0) AND t.budget >= ?";
    $stmt = $conn->prepare($query);
    $stmt->execute(["%$search%", $category, $category, $min_budget]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch categories for filter
    $stmt = $conn->prepare("SELECT * FROM categories");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); location.href = 'index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Tasks</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .task { background: #e9f7ff; border: 1px solid #007bff; border-radius: 8px; padding: 15px; margin: 10px 0; transition: transform 0.3s; }
        .task:hover { transform: scale(1.02); box-shadow: 0 6px 12px rgba(0,123,255,0.2); }
        form { margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        input, select { padding: 10px; margin: 5px; flex: 1; min-width: 150px; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #0069d9; }
        .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .status { font-weight: bold; color: #dc3545; }
        @media (max-width: 768px) { .container { padding: 10px; } .task { padding: 10px; } form { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Browse Tasks</h2>
        <form method="GET" action="browse_tasks.php">
            <input type="text" name="search" placeholder="Search by title" value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($category == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="min_budget" placeholder="Min Budget" value="<?php echo $min_budget ? number_format($min_budget, 2) : ''; ?>" min="0" step="0.01">
            <button type="submit">Filter</button>
        </form>
        <?php if (count($tasks) > 0): ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                    <p>Budget: $<?php echo number_format($task['budget'], 2); ?> | Location: <?php echo htmlspecialchars($task['location']); ?> | Category: <?php echo htmlspecialchars($task['category_name']); ?></p>
                    <p>Posted by: <?php echo htmlspecialchars($task['client']); ?> | Status: <span class="status"><?php echo ucfirst($task['status']); ?></span></p>
                    <?php if ($role == 'client' && $task['user_id'] == $user_id): ?>
                        <p>Applications: <?php echo $task['application_count']; ?></p>
                    <?php endif; ?>
                    <div class="action-buttons">
                        <?php if ($role == 'freelancer' && $task['status'] == 'pending'): ?>
                            <button onclick="applyToTask(<?php echo $task['id']; ?>)">Apply</button>
                            <button onclick="message(<?php echo $task['user_id']; ?>, <?php echo $task['id']; ?>)">Message Client</button>
                        <?php endif; ?>
                        <?php if ($role == 'client' && $task['user_id'] == $user_id): ?>
                            <button onclick="viewApps(<?php echo $task['id']; ?>)">View Applications</button>
                            <button onclick="updateStatus(<?php echo $task['id']; ?>)">Update Status</button>
                        <?php endif; ?>
                        <?php if ($role == 'freelancer'): ?>
                            <button onclick="viewApps(<?php echo $task['id']; ?>)">View My Application</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tasks found matching your criteria.</p>
        <?php endif; ?>
        <button onclick="goToHome()">Back to Home</button>
    </div>
    <script>
        function applyToTask(taskId) { location.href = 'apply_task.php?task_id=' + taskId; }
        function viewApps(taskId) { location.href = 'view_applications.php?task_id=' + taskId; }
        function message(userId, taskId) { location.href = 'messages.php?to_user_id=' + userId + '&task_id=' + taskId; }
        function updateStatus(taskId) { 
            var status = prompt('Enter new status ("in_progress" or "completed")').toLowerCase().trim();
            if (status === 'in_progress' || status === 'completed') {
                location.href = 'update_task.php?task_id=' + taskId + '&status=' + status;
            } else if (status) {
                alert('Invalid status. Please use "in_progress" or "completed".');
            }
        }
        function goToHome() { location.href = 'index.php'; }
    </script>
</body>
</html>
