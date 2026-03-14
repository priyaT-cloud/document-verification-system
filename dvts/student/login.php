<?php
require_once '../includes/config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);

    if (empty($student_id) || empty($name)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE student_id = ? AND name = ?");
        mysqli_stmt_bind_param($stmt, "ss", $student_id, $name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['student_name'] = $row['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid Student ID or Name. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login - DVTS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <div class="icon">🎓</div>
        <h2>Student Login</h2>
        <p>Digital Document Verification System</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. 23IABCA120" required value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-success" style="width:100%;">Login as Student</button>
        </form>

        <p style="text-align:center; margin-top:20px; font-size:13px;">
            <a href="../index.php" style="color:#1a237e;">← Back to Home</a>
        </p>
    </div>
</div>
</body>
</html>
