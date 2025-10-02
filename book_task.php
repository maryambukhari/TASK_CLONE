?php
// book_task.php - Booking and Dummy Payment
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$freelancer_id = isset($_GET['freelancer_id']) ? (int)$_GET['freelancer_id'] : 0;
$user_id = $_SESSION['user_id'];

try {
    // Validate task exists and belongs to the user
    $stmt = $conn->prepare("SELECT id, title FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        echo "<script>alert('Invalid task or you are not authorized to book this task'); location.href = 'browse_tasks.php';</script>";
        exit;
    }

    // Validate freelancer has applied
    $stmt = $conn->prepare("SELECT proposed_rate FROM applications WHERE task_id = ? AND freelancer_id = ?");
    $stmt->execute([$task_id, $freelancer_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        echo "<script>alert('Invalid freelancer or no application found'); location.href = 'view_applications.php?task_id=$task_id';</script>";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $amount = (float)$_POST['amount'];
        try {
            // Dummy payment simulation
            $payment_status = 'completed';
            $stmt = $conn->prepare("INSERT INTO bookings (task_id, freelancer_id, client_id, payment_amount, payment_status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$task_id, $freelancer_id, $user_id, $amount, $payment_status]);

            // Update application to accepted
            $stmt = $conn->prepare("UPDATE applications SET status = 'accepted' WHERE task_id = ? AND freelancer_id = ?");
            $stmt->execute([$task_id, $freelancer_id]);

            // Update task to in_progress
            $stmt = $conn->prepare("UPDATE tasks SET status = 'in_progress' WHERE id = ?");
            $stmt->execute([$task_id]);

            echo "<script>alert('Task booked successfully'); location.href = 'browse_tasks.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Error processing booking: " . addslashes($e->getMessage()) . "'); location.href = 'view_applications.php?task_id=$task_id';</script>";
        }
    }
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
    <title>Book Task</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #218838; }
        @media (max-width: 768px) { .container { padding: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Book Task: <?php echo htmlspecialchars($task['title']); ?></h2>
        <form method="POST">
            <input type="number" name="amount" placeholder="Payment Amount" value="<?php echo number_format($application['proposed_rate'], 2); ?>" min="0" step="0.01" required>
            <button type="submit">Pay (Dummy)</button>
        </form>
        <button onclick="goToBrowse()">Back</button>
    </div>
    <script>
        function goToBrowse() { location.href = 'view_applications.php?task_id=<?php echo $task_id; ?>'; }
    </script>
</body>
</html>
