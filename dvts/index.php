<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DVTS - Digital Document Verification System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <h2>📄 DVTS - Digital Document Verification System</h2>
    <div>
        <a href="index.php">Home</a>
    </div>
</div>

<div class="container">
    <div class="hero">
        <h1>Digital Document Verification & Tracking System</h1>
        <p>A secure, transparent platform for academic document verification</p>
    </div>

    <div style="text-align:center; margin-bottom: 20px;">
        <h2 style="color:#1a237e;">Select Your Role to Continue</h2>
        <p style="color:#666; margin-top:8px;">Choose the portal that matches your role</p>
    </div>

    <div class="role-grid">
        <a href="student/login.php" class="role-card student">
            <div class="icon">🎓</div>
            <h3>Student</h3>
            <p>Upload documents, track verification status, and view recruiter visits</p>
            <br>
            <span class="btn btn-success btn-sm">Student Login →</span>
        </a>

        <a href="faculty/login.php" class="role-card faculty">
            <div class="icon">👨‍🏫</div>
            <h3>Faculty / Admin</h3>
            <p>Verify or reject student documents and manage the verification process</p>
            <br>
            <span class="btn btn-primary btn-sm">Faculty Login →</span>
        </a>

        <a href="recruiter/login.php" class="role-card recruiter">
            <div class="icon">🏢</div>
            <h3>Recruiter</h3>
            <p>View verified student documents and profiles using Student ID</p>
            <br>
            <span class="btn btn-sm" style="background:#6a1b9a;color:white;">Recruiter Login →</span>
        </a>
    </div>

</body>
</html>
