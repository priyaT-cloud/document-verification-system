<?php
require_once '../includes/config.php';

if (isset($_SESSION['faculty_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $hashed = MD5($password);
        $stmt = mysqli_prepare($conn, "SELECT * FROM faculty WHERE email = ? AND password = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $hashed);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['faculty_id']   = $row['id'];
            $_SESSION['faculty_name'] = $row['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Login - DVTS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <div class="icon">👨‍🏫</div>
        <h2>Faculty Login</h2>
        <p>Admin / Verification Portal</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="faculty@college.edu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login as Faculty</button>
        </form>

        <p style="text-align:center; margin-top:20px; font-size:13px;">
            <a href="../index.php" style="color:#1a237e;">← Back to Home</a>
        </p>
    </div>
</div>
</body>
</html>
