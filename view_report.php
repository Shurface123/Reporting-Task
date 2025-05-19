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

// Set headers for PDF output
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="report_' . $reportId . '.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo $pdfContent;
exit;
?>
