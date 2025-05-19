<?php
// Include config file
require_once('config.php');

// Check if database needs initialization
$checkQuery = "SHOW TABLES LIKE 'Students'";
$result = $conn->query($checkQuery);
$dbInitialized = ($result && $result->num_rows > 0);

// Initialize the database if not already done
if (!$dbInitialized) {
    initializeDatabase($conn);
}

// Include queries
require_once('queries.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Database Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #0069d9;
        }
        .success-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Student Database Reports</h1>

        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <strong>Success!</strong> All reports have been generated successfully. The reports are saved in the StudentID_FullName_db2 folder.
        </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                Generate All Reports
            </div>
            <div class="card-body">
                <p>Click the button below to generate all reports at once. This will create PDF reports with charts for all the queries.</p>
                <form method="post" action="generate_report.php">
                    <button type="submit" name="generate_all" class="btn btn-primary">Generate All Reports</button>
                </form>
            </div>
        </div>

        <h2 class="my-4">Individual Reports</h2>
        
        <div class="row">
            <?php foreach ($all_queries as $query): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Report <?php echo $query['id']; ?>: <?php echo $query['title']; ?>
                    </div>
                    <div class="card-body">
                        <p><?php echo $query['description']; ?></p>
                        <a href="view_report.php?id=<?php echo $query['id']; ?>" class="btn btn-primary" target="_blank">View Report</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
