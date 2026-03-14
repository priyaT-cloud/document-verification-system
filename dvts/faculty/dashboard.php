<?php
require_once '../includes/config.php';

if (!isset($_SESSION['faculty_id'])) {
    header("Location: login.php");
    exit;
}

$faculty_name = $_SESSION['faculty_name'];
$success = $error = '';

// Handle verify/reject action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $doc_id = intval($_POST['doc_id']);
    $action = $_POST['action'];

    if ($action === 'verify') {
        $stmt = mysqli_prepare($conn, "UPDATE documents SET status='Verified', verified_by=?, verified_at=NOW(), rejection_reason=NULL WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $faculty_name, $doc_id);
        mysqli_stmt_execute($stmt);
        $success = "Document verified successfully.";
    } elseif ($action === 'reject') {
        $reason = trim($_POST['reason']);
        $stmt = mysqli_prepare($conn, "UPDATE documents SET status='Rejected', verified_by=?, verified_at=NOW(), rejection_reason=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $faculty_name, $reason, $doc_id);
        mysqli_stmt_execute($stmt);
        $success = "Document rejected.";
    }
}

// Search
$search_id = trim($_GET['search'] ?? '');
$student_info = null;
$docs = null;

if ($search_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $search_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student_info = mysqli_fetch_assoc($res);

    if ($student_info) {
        $docs = mysqli_query($conn, "SELECT * FROM documents WHERE student_id = '" . mysqli_real_escape_string($conn, $search_id) . "' ORDER BY uploaded_at DESC");
    }
}

// Overall stats
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(status='Pending') as pending,
        SUM(status='Verified') as verified,
        SUM(status='Rejected') as rejected
    FROM documents
"));
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard - DVTS</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .reject-form { display:none; margin-top:10px; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>👨‍🏫 Faculty Dashboard</h2>
    <div>
        Welcome, <strong><?= htmlspecialchars($faculty_name) ?></strong>
        &nbsp;|&nbsp; <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="num"><?= $total_students ?></div>
            <div class="label">Total Students</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#e65100;"><?= $stats['pending'] ?></div>
            <div class="label">Pending Docs</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#2e7d32;"><?= $stats['verified'] ?></div>
            <div class="label">Verified Docs</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#c62828;"><?= $stats['rejected'] ?></div>
            <div class="label">Rejected Docs</div>
        </div>
    </div>

    <!-- Search -->
    <div class="card">
        <h3>🔍 Search Student by ID</h3>
        <form method="GET" style="display:flex; gap:15px; align-items:flex-end;">
            <div class="form-group" style="margin:0; flex:1;">
                <label>Student ID</label>
                <input type="text" name="search" placeholder="e.g. 23IABCA120" value="<?= htmlspecialchars($search_id) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search_id): ?>
                <a href="dashboard.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($search_id && !$student_info): ?>
        <div class="alert alert-danger">No student found with ID: <strong><?= htmlspecialchars($search_id) ?></strong></div>
    <?php endif; ?>

    <?php if ($student_info): ?>
    <!-- Student Info -->
    <div class="card">
        <h3>👤 Student Information</h3>
        <table style="max-width:500px;">
            <tr><td><strong>Student ID</strong></td><td><?= htmlspecialchars($student_info['student_id']) ?></td></tr>
            <tr><td><strong>Name</strong></td><td><?= htmlspecialchars($student_info['name']) ?></td></tr>
            <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($student_info['email']) ?></td></tr>
            <tr><td><strong>Department</strong></td><td><?= htmlspecialchars($student_info['department']) ?></td></tr>
        </table>
        <p style="margin-top:10px;" class="alert alert-success" style="display:inline-block;">✅ This student belongs to this institution.</p>
    </div>

    <!-- Documents to Verify -->
    <div class="card">
        <h3>📂 Documents Uploaded by <?= htmlspecialchars($student_info['name']) ?></h3>
        <?php if (mysqli_num_rows($docs) == 0): ?>
            <div class="alert alert-info">This student has not uploaded any documents yet.</div>
        <?php else: ?>
        <?php while ($doc = mysqli_fetch_assoc($docs)): ?>
        <div style="border:1px solid #eee; border-radius:8px; padding:15px; margin-bottom:15px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><?= htmlspecialchars($doc['document_name']) ?></strong>
                    <span style="color:#888; font-size:13px; margin-left:10px;">[<?= $doc['document_type'] ?>]</span>
                    <br>
                    <small style="color:#999;">Uploaded: <?= date('d M Y, h:i A', strtotime($doc['uploaded_at'])) ?></small>
                    <?php if ($doc['verified_by']): ?>
                    <br><small style="color:#555;">Action by: <?= htmlspecialchars($doc['verified_by']) ?></small>
                    <?php endif; ?>
                    <?php if ($doc['rejection_reason']): ?>
                    <br><small style="color:#c62828;">Reason: <?= htmlspecialchars($doc['rejection_reason']) ?></small>
                    <?php endif; ?>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-<?= strtolower($doc['status']) ?>"><?= $doc['status'] ?></span>
                    <a href="<?= UPLOAD_URL . $doc['file_path'] ?>" target="_blank" class="btn btn-secondary btn-sm">View Doc</a>
                </div>
            </div>

            <?php if ($doc['status'] == 'Pending' || $doc['status'] == 'Rejected'): ?>
            <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                <form method="POST">
                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                    <input type="hidden" name="action" value="verify">
                    <input type="hidden" name="search" value="<?= $search_id ?>">
                    <button type="submit" class="btn btn-success btn-sm">✅ Verify</button>
                </form>

                <button class="btn btn-danger btn-sm" onclick="document.getElementById('reject-<?= $doc['id'] ?>').style.display='block'">❌ Reject</button>
            </div>

            <div id="reject-<?= $doc['id'] ?>" class="reject-form">
                <form method="POST" style="display:flex; gap:10px; margin-top:8px;">
                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="search" value="<?= $search_id ?>">
                    <input type="text" name="reason" placeholder="Reason for rejection" required style="flex:1; padding:8px; border:1px solid #ddd; border-radius:5px;">
                    <button type="submit" class="btn btn-danger btn-sm">Submit Rejection</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('reject-<?= $doc['id'] ?>').style.display='none'">Cancel</button>
                </form>
            </div>
            <?php elseif ($doc['status'] == 'Verified'): ?>
            <div style="margin-top:8px;">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="search" value="<?= $search_id ?>">
                    <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('reject-<?= $doc['id'] ?>').style.display='block'">Revoke & Reject</button>
                </form>
                <div id="reject-<?= $doc['id'] ?>" class="reject-form">
                    <form method="POST" style="display:flex; gap:10px; margin-top:8px;">
                        <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="search" value="<?= $search_id ?>">
                        <input type="text" name="reason" placeholder="Reason for rejection" required style="flex:1; padding:8px; border:1px solid #ddd; border-radius:5px;">
                        <button type="submit" class="btn btn-danger btn-sm">Submit</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('reject-<?= $doc['id'] ?>').style.display='none'">Cancel</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Pending -->
    <?php if (!$search_id): ?>
    <div class="card">
        <h3>⏳ Recent Pending Documents</h3>
        <?php
        $pending_docs = mysqli_query($conn, "
            SELECT d.*, s.name as student_name
            FROM documents d
            JOIN students s ON d.student_id = s.student_id
            WHERE d.status = 'Pending'
            ORDER BY d.uploaded_at DESC
            LIMIT 10
        ");
        if (mysqli_num_rows($pending_docs) == 0): ?>
            <div class="alert alert-success">No pending documents. All caught up! 🎉</div>
        <?php else: ?>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Document</th>
                <th>Type</th>
                <th>Uploaded On</th>
                <th>Action</th>
            </tr>
            <?php while ($pd = mysqli_fetch_assoc($pending_docs)): ?>
            <tr>
                <td><?= $pd['student_id'] ?></td>
                <td><?= htmlspecialchars($pd['student_name']) ?></td>
                <td><?= htmlspecialchars($pd['document_name']) ?></td>
                <td><?= $pd['document_type'] ?></td>
                <td><?= date('d M Y', strtotime($pd['uploaded_at'])) ?></td>
                <td><a href="dashboard.php?search=<?= $pd['student_id'] ?>" class="btn btn-primary btn-sm">Review</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
