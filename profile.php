<?php
// profile.php - Profile Management
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $location = $_POST['location'];
    $skills = isset($_POST['skills']) ? $_POST['skills'] : null;

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, location = ?, skills = ? WHERE id = ?");
    $stmt->execute([$full_name, $location, $skills, $user_id]);
    echo "<script>location.href = 'profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        /* Internal CSS - Amazing colors, effects, fonts */
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f0f4f8, #d9e2ec); color: #333; margin: 0; padding: 20px; }
        form { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #0069d9; }
        @media (max-width: 768px) { form { padding: 10px; } }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Edit Profile</h2>
        <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" placeholder="Full Name">
        <input type="text" name="location" value="<?php echo $user['location']; ?>" placeholder="Location">
        <?php if ($_SESSION['role'] == 'freelancer'): ?>
            <textarea name="skills" placeholder="Skills (comma-separated)"><?php echo $user['skills']; ?></textarea>
        <?php endif; ?>
        <button type="submit">Update</button>
    </form>
    <button onclick="goToHome()">Back to Home</button>
    <script>
        function goToHome() { location.href = 'index.php'; }
    </script>
</body>
</html>
