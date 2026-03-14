<?php
require_once '../includes/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Handle document upload
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $doc_name  = trim($_POST['doc_name']);
    $doc_type  = trim($_POST['doc_type']);
    $file      = $_FILES['document'];

    $allowed = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (empty($doc_name) || empty($doc_type)) {
        $error = "Please fill all fields.";
    } elseif (!in_array($ext, $allowed)) {
        $error = "Only PDF, JPG, JPEG, PNG files are allowed.";
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $error = "File size must be under 5MB.";
    } else {
        $filename = $student_id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $dest = UPLOAD_DIR . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO documents (student_id, document_name, document_type, file_path) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $student_id, $doc_name, $doc_type, $filename);
            mysqli_stmt_execute($stmt);
            $success = "Document uploaded successfully!";
        } else {
            $error = "Failed to upload file. Check upload folder permissions.";
        }
    }
}

// Get documents
$docs = mysqli_query($conn, "SELECT * FROM documents WHERE student_id = '$student_id' ORDER BY uploaded_at DESC");

// Get recruiter visits
$visits = mysqli_query($conn, "SELECT * FROM recruiter_visits WHERE student_id = '$student_id' ORDER BY visited_at DESC LIMIT 10");

// Stats
$total  = mysqli_num_rows($docs);
mysqli_data_seek($docs, 0);
$pending = $verified = $rejected = 0;
while ($d = mysqli_fetch_assoc($docs)) {
    if ($d['status'] == 'Pending')  $pending++;
    if ($d['status'] == 'Verified') $verified++;
    if ($d['status'] == 'Rejected') $rejected++;
}
mysqli_data_seek($docs, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - DVTS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <h2>🎓 Student Dashboard</h2>
    <div>
        Welcome, <strong><?= htmlspecialchars($student_name) ?></strong> (<?= $student_id ?>)
        &nbsp;|&nbsp; <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="num"><?= $total ?></div>
            <div class="label">Total Documents</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#e65100;"><?= $pending ?></div>
            <div class="label">Pending</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#2e7d32;"><?= $verified ?></div>
            <div class="label">Verified</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#c62828;"><?= $rejected ?></div>
            <div class="label">Rejected</div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card">
        <h3>📤 Upload New Document</h3>
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:15px; align-items:end;">
                <div class="form-group" style="margin:0;">
                    <label>Document Name</label>
                    <input type="text" name="doc_name" placeholder="e.g. 10th Marksheet" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Document Type</label>
                    <select name="doc_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="Marksheet">Marksheet</option>
                        <option value="Certificate">Certificate</option>
                        <option value="ID Proof">ID Proof</option>
                        <option value="Transfer Certificate">Transfer Certificate</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Choose File (PDF/JPG/PNG, max 5MB)</label>
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <h3>📁 My Documents</h3>
        <?php if ($total == 0): ?>
            <div class="alert alert-info">No documents uploaded yet. Upload your first document above.</div>
        <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Document Name</th>
                <th>Type</th>
                <th>Uploaded On</th>
                <th>Status</th>
                <th>Verified By</th>
                <th>Remarks</th>
                <th>View</th>
            </tr>
            <?php $i = 1; while ($doc = mysqli_fetch_assoc($docs)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($doc['document_name']) ?></td>
                <td><?= htmlspecialchars($doc['document_type']) ?></td>
                <td><?= date('d M Y, h:i A', strtotime($doc['uploaded_at'])) ?></td>
                <td><span class="badge badge-<?= strtolower($doc['status']) ?>"><?= $doc['status'] ?></span></td>
                <td><?= $doc['verified_by'] ? htmlspecialchars($doc['verified_by']) : '<em style="color:#aaa;">-</em>' ?></td>
                <td style="font-size:13px; color:#c62828;"><?= $doc['rejection_reason'] ? htmlspecialchars($doc['rejection_reason']) : '' ?></td>
                <td>
                    <a href="<?= UPLOAD_URL . $doc['file_path'] ?>" target="_blank" class="btn btn-secondary btn-sm">View</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Recruiter Visits -->
    <div class="card">
        <h3>🏢 Recruiter Visits to My Profile</h3>
        <?php
        $visit_count = mysqli_num_rows($visits);
        if ($visit_count == 0): ?>
            <div class="alert alert-info">No recruiter has visited your profile yet.</div>
        <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Company Name</th>
                <th>Visited At</th>
            </tr>
            <?php $i = 1; while ($v = mysqli_fetch_assoc($visits)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td>🏢 <?= htmlspecialchars($v['company_name']) ?></td>
                <td><?= date('d M Y, h:i A', strtotime($v['visited_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
