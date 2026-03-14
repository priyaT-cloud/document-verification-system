<?php
require_once '../includes/config.php';

if (isset($_SESSION['recruiter_company'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company = trim($_POST['company_name']);

    if (empty($company)) {
        $error = "Please enter your company name.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM recruiters WHERE company_name = ?");
        mysqli_stmt_bind_param($stmt, "s", $company);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['recruiter_company'] = $row['company_name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Company not found. Please contact the admin to register your company.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recruiter Login - DVTS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">
        <div class="icon">🏢</div>
        <h2>Recruiter Login</h2>
        <p>Company Verification Portal</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" placeholder="e.g. TCS, Infosys..." required value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; background:#6a1b9a;">Login as Recruiter</button>
        </form>

        <div class="alert alert-info" style="margin-top:20px; font-size:13px;">
            ℹ️ Only registered companies can access this portal. Contact the college admin to register.
        </div>

        <p style="text-align:center; margin-top:15px; font-size:13px;">
            <a href="../index.php" style="color:#1a237e;">← Back to Home</a>
        </p>
    </div>
</div>
</body>
</html>
