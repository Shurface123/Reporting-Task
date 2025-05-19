<?php
// Include necessary files
require_once('config.php');
require_once('queries.php');
require_once('generate_chart.php');
require_once('generate_pdf.php');

// Create output directory
$outputDir = __DIR__ . '/StudentID_FullName_db2';
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Create subdirectories
$sqlDir = $outputDir . '/sql';
$pdfDir = $outputDir . '/pdf';
$chartDir = $outputDir . '/charts';

if (!file_exists($sqlDir)) {
    mkdir($sqlDir, 0777, true);
}
if (!file_exists($pdfDir)) {
    mkdir($pdfDir, 0777, true);
}
if (!file_exists($chartDir)) {
    mkdir($chartDir, 0777, true);
}

// Initialize database if not already done
initializeDatabase($conn);

// Process a specific report if an ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reportId = (int)$_GET['id'];
    
    // Find the query data
    $queryData = null;
    foreach ($all_queries as $query) {
        if ($query['id'] == $reportId) {
            $queryData = $query;
            break;
        }
    }
    
    if ($queryData) {
        // Execute the query
        $result = $conn->query($queryData['query']);
        $resultData = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultData[] = $row;
            }
        }
        
        // Save SQL query to file
        $sqlFilename = $sqlDir . '/query_' . $reportId . '.sql';
        file_put_contents($sqlFilename, $queryData['query']);
        
        // Generate chart if applicable
        $chartImage = null;
        if (isset($queryData['chart_type']) && $queryData['chart_type'] !== 'none' && !empty($resultData)) {
            $chartFilePath = $chartDir . '/chart_' . $reportId;
            $chartImage = generateChart($queryData, $resultData, $chartFilePath);
        }
        
        // Generate PDF report
        $pdfContent = generatePDFReport($queryData, $resultData, $chartImage);
        $pdfFilename = $pdfDir . '/report_' . $reportId . '.pdf';
        file_put_contents($pdfFilename, $pdfContent);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Report generated successfully',
            'pdf_path' => $pdfFilename
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid report ID'
        ]);
    }
    exit;
}

// Generate all reports
if (isset($_GET['generate_all']) || isset($_POST['generate_all'])) {
    $generatedReports = [];
    
    foreach ($all_queries as $queryData) {
        $reportId = $queryData['id'];
        
        // Execute the query
        $result = $conn->query($queryData['query']);
        $resultData = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultData[] = $row;
            }
        }
        
        // Save SQL query to file
        $sqlFilename = $sqlDir . '/query_' . $reportId . '.sql';
        file_put_contents($sqlFilename, $queryData['query']);
        
        // Generate chart if applicable
        $chartImage = null;
        if (isset($queryData['chart_type']) && $queryData['chart_type'] !== 'none' && !empty($resultData)) {
            $chartFilePath = $chartDir . '/chart_' . $reportId;
            $chartImage = generateChart($queryData, $resultData, $chartFilePath);
        }
        
        // Generate PDF report
        $pdfContent = generatePDFReport($queryData, $resultData, $chartImage);
        $pdfFilename = $pdfDir . '/report_' . $reportId . '.pdf';
        file_put_contents($pdfFilename, $pdfContent);
        
        $generatedReports[] = [
            'id' => $reportId,
            'title' => $queryData['title'],
            'pdf_path' => $pdfFilename
        ];
    }
    
    // Create README file
    $readmeContent = "# Database Report Assignment\n\n";
    $readmeContent .= "This folder contains SQL queries, PDF reports, and charts for the database assignment.\n\n";
    $readmeContent .= "## Reports\n\n";
    
    foreach ($all_queries as $query) {
        $readmeContent .= "- Report " . $query['id'] . ": " . $query['title'] . "\n";
        $readmeContent .= "  - SQL: sql/query_" . $query['id'] . ".sql\n";
        $readmeContent .= "  - PDF: pdf/report_" . $query['id'] . ".pdf\n";
        if (isset($query['chart_type']) && $query['chart_type'] !== 'none') {
            $readmeContent .= "  - Chart: charts/chart_" . $query['id'] . ".png\n";
        }
        $readmeContent .= "\n";
    }
    
    file_put_contents($outputDir . '/README.md', $readmeContent);
    
    // Redirect back to index with success message
    if (!isset($_GET['api'])) {
        header('Location: index.php?success=1');
        exit;
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'All reports generated successfully',
            'reports' => $generatedReports
        ]);
        exit;
    }
}
?>
