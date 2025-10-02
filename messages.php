<?php
// messages.php - Messaging System
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$to_user_id = isset($_GET['to_user_id']) ? (int)$_GET['to_user_id'] : null;
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : null;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    // Handle message sending
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $to_user_id) {
        $message = trim($_POST['message']);
        if (!empty($message)) {
            $stmt = $conn->prepare("INSERT INTO messages (from_user_id, to_user_id, task_id, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $to_user_id, $task_id, $message]);
            echo "<script>location.href = 'messages.php?to_user_id=$to_user_id&task_id=$task_id';</script>";
            exit;
        } else {
            echo "<script>alert('Message cannot be empty'); location.href = 'messages.php?to_user_id=$to_user_id&task_id=$task_id';</script>";
            exit;
        }
    }

    // Fetch messages if to_user_id is set
    $messages = [];
    $chat_with = null;
    if ($to_user_id) {
        $stmt = $conn->prepare("SELECT m.*, u.username as from_user FROM messages m JOIN users u ON m.from_user_id = u.id WHERE ((m.from_user_id = ? AND m.to_user_id = ?) OR (m.from_user_id = ? AND m.to_user_id = ?)) AND (m.task_id = ? OR ? IS NULL) ORDER BY m.sent_at");
        $stmt->execute([$user_id, $to_user_id, $to_user_id, $user_id, $task_id, $task_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT full_name, username FROM users WHERE id = ?");
        $stmt->execute([$to_user_id]);
        $chat_with = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$chat_with) {
            echo "<script>alert('Invalid user to chat with'); location.href = 'messages.php';</script>";
            exit;
        }
    }

    // Fetch contacts
    $contacts = [];
    if ($role == 'client') {
        $stmt = $conn->prepare("SELECT DISTINCT u.id, u.username, u.full_name, t.id as task_id, t.title FROM users u JOIN applications a ON u.id = a.freelancer_id JOIN tasks t ON a.task_id = t.id WHERE t.user_id = ?");
        $stmt->execute([$user_id]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT DISTINCT u.id, u.username, u.full_name, t.id as task_id, t.title FROM users u JOIN tasks t ON u.id = t.user_id JOIN applications a ON t.id = a.task_id WHERE a.freelancer_id = ?");
        $stmt->execute([$user_id]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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
    <title>Messages</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        .chat { max-width: 800px; margin: auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .message { border-bottom: 1px solid #eee; padding: 10px; }
        .contact { background: #e9f7ff; padding: 10px; margin: 5px; border-radius: 5px; cursor: pointer; transition: transform 0.3s; }
        .contact:hover { transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,123,255,0.2); }
        form { margin-top: 20px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #0069d9; }
        .chat-box { max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 5px; }
        @media (max-width: 768px) { .chat { padding: 10px; } }
    </style>
</head>
<body>
    <div class="chat">
        <h2>Messages</h2>
        <h3>Contacts</h3>
        <?php if ($contacts): ?>
            <?php foreach ($contacts as $contact): ?>
                <div class="contact" onclick="startChat(<?php echo $contact['id']; ?>, <?php echo $contact['task_id']; ?>)">
                    <strong><?php echo htmlspecialchars($contact['full_name'] ?: $contact['username']); ?></strong> - Task: <?php echo htmlspecialchars($contact['title']); ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No contacts available. Apply to tasks (as a freelancer) or wait for applications (as a client).</p>
        <?php endif; ?>
        <?php if ($to_user_id && $chat_with): ?>
            <h3>Chat with <?php echo htmlspecialchars($chat_with['full_name'] ?: $chat_with['username']); ?> (Task: <?php echo htmlspecialchars($task['title'] ?? 'N/A'); ?>)</h3>
            <div class="chat-box">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message">
                            <strong><?php echo htmlspecialchars($msg['from_user']); ?>:</strong> <?php echo htmlspecialchars($msg['message']); ?>
                            <small>(<?php echo $msg['sent_at']; ?>)</small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No messages yet.</p>
                <?php endif; ?>
            </div>
            <form method="POST">
                <textarea name="message" placeholder="Type message" required></textarea>
                <button type="submit">Send</button>
            </form>
        <?php endif; ?>
        <button onclick="goToHome()">Back to Home</button>
    </div>
    <script>
        function startChat(userId, taskId) { location.href = 'messages.php?to_user_id=' + userId + '&task_id=' + taskId; }
        function goToHome() { location.href = 'index.php'; }
    </script>
</body>
</html>
