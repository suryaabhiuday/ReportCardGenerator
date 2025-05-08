<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$importResults = isset($_SESSION['import_results']) ? $_SESSION['import_results'] : null;
$importError = isset($_SESSION['import_error']) ? $_SESSION['import_error'] : null;

// Clear session variables
unset($_SESSION['import_results']);
unset($_SESSION['import_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data - Report Card Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Import Data</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($importError): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($importError); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($importResults): ?>
                            <div class="alert <?php echo $importResults['success'] ? 'alert-success' : 'alert-warning'; ?>">
                                <h4>Import Results</h4>
                                <?php if ($importResults['students']['imported'] > 0): ?>
                                    <p>Students: <?php echo $importResults['students']['imported']; ?> imported successfully</p>
                                <?php endif; ?>
                                <?php if ($importResults['subjects']['imported'] > 0): ?>
                                    <p>Subjects: <?php echo $importResults['subjects']['imported']; ?> imported successfully</p>
                                <?php endif; ?>
                                <?php if ($importResults['marks']['imported'] > 0): ?>
                                    <p>Marks: <?php echo $importResults['marks']['imported']; ?> imported successfully</p>
                                <?php endif; ?>

                                <?php if (!empty($importResults['students']['errors'])): ?>
                                    <div class="mt-3">
                                        <h5>Student Import Errors:</h5>
                                        <ul>
                                            <?php foreach ($importResults['students']['errors'] as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($importResults['subjects']['errors'])): ?>
                                    <div class="mt-3">
                                        <h5>Subject Import Errors:</h5>
                                        <ul>
                                            <?php foreach ($importResults['subjects']['errors'] as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($importResults['marks']['errors'])): ?>
                                    <div class="mt-3">
                                        <h5>Marks Import Errors:</h5>
                                        <ul>
                                            <?php foreach ($importResults['marks']['errors'] as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="process_import.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="excelFile" class="form-label">Select Excel File</label>
                                <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx,.xls" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Data to Import</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="import_students" name="import_students" checked>
                                    <label class="form-check-label" for="import_students">
                                        Import Students
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="import_subjects" name="import_subjects" checked>
                                    <label class="form-check-label" for="import_subjects">
                                        Import Subjects
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="import_marks" name="import_marks" checked>
                                    <label class="form-check-label" for="import_marks">
                                        Import Marks
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <a href="template.php" class="btn btn-info">
                                    <i class="fas fa-download"></i> Download Template
                                </a>
                            </div>

                            <div class="alert alert-info">
                                <h5>Instructions:</h5>
                                <ol>
                                    <li>Download the template file using the button above</li>
                                    <li>Fill in your data following the template format</li>
                                    <li>Select the Excel file you want to import</li>
                                    <li>Choose which data you want to import</li>
                                    <li>Click Import to process your data</li>
                                </ol>
                                <p class="mb-0"><strong>Note:</strong> Make sure your data matches the template format exactly. Any errors will be reported after the import attempt.</p>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-import"></i> Import Data
                                </button>
                                <a href="../dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 