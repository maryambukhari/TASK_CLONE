<?php
// update_task.php - Update Task Status
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$status = isset($_GET['status']) ? trim(strtolower($_GET['status'])) : '';
$user_id = $_SESSION['user_id'];

try {
    // Validate task exists and belongs to the user
    $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo "<script>alert('Invalid task or you are not authorized to update it'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    // Validate status
    $valid_statuses = ['in_progress', 'completed'];
    if (!in_array($status, $valid_statuses)) {
        echo "<script>alert('Invalid status. Please use \"in_progress\" or \"completed\"'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    // Update task status
    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->execute([$status, $task_id]);
    echo "<script>alert('Task status updated successfully'); location.href = 'browse_tasks.php';</script>";
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); location.href = 'browse_tasks.php';</script>";
    exit;
}
?>
