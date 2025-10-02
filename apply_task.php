<?php
// apply_task.php - Apply to Task
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'freelancer') {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$freelancer_id = $_SESSION['user_id'];

try {
    // Validate task exists and is pending
    $stmt = $conn->prepare("SELECT id, title FROM tasks WHERE id = ? AND status = 'pending'");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo "<script>alert('Invalid or unavailable task'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    // Check if freelancer already applied
    $stmt = $conn->prepare("SELECT id FROM applications WHERE task_id = ? AND freelancer_id = ?");
    $stmt->execute([$task_id, $freelancer_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<script>alert('You have already applied to this task'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $message = $_POST['message'];
        $proposed_rate = (float)$_POST['proposed_rate'];
        try {
            $stmt = $conn->prepare("INSERT INTO applications (task_id, freelancer_id, message, proposed_rate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$task_id, $freelancer_id, $message, $proposed_rate]);
            echo "<script>alert('Application submitted successfully'); location.href = 'browse_tasks.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error applying to task: " . addslashes($e->getMessage()) . "'); location.href = 'browse_tasks.php';</script>";
        }
    }
} catch (PDOException $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); location.href = 'browse_tasks.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to Task</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #218838; }
        @media (max-width: 768px) { .container { padding: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Apply to Task: <?php echo htmlspecialchars($task['title']); ?></h2>
        <form method="POST">
            <textarea name="message" placeholder="Your Message" required></textarea>
            <input type="number" name="proposed_rate" placeholder="Proposed Rate" min="0" step="0.01" required>
            <button type="submit">Apply</button>
        </form>
        <button onclick="goToBrowse()">Back</button>
    </div>
    <script>
        function goToBrowse() { location.href = 'browse_tasks.php'; }
    </script>
</body>
</html>
