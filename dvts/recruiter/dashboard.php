<?php
require_once '../includes/config.php';

if (!isset($_SESSION['recruiter_company'])) {
    header("Location: login.php");
    exit;
}

$company = $_SESSION['recruiter_company'];
$student_info = null;
$docs = null;
$error = '';
$searched = false;

$search_id = trim($_GET['search'] ?? '');

if ($search_id) {
    $searched = true;
    $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $search_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student_info = mysqli_fetch_assoc($res);

    if ($student_info) {
        // Only show verified docs to recruiters
        $docs = mysqli_query($conn, "SELECT * FROM documents WHERE student_id = '" . mysqli_real_escape_string($conn, $search_id) . "' AND status = 'Verified' ORDER BY uploaded_at DESC");

        // Log the visit (avoid duplicate within 1 hour)
        $check = mysqli_query($conn, "SELECT id FROM recruiter_visits WHERE student_id = '" . mysqli_real_escape_string($conn, $search_id) . "' AND company_name = '" . mysqli_real_escape_string($conn, $company) . "' AND visited_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO recruiter_visits (student_id, company_name) VALUES ('" . mysqli_real_escape_string($conn, $search_id) . "', '" . mysqli_real_escape_string($conn, $company) . "')");
        }
    } else {
        $error = "No student found with ID: " . htmlspecialchars($search_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recruiter Dashboard - DVTS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar" style="background: linear-gradient(135deg, #4a148c, #6a1b9a);">
    <h2>🏢 Recruiter Portal</h2>
    <div>
        Company: <strong><?= htmlspecialchars($company) ?></strong>
        &nbsp;|&nbsp; <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="card">
        <h3>🔍 Search Student Profile</h3>
        <p style="color:#666; font-size:14px; margin-bottom:15px;">
            Enter the student's unique ID to view their verified documents. Only verified documents are accessible.
        </p>
        <form method="GET" style="display:flex; gap:15px; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1;">
                <label>Student ID</label>
                <input type="text" name="search" placeholder="Enter Student ID (e.g. 23IABCA120)" value="<?= htmlspecialchars($search_id) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" style="background:#6a1b9a;">Search</button>
            <?php if ($search_id): ?>
                <a href="dashboard.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($student_info): ?>

    <!-- Student Profile -->
    <div class="card">
        <h3>👤 Student Profile</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <table>
                <tr><td><strong>Student ID</strong></td><td><?= htmlspecialchars($student_info['student_id']) ?></td></tr>
                <tr><td><strong>Name</strong></td><td><?= htmlspecialchars($student_info['name']) ?></td></tr>
                <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($student_info['email']) ?></td></tr>
                <tr><td><strong>Department</strong></td><td><?= htmlspecialchars($student_info['department']) ?></td></tr>
            </table>
        </div>
        <div class="alert alert-success" style="margin-top:15px; display:inline-block;">
            ✅ Verified Student Record — Institution: College
        </div>
    </div>

    <!-- Verified Documents -->
    <div class="card">
        <h3>📄 Verified Documents</h3>
        <?php if (!$docs || mysqli_num_rows($docs) == 0): ?>
            <div class="alert alert-warning">
                ⚠️ This student has no verified documents available yet.
                Documents need to be verified by faculty before they are accessible here.
            </div>
        <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Document Name</th>
                <th>Type</th>
                <th>Verified By</th>
                <th>Verified On</th>
                <th>View</th>
            </tr>
            <?php $i = 1; while ($doc = mysqli_fetch_assoc($docs)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($doc['document_name']) ?></td>
                <td><?= htmlspecialchars($doc['document_type']) ?></td>
                <td><?= htmlspecialchars($doc['verified_by']) ?></td>
                <td><?= date('d M Y', strtotime($doc['verified_at'])) ?></td>
                <td>
                    <a href="<?= UPLOAD_URL . $doc['file_path'] ?>" target="_blank" class="btn btn-success btn-sm">View Document</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>

    <?php endif; ?>

    <?php if (!$searched): ?>
    <div class="card" style="text-align:center; padding:40px; color:#888;">
        <div style="font-size:60px; margin-bottom:15px;">🔍</div>
        <h3 style="color:#6a1b9a;">Enter a Student ID to view their verified profile</h3>
        <p style="margin-top:10px; font-size:14px;">Only students with verified documents will show results.</p>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
