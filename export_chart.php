<?php
// Include necessary files
require_once('config.php');
require_once('queries.php');
require_once('generate_chart.php');

// Check if id and chart type are provided
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['type'])) {
    die("Invalid parameters");
}

$reportId = (int)$_GET['id'];
$chartType = $_GET['type'];

// Validate chart type
if (!in_array($chartType, ['bar', 'pie', 'line'])) {
    die("Invalid chart type");
}

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

if (empty($resultData)) {
    die("No data available for this report");
}

// Override the chart type
$originalChartType = isset($queryData['chart_type']) ? $queryData['chart_type'] : 'none';
$queryData['chart_type'] = $chartType;

// For pie charts, try to use sensible defaults if original chart wasn't pie
if ($chartType == 'pie' && $originalChartType != 'pie') {
    // Default labels and data for pie chart
    $firstRow = reset($resultData);
    $keys = array_keys($firstRow);
    
    // For pie charts, we need one label column and one data column
    // Try to find a string column for labels and a numeric column for data
    $labelColumn = null;
    $dataColumn = null;
    
    // First pass: try to find a string column and numeric column
    foreach ($keys as $key) {
        $value = $firstRow[$key];
        if ($labelColumn === null && is_string($value) && !is_numeric($value)) {
            $labelColumn = $key;
        } elseif ($dataColumn === null && is_numeric($value)) {
            $dataColumn = $key;
        }
        
        if ($labelColumn !== null && $dataColumn !== null) {
            break;
        }
    }
    
    // Second pass: if no clear string column, just use the first column for labels
    if ($labelColumn === null) {
        $labelColumn = $keys[0];
    }
    
    // Third pass: if no clear numeric column, use the second column (or first if only one column)
    if ($dataColumn === null) {
        $dataColumn = isset($keys[1]) ? $keys[1] : $keys[0];
    }
    
    $queryData['chart_labels'] = $labelColumn;
    $queryData['chart_data'] = $dataColumn;
}

// For line/bar charts, try to use sensible defaults
if (($chartType == 'line' || $chartType == 'bar') && 
    ($originalChartType != 'line' && $originalChartType != 'bar')) {
    
    $firstRow = reset($resultData);
    $keys = array_keys($firstRow);
    
    // For line/bar charts, we need x and y axis data
    // Try to find a suitable x column (preferably string/date) and y column (numeric)
    $xColumn = null;
    $yColumn = null;
    
    // First pass: try to find a string column for x and numeric column for y
    foreach ($keys as $key) {
        $value = $firstRow[$key];
        if ($xColumn === null && (is_string($value) || strtotime($value))) {
            $xColumn = $key;
        } elseif ($yColumn === null && is_numeric($value)) {
            $yColumn = $key;
        }
        
        if ($xColumn !== null && $yColumn !== null) {
            break;
        }
    }
    
    // Second pass: if no clear columns found, use first column for x and second for y
    if ($xColumn === null) {
        $xColumn = $keys[0];
    }
    
    if ($yColumn === null) {
        $yColumn = isset($keys[1]) ? $keys[1] : $keys[0];
    }
    
    $queryData['chart_x'] = $xColumn;
    $queryData['chart_y'] = $yColumn;
}

// Set a chart title
$queryData['chart_title'] = $queryData['title'] . ' (' . ucfirst($chartType) . ' Chart)';

// Create temporary directory if not exist
$tempDir = __DIR__ . '/temp';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Generate the chart
$chartFilePath = $tempDir . '/chart_' . $reportId . '_' . $chartType;
$chartImage = generateChart($queryData, $resultData, $chartFilePath);

if (!$chartImage) {
    die("Failed to generate chart");
}

// Set headers for PNG output
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="chart_' . $reportId . '_' . $chartType . '.png"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output the chart image
readfile($chartImage);
exit;
?>
