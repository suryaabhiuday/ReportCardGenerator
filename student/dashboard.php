<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

// Fetch student details
$stmt = $pdo->prepare("SELECT s.* FROM students s WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: ../index.php");
    exit();
}

// Fetch marks for the student
$stmt = $pdo->prepare("
    SELECT m.*, s.subject_name, s.subject_code 
    FROM marks m 
    JOIN subjects s ON m.subject_id = s.id 
    WHERE m.student_id = ? 
    ORDER BY m.semester, s.subject_name
");
$stmt->execute([$student['id']]);
$marks = $stmt->fetchAll();

// Group marks by semester
$semester_marks = [];
foreach ($marks as $mark) {
    $semester = $mark['semester'];
    if (!isset($semester_marks[$semester])) {
        $semester_marks[$semester] = [];
    }
    $semester_marks[$semester][] = $mark;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Report Card Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.8);
        }
        .nav-link:hover {
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .semester-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h3 class="text-center mb-4">Student Panel</h3>
                <div class="text-center mb-4">
                    <h5><?php echo htmlspecialchars($student['full_name']); ?></h5>
                    <p class="mb-0">Roll No: <?php echo htmlspecialchars($student['roll_number']); ?></p>
                    <p>Class: <?php echo htmlspecialchars($student['class'] . ' ' . $student['section']); ?></p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#marks" data-bs-toggle="tab">
                            <i class="bi bi-card-list"></i> View Marks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#report-card" data-bs-toggle="tab">
                            <i class="bi bi-file-earmark-text"></i> Download Report Card
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="tab-content">
                    <!-- Marks Tab -->
                    <div class="tab-pane fade show active" id="marks">
                        <h2>Academic Performance</h2>
                        <?php foreach ($semester_marks as $semester => $semester_data): ?>
                        <div class="card semester-card">
                            <div class="card-header">
                                <h4>Semester <?php echo $semester; ?></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Subject Code</th>
                                                <th>Subject Name</th>
                                                <th>Marks Obtained</th>
                                                <th>Total Marks</th>
                                                <th>Percentage</th>
                                                <th>Exam Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($semester_data as $mark): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($mark['subject_code']); ?></td>
                                                <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($mark['marks_obtained']); ?></td>
                                                <td><?php echo htmlspecialchars($mark['total_marks']); ?></td>
                                                <td><?php echo number_format(($mark['marks_obtained'] / $mark['total_marks']) * 100, 2); ?>%</td>
                                                <td><?php echo date('d M Y', strtotime($mark['exam_date'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Report Card Tab -->
                    <div class="tab-pane fade" id="report-card">
                        <h2>Download Report Card</h2>
                        <div class="card">
                            <div class="card-body">
                                <p>Click the button below to download your report card as a PDF.</p>
                                <form action="generate_pdf.php" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Select Semester</label>
                                        <select class="form-control" name="semester" required>
                                            <?php foreach (array_keys($semester_marks) as $semester): ?>
                                            <option value="<?php echo $semester; ?>">Semester <?php echo $semester; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-download"></i> Download Report Card
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 