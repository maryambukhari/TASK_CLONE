<?php
// view_applications.php - View Applications for a Task
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // Validate task exists
    $stmt = $conn->prepare("SELECT id, title, user_id FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo "<script>alert('Invalid task'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    // Check authorization based on role
    if ($role == 'client' && $task['user_id'] != $user_id) {
        echo "<script>alert('You are not authorized to view applications for this task'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    // Fetch applications
    if ($role == 'client') {
        // Clients see all applications for their task
        $stmt = $conn->prepare("SELECT a.*, u.full_name, u.rating, u.username FROM applications a JOIN users u ON a.freelancer_id = u.id WHERE a.task_id = ?");
        $stmt->execute([$task_id]);
    } else {
        // Freelancers see only their own application for the task
        $stmt = $conn->prepare("SELECT a.*, u.full_name, u.rating, u.username FROM applications a JOIN users u ON a.freelancer_id = u.id WHERE a.task_id = ? AND a.freelancer_id = ?");
        $stmt->execute([$task_id, $user_id]);
    }
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); location.href = 'browse_tasks.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .app { background: #e9f7ff; border: 1px solid #007bff; padding: 15px; margin: 10px 0; border-radius: 8px; transition: transform 0.3s; }
        .app:hover { transform: scale(1.02); box-shadow: 0 6px 12px rgba(0,123,255,0.2); }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #218838; }
        @media (max-width: 768px) { .container { padding: 10px; } .app { padding: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Applications for Task: <?php echo htmlspecialchars($task['title']); ?></h2>
        <?php if (count($applications) > 0): ?>
            <?php foreach ($applications as $app): ?>
                <div class="app">
                    <h3><?php echo htmlspecialchars($app['full_name'] ?: $app['username']); ?> (Rating: <?php echo number_format($app['rating'], 2); ?>)</h3>
                    <p>Message: <?php echo htmlspecialchars($app['message']); ?></p>
                    <p>Proposed Rate: $<?php echo number_format($app['proposed_rate'], 2); ?></p>
                    <?php if ($role == 'client' && $task['user_id'] == $user_id): ?>
                        <button onclick="book(<?php echo $app['freelancer_id']; ?>)">Book</button>
                        <button onclick="message(<?php echo $app['freelancer_id']; ?>)">Message</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No applications found for this task.</p>
        <?php endif; ?>
        <button onclick="goToBrowse()">Back to Tasks</button>
    </div>
    <script>
        function book(freelancerId) { location.href = 'book_task.php?task_id=<?php echo $task_id; ?>&freelancer_id=' + freelancerId; }
        function message(freelancerId) { location.href = 'messages.php?to_user_id=' + freelancerId + '&task_id=<?php echo $task_id; ?>'; }
        function goToBrowse() { location.href = 'browse_tasks.php'; }
    </script>
</body>
</html>
