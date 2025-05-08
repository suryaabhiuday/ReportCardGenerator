<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch all students
$stmt = $pdo->query("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id");
$students = $stmt->fetchAll();

// Fetch all subjects
$stmt = $pdo->query("SELECT * FROM subjects");
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Report Card Generator</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h3 class="text-center mb-4">Admin Panel</h3>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#students" data-bs-toggle="tab">
                            <i class="bi bi-people"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#subjects" data-bs-toggle="tab">
                            <i class="bi bi-book"></i> Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#marks" data-bs-toggle="tab">
                            <i class="bi bi-pencil-square"></i> Marks
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
                    <!-- Students Tab -->
                    <div class="tab-pane fade show active" id="students">
                        <h2>Manage Students</h2>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                        <i class="fas fa-user-plus"></i> Add New Student
                                    </button>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                        <i class="fas fa-book"></i> Add New Subject
                                    </button>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addMarksModal">
                                        <i class="fas fa-chart-bar"></i> Add Marks
                                    </button>
                                    <a href="import/import.php" class="btn btn-warning">
                                        <i class="fas fa-file-import"></i> Import Data
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Roll Number</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                                        <td><?php echo htmlspecialchars($student['section']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editStudent(<?php echo $student['id']; ?>)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Subjects Tab -->
                    <div class="tab-pane fade" id="subjects">
                        <h2>Manage Subjects</h2>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                        <i class="fas fa-user-plus"></i> Add New Student
                                    </button>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                        <i class="fas fa-book"></i> Add New Subject
                                    </button>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addMarksModal">
                                        <i class="fas fa-chart-bar"></i> Add Marks
                                    </button>
                                    <a href="import/import.php" class="btn btn-warning">
                                        <i class="fas fa-file-import"></i> Import Data
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editSubject(<?php echo $subject['id']; ?>)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSubject(<?php echo $subject['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Marks Tab -->
                    <div class="tab-pane fade" id="marks">
                        <h2>Manage Marks</h2>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                        <i class="fas fa-user-plus"></i> Add New Student
                                    </button>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                        <i class="fas fa-book"></i> Add New Subject
                                    </button>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addMarksModal">
                                        <i class="fas fa-chart-bar"></i> Add Marks
                                    </button>
                                    <a href="import/import.php" class="btn btn-warning">
                                        <i class="fas fa-file-import"></i> Import Data
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Semester</th>
                                        <th>Marks Obtained</th>
                                        <th>Total Marks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="marksTableBody">
                                    <!-- Marks will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStudentForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roll Number</label>
                            <input type="text" class="form-control" name="roll_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control" name="class" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveStudent()">Save Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addSubjectForm">
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" name="subject_code" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveSubject()">Save Subject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Marks Modal -->
    <div class="modal fade" id="addMarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Marks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMarksForm">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select class="form-control" name="student_id" required>
                                <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name'] . ' (' . $student['roll_number'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-control" name="subject_id" required>
                                <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="number" class="form-control" name="semester" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Marks Obtained</label>
                            <input type="number" step="0.01" class="form-control" name="marks_obtained" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Marks</label>
                            <input type="number" step="0.01" class="form-control" name="total_marks" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Exam Date</label>
                            <input type="date" class="form-control" name="exam_date" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveMarks()">Save Marks</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to save student
        function saveStudent() {
            const form = document.getElementById('addStudentForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            fetch('api/student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Student saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (result.error || 'Failed to save student'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving student');
            });
        }

        // Function to edit student
        function editStudent(id) {
            fetch(`api/student.php?id=${id}`)
                .then(response => response.json())
                .then(student => {
                    const form = document.getElementById('addStudentForm');
                    form.querySelector('[name="username"]').value = student.username;
                    form.querySelector('[name="full_name"]').value = student.full_name;
                    form.querySelector('[name="roll_number"]').value = student.roll_number;
                    form.querySelector('[name="class"]').value = student.class;
                    form.querySelector('[name="section"]').value = student.section;
                    
                    // Add hidden input for student ID
                    let idInput = form.querySelector('[name="id"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    
                    // Change modal title
                    document.querySelector('#addStudentModal .modal-title').textContent = 'Edit Student';
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('addStudentModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading student data');
                });
        }

        // Function to delete student
        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                fetch('api/student.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Student deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to delete student'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting student');
                });
            }
        }

        // Reset form when modal is closed
        document.getElementById('addStudentModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('addStudentForm');
            form.reset();
            form.querySelector('[name="id"]')?.remove();
            document.querySelector('#addStudentModal .modal-title').textContent = 'Add New Student';
        });

        // Function to save subject
        function saveSubject() {
            const form = document.getElementById('addSubjectForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            fetch('api/subject.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Subject saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (result.error || 'Failed to save subject'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving subject');
            });
        }

        // Function to edit subject
        function editSubject(id) {
            fetch(`api/subject.php?id=${id}`)
                .then(response => response.json())
                .then(subject => {
                    const form = document.getElementById('addSubjectForm');
                    form.querySelector('[name="subject_name"]').value = subject.subject_name;
                    form.querySelector('[name="subject_code"]').value = subject.subject_code;
                    
                    // Add hidden input for subject ID
                    let idInput = form.querySelector('[name="id"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    
                    // Change modal title
                    document.querySelector('#addSubjectModal .modal-title').textContent = 'Edit Subject';
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('addSubjectModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading subject data');
                });
        }

        // Function to delete subject
        function deleteSubject(id) {
            if (confirm('Are you sure you want to delete this subject?')) {
                fetch('api/subject.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Subject deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to delete subject'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting subject');
                });
            }
        }

        // Reset form when subject modal is closed
        document.getElementById('addSubjectModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('addSubjectForm');
            form.reset();
            form.querySelector('[name="id"]')?.remove();
            document.querySelector('#addSubjectModal .modal-title').textContent = 'Add New Subject';
        });

        function saveMarks() {
            const form = document.getElementById('addMarksForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            fetch('api/marks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Marks saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (result.error || 'Failed to save marks'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving marks');
            });
        }

        // Function to edit marks
        function editMarks(id) {
            fetch(`api/marks.php?id=${id}`)
                .then(response => response.json())
                .then(marks => {
                    const form = document.getElementById('addMarksForm');
                    form.querySelector('[name="student_id"]').value = marks.student_id;
                    form.querySelector('[name="subject_id"]').value = marks.subject_id;
                    form.querySelector('[name="semester"]').value = marks.semester;
                    form.querySelector('[name="marks_obtained"]').value = marks.marks_obtained;
                    form.querySelector('[name="total_marks"]').value = marks.total_marks;
                    form.querySelector('[name="exam_date"]').value = marks.exam_date;
                    
                    // Add hidden input for marks ID
                    let idInput = form.querySelector('[name="id"]');
                    if (!idInput) {
                        idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        form.appendChild(idInput);
                    }
                    idInput.value = id;
                    
                    // Change modal title
                    document.querySelector('#addMarksModal .modal-title').textContent = 'Edit Marks';
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('addMarksModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading marks data');
                });
        }

        // Function to delete marks
        function deleteMarks(id) {
            if (confirm('Are you sure you want to delete these marks?')) {
                fetch('api/marks.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Marks deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to delete marks'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting marks');
                });
            }
        }

        // Reset form when marks modal is closed
        document.getElementById('addMarksModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('addMarksForm');
            form.reset();
            form.querySelector('[name="id"]')?.remove();
            document.querySelector('#addMarksModal .modal-title').textContent = 'Add New Marks';
        });

        // Load marks table on page load
        function loadMarksTable() {
            fetch('api/marks.php')
                .then(response => response.json())
                .then(marks => {
                    const tbody = document.getElementById('marksTableBody');
                    tbody.innerHTML = '';
                    
                    marks.forEach(mark => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${mark.student_name}</td>
                            <td>${mark.subject_name}</td>
                            <td>${mark.semester}</td>
                            <td>${mark.marks_obtained}</td>
                            <td>${mark.total_marks}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editMarks(${mark.id})">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMarks(${mark.id})">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading marks data');
                });
        }

        // Load marks table when marks tab is shown
        document.querySelector('a[href="#marks"]').addEventListener('shown.bs.tab', loadMarksTable);
    </script>
</body>
</html> 