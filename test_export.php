<?php
// Test script to verify chart export functionality
require_once('config.php');
require_once('queries.php');
require_once('generate_chart.php');

// Set error reporting to maximum
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Chart Export Test</h1>";

// Test for each chart type
$chartTypes = ['bar', 'pie', 'line'];

foreach ($chartTypes as $chartType) {
    echo "<h2>Testing $chartType chart export</h2>";
    
    // Get the first report that has data
    $testReport = null;
    foreach ($all_queries as $query) {
        // Execute the query to check if it has data
        $result = $conn->query($query['query']);
        if ($result && $result->num_rows > 0) {
            $testReport = $query;
            break;
        }
    }
    
    if (!$testReport) {
        echo "<p>No report with data found</p>";
        continue;
    }
    
    // Fetch result data
    $result = $conn->query($testReport['query']);
    $resultData = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resultData[] = $row;
        }
    }
    
    // Set chart type and other properties
    $testReport['chart_type'] = $chartType;
    
    // For pie charts, determine label and data columns
    if ($chartType == 'pie') {
        $firstRow = reset($resultData);
        $keys = array_keys($firstRow);
        
        // Try to find string column for labels and numeric column for data
        $labelColumn = null;
        $dataColumn = null;
        
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
        
        // Fallbacks
        if ($labelColumn === null) $labelColumn = $keys[0];
        if ($dataColumn === null) $dataColumn = isset($keys[1]) ? $keys[1] : $keys[0];
        
        $testReport['chart_labels'] = $labelColumn;
        $testReport['chart_data'] = $dataColumn;
    }
    
    // For line/bar charts
    if ($chartType == 'line' || $chartType == 'bar') {
        $firstRow = reset($resultData);
        $keys = array_keys($firstRow);
        
        // Try to find suitable x and y columns
        $xColumn = null;
        $yColumn = null;
        
        foreach ($keys as $key) {
            $value = $firstRow[$key];
            if ($xColumn === null && (is_string($value) || @strtotime($value))) {
                $xColumn = $key;
            } elseif ($yColumn === null && is_numeric($value)) {
                $yColumn = $key;
            }
            
            if ($xColumn !== null && $yColumn !== null) {
                break;
            }
        }
        
        // Fallbacks
        if ($xColumn === null) $xColumn = $keys[0];
        if ($yColumn === null) $yColumn = isset($keys[1]) ? $keys[1] : $keys[0];
        
        $testReport['chart_x'] = $xColumn;
        $testReport['chart_y'] = $yColumn;
    }
    
    // Set chart title
    $testReport['chart_title'] = $testReport['title'] . ' (' . ucfirst($chartType) . ' Chart)';
    
    // Create temp directory if not exists
    $tempDir = __DIR__ . '/temp';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    
    // Generate the chart
    echo "<p>Generating chart with " . count($resultData) . " data points...</p>";
    $chartFilePath = $tempDir . '/test_' . $chartType;
    $chartImage = generateChart($testReport, $resultData, $chartFilePath);
    
    if ($chartImage && file_exists($chartImage)) {
        echo "<p>✅ Success! Chart image generated at: $chartImage</p>";
        echo "<p><img src='temp/" . basename($chartImage) . "' style='max-width: 500px; border: 1px solid #ddd;'></p>";
    } else {
        echo "<p>❌ Failed to generate chart image</p>";
    }
    
    echo "<hr>";
}
?>
