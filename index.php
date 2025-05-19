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
    <title>STUDENT DATABASE REPORTS SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FF6600;
            --primary-dark: #E65600;
            --secondary: #222222;
            --light: #f8f9fa;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--secondary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            color: var(--primary) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .page-header {
            background-color: white;
            padding: 1.5rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .page-title {
            color: var(--secondary);
            font-weight: 600;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--secondary);
            color: white;
            font-weight: 600;
            border-bottom: none;
            padding: 1rem 1.25rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 500;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 0.2rem rgba(255, 102, 0, 0.25);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            font-weight: 500;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .success-message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .section-heading {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: inline-block;
        }
        
        .report-card-header {
            display: flex;
            align-items: center;
        }
        
        .report-icon {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .footer {
            background-color: var(--secondary);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }
        
        .dashboard-stats {
            background-color: var(--secondary);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            height: 100%;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line me-2"></i>STUDENT DATABASE REPORTS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Dashboard</h1>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle me-2"></i> <strong>SUCCESS!</strong> All reports have been generated successfully. The reports are saved in the StudentID_FullName_db2 folder.
        </div>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="stat-card">
                        <i class="fas fa-file-alt fa-2x mb-3 text-white-50"></i>
                        <div class="stat-value"><?php echo count($all_queries); ?></div>
                        <div class="stat-label">AVAILABLE REPORTS</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="stat-card">
                        <i class="fas fa-chart-bar fa-2x mb-3 text-white-50"></i>
                        <div class="stat-value">
                            <?php 
                            $chartCount = 0;
                            foreach($all_queries as $query) {
                                if(isset($query['chart_type']) && $query['chart_type'] !== 'none') $chartCount++;
                            }
                            echo $chartCount;
                            ?>
                        </div>
                        <div class="stat-label">CHARTS AVAILABLE</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <i class="fas fa-users fa-2x mb-3 text-white-50"></i>
                        <div class="stat-value">
                            <?php 
                            // Try to get student count from database
                            $studentsCount = 0;
                            $countQuery = "SELECT COUNT(*) as count FROM Students";
                            $result = $conn->query($countQuery);
                            if ($result && $result->num_rows > 0) {
                                $studentsCount = $result->fetch_assoc()['count'];
                            }
                            echo $studentsCount;
                            ?>
                        </div>
                        <div class="stat-label">STUDENTS</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate All Reports Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-file-export me-2"></i> GENERATE ALL REPORTS
            </div>
            <div class="card-body">
                <p>Click the button below to generate all reports at once. This will create PDF reports with charts for all the queries.</p>
                <form method="post" action="generate_report.php">
                    <button type="submit" name="generate_all" class="btn btn-primary">
                        <i class="fas fa-cogs me-2"></i> GENERATE ALL REPORTS
                    </button>
                </form>
            </div>
        </div>

        <h2 class="section-heading">AVAILABLE REPORTS</h2>
        
        <div class="row">
            <?php foreach ($all_queries as $query): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header report-card-header">
                        <?php 
                        $icon = 'fa-table-list';
                        if (isset($query['chart_type'])) {
                            switch($query['chart_type']) {
                                case 'bar': $icon = 'fa-chart-column'; break;
                                case 'pie': $icon = 'fa-chart-pie'; break;
                                case 'line': $icon = 'fa-chart-line'; break;
                            }
                        } 
                        ?>
                        <i class="fas <?php echo $icon; ?> fa-fw report-icon"></i>
                        <span>REPORT <?php echo $query['id']; ?>: <?php echo $query['title']; ?></span>
                    </div>
                    <div class="card-body">
                        <p><?php echo $query['description']; ?></p>
                        <a href="view_report.php?id=<?php echo $query['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i> VIEW REPORT
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center">
                <p>© <?php echo date('Y');  ?> STUDENT DATABASE REPORTS SYSTEM</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
