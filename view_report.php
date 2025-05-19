<?php
// Include necessary files
require_once('config.php');
require_once('queries.php');
require_once('generate_chart.php');
require_once('generate_pdf.php');

// Check if id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid report ID");
}

$reportId = (int)$_GET['id'];

// Find the query data
$queryData = null;
foreach ($all_queries as $query) {
    if ($query['id'] == $reportId) {
        $queryData = $query;
        break;
    }
}

if (!$queryData) {
    die("Report not found");
}

// Execute the query
$result = $conn->query($queryData['query']);
$resultData = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $resultData[] = $row;
    }
}

// Create temporary directories if not exist
$tempDir = __DIR__ . '/temp';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Check if a specific action is requested
if (isset($_GET['action']) && $_GET['action'] === 'download_pdf') {
    // Generate chart if applicable
    $chartImage = null;
    if (isset($queryData['chart_type']) && $queryData['chart_type'] !== 'none' && !empty($resultData)) {
        $chartFilePath = $tempDir . '/chart_' . $reportId;
        $chartImage = generateChart($queryData, $resultData, $chartFilePath);
    }

    // Generate PDF
    $pdfContent = generatePDFReport($queryData, $resultData, $chartImage);
    $pdfFilename = $tempDir . '/report_' . $reportId . '.pdf';
    file_put_contents($pdfFilename, $pdfContent);

    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="report_' . $reportId . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    echo $pdfContent;
    exit;
} else {
    // Display the interactive report page with charts
    // Generate chart if applicable
    $chartImage = null;
    $hasChartSupport = false;
    
    if (isset($queryData['chart_type']) && $queryData['chart_type'] !== 'none' && !empty($resultData)) {
        $hasChartSupport = true;
        $chartFilePath = $tempDir . '/chart_' . $reportId;
        $chartImage = generateChart($queryData, $resultData, $chartFilePath);
    }
}

// Determine if data can be visualized with charts
$canVisualizeData = !empty($resultData) && count($resultData) > 0;

// Get the first row to check data types
$firstRow = !empty($resultData) ? reset($resultData) : null;
$hasNumericData = false;

if ($firstRow) {
    foreach ($firstRow as $value) {
        if (is_numeric($value)) {
            $hasNumericData = true;
            break;
        }
    }
}

// Only show chart options if we have numeric data
$showChartOptions = $canVisualizeData && $hasNumericData;

// Get the original chart type if any
$originalChartType = isset($queryData['chart_type']) ? $queryData['chart_type'] : 'none';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $queryData['title']; ?> - Report</title>
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
            margin-bottom: 1.5rem;
            overflow: hidden;
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
        
        .section-heading {
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: inline-block;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background-color: var(--secondary);
            color: white;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .chart-container {
            height: 400px;
            margin-bottom: 2rem;
        }

        .chart-options {
            margin-bottom: 2rem;
        }

        .chart-option-btn {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .chart-option-btn input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .chart-option-btn label {
            display: inline-block;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-weight: 500;
            color: #333;
            transition: all 0.2s;
        }

        .chart-option-btn input[type="radio"]:checked + label {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .footer {
            background-color: var(--secondary);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
        }

        .export-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .download-btn {
            display: flex;
            align-items: center;
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 8px 15px;
            border-radius: 5px;
            color: #333;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }

        .download-btn:hover {
            background-color: #f1f1f1;
            text-decoration: none;
        }

        .download-btn .icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .pdf-icon { color: #FF6600; }
        .chart-icon { color: #28a745; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line me-2"></i>Student DB Reports
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title"><?php echo $queryData['title']; ?></h1>
        </div>
    </div>

    <div class="container">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i> Report Information
            </div>
            <div class="card-body">
                <p><strong>Description:</strong> <?php echo $queryData['description']; ?></p>
                <p><strong>SQL Query:</strong></p>
                <pre class="bg-light p-3 border rounded"><code><?php echo htmlspecialchars($queryData['query']); ?></code></pre>
                
                <!-- Export Options -->
                <h5 class="mb-3">Export Options</h5>
                <div class="export-options">
                    <a href="?id=<?php echo $reportId; ?>&action=download_pdf" class="download-btn">
                        <i class="fas fa-file-pdf icon pdf-icon"></i> Download as PDF
                    </a>
                    
                    <?php if ($showChartOptions): ?>
                    <!-- Chart Download Options -->
                    <a href="export_chart.php?id=<?php echo $reportId; ?>&type=bar" class="download-btn" target="_blank">
                        <i class="fas fa-chart-column icon chart-icon"></i> Export Bar Chart
                    </a>
                    
                    <a href="export_chart.php?id=<?php echo $reportId; ?>&type=line" class="download-btn" target="_blank">
                        <i class="fas fa-chart-line icon chart-icon"></i> Export Line Chart
                    </a>
                    
                    <a href="export_chart.php?id=<?php echo $reportId; ?>&type=pie" class="download-btn" target="_blank">
                        <i class="fas fa-chart-pie icon chart-icon"></i> Export Pie Chart
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($showChartOptions): ?>
        <!-- Chart Visualization -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Data Visualization
            </div>
            <div class="card-body">
                <!-- Chart Type Selection -->
                <div class="chart-options">
                    <h5 class="mb-3">Select Chart Type:</h5>
                    <div>
                        <div class="chart-option-btn">
                            <input type="radio" name="chartType" id="chartTypeBar" value="bar" <?php echo ($originalChartType === 'bar') ? 'checked' : ''; ?>>
                            <label for="chartTypeBar"><i class="fas fa-chart-column me-1"></i> Bar Chart</label>
                        </div>
                        <div class="chart-option-btn">
                            <input type="radio" name="chartType" id="chartTypeLine" value="line" <?php echo ($originalChartType === 'line') ? 'checked' : ''; ?>>
                            <label for="chartTypeLine"><i class="fas fa-chart-line me-1"></i> Line Chart</label>
                        </div>
                        <div class="chart-option-btn">
                            <input type="radio" name="chartType" id="chartTypePie" value="pie" <?php echo ($originalChartType === 'pie') ? 'checked' : ''; ?>>
                            <label for="chartTypePie"><i class="fas fa-chart-pie me-1"></i> Pie Chart</label>
                        </div>
                    </div>
                </div>

                <!-- Chart Container -->
                <div class="chart-container">
                    <canvas id="reportChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table me-2"></i> Data Table
            </div>
            <div class="card-body">
                <?php if (!empty($resultData)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <?php foreach (array_keys(reset($resultData)) as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultData as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No data available for this report.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Student Database Reports System</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if ($showChartOptions): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare data from PHP
        const reportData = <?php echo json_encode($resultData); ?>;
        const queryData = <?php echo json_encode($queryData); ?>;
        
        // Chart configuration
        let chartType = '<?php echo $originalChartType !== 'none' ? $originalChartType : 'bar'; ?>';
        let chartCanvas = document.getElementById('reportChart');
        let chartInstance = null;
        
        // Chart data preparation functions
        function prepareBarLineChartData() {
            // Try to determine x and y axis data
            let xKey = queryData.chart_x || Object.keys(reportData[0])[0];
            let yKey = queryData.chart_y || Object.keys(reportData[0]).find(k => typeof reportData[0][k] === 'number') || Object.keys(reportData[0])[1];
            
            const labels = reportData.map(row => row[xKey]);
            const data = reportData.map(row => parseFloat(row[yKey]) || 0);
            
            // Generate random colors for bar chart
            const backgroundColors = reportData.map(() => {
                const r = Math.floor(Math.random() * 200) + 55;
                const g = Math.floor(Math.random() * 200) + 55;
                const b = Math.floor(Math.random() * 200) + 55;
                return `rgba(${r}, ${g}, ${b}, 0.8)`;
            });
            
            return {
                labels,
                datasets: [{
                    label: queryData.chart_title || queryData.title,
                    data,
                    backgroundColor: chartType === 'bar' ? backgroundColors : 'rgba(255, 102, 0, 0.4)',
                    borderColor: 'rgba(255, 102, 0, 1)',
                    borderWidth: 1,
                    tension: 0.3
                }]
            };
        }
        
        function preparePieChartData() {
            // Try to determine label and data for pie chart
            let labelKey = queryData.chart_labels || Object.keys(reportData[0])[0];
            let dataKey = queryData.chart_data || Object.keys(reportData[0]).find(k => typeof reportData[0][k] === 'number') || Object.keys(reportData[0])[1];
            
            const labels = reportData.map(row => row[labelKey]);
            const data = reportData.map(row => parseFloat(row[dataKey]) || 0);
            
            // Generate colors for pie segments
            const backgroundColors = reportData.map((_, i) => {
                const hue = (i * 360 / reportData.length) % 360;
                return `hsl(${hue}, 70%, 60%)`;
            });
            
            return {
                labels,
                datasets: [{
                    data,
                    backgroundColor: backgroundColors,
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            };
        }
        
        // Function to draw the chart
        function drawChart() {
            // Destroy previous chart if exists
            if (chartInstance) {
                chartInstance.destroy();
            }
            
            // Prepare data based on chart type
            let chartData;
            if (chartType === 'pie') {
                chartData = preparePieChartData();
            } else {
                chartData = prepareBarLineChartData();
            }
            
            // Chart options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: queryData.chart_title || queryData.title,
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            };
            
            // Create the chart
            chartInstance = new Chart(chartCanvas, {
                type: chartType,
                data: chartData,
                options: chartOptions
            });
        }
        
        // Initialize chart
        drawChart();
        
        // Handle chart type selection
        document.querySelectorAll('input[name="chartType"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                chartType = this.value;
                drawChart();
            });
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>
