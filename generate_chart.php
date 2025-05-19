<?php
// Function to generate charts using Chart.js
function generateChart($queryData, $resultData, $chartFilePath) {
    // Check if chart type is specified and results exist
    if (!isset($queryData['chart_type']) || $queryData['chart_type'] == 'none' || !is_array($resultData) || empty($resultData)) {
        error_log('Invalid chart data: chart_type not set, is none, or resultData is empty/invalid');
        return false;
    }
    
    // Create a unique filename for the chart
    $chartType = $queryData['chart_type'];
    $chartTitle = isset($queryData['chart_title']) ? $queryData['chart_title'] : $queryData['title'];
    
    // Setup data based on chart type
    $labels = [];
    $data = [];
    $backgroundColor = [];
    
    // Generate colors for the chart
    $colors = [
        'rgba(54, 162, 235, 0.8)', 
        'rgba(255, 99, 132, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(199, 199, 199, 0.8)',
        'rgba(83, 102, 255, 0.8)',
        'rgba(40, 159, 64, 0.8)',
        'rgba(210, 199, 199, 0.8)'
    ];
    
    // Prepare data based on chart type
    try {
        switch ($chartType) {
            case 'bar':
                if (!isset($queryData['chart_x']) || !isset($queryData['chart_y'])) {
                    error_log('Missing chart_x or chart_y for bar chart');
                    return false;
                }
                
                foreach ($resultData as $key => $row) {
                    if (!isset($row[$queryData['chart_x']]) || !isset($row[$queryData['chart_y']])) {
                        continue; // Skip this row
                    }
                    $labels[] = $row[$queryData['chart_x']];
                    $data[] = (float) $row[$queryData['chart_y']];
                    $backgroundColor[] = $colors[$key % count($colors)];
                }
                break;
                
            case 'pie':
                if (!isset($queryData['chart_labels']) || !isset($queryData['chart_data'])) {
                    error_log('Missing chart_labels or chart_data for pie chart');
                    return false;
                }
                
                foreach ($resultData as $key => $row) {
                    if (!isset($row[$queryData['chart_labels']]) || !isset($row[$queryData['chart_data']])) {
                        continue; // Skip this row
                    }
                    $labels[] = $row[$queryData['chart_labels']];
                    $data[] = (float) $row[$queryData['chart_data']];
                    $backgroundColor[] = $colors[$key % count($colors)];
                }
                break;
                
            case 'line':
                if (!isset($queryData['chart_x']) || !isset($queryData['chart_y'])) {
                    error_log('Missing chart_x or chart_y for line chart');
                    return false;
                }
                
                foreach ($resultData as $key => $row) {
                    if (!isset($row[$queryData['chart_x']]) || !isset($row[$queryData['chart_y']])) {
                        continue; // Skip this row
                    }
                    $labels[] = $row[$queryData['chart_x']];
                    $data[] = (float) $row[$queryData['chart_y']];
                    $backgroundColor[] = $colors[$key % count($colors)];
                }
                break;
                
            default:
                error_log('Unsupported chart type: ' . $chartType);
                return false;
        }
    } catch (Exception $e) {
        error_log('Error preparing chart data: ' . $e->getMessage());
        return false;
    }
    
    // Check if we have any data to display
    if (empty($labels) || empty($data)) {
        error_log('No valid data to display in chart');
        return false;
    }
    
    // Create HTML to render the chart
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>' . $chartTitle . '</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            body { margin: 0; padding: 0; }
            .chart-container { width: 800px; height: 400px; }
        </style>
    </head>
    <body>
        <div class="chart-container">
            <canvas id="myChart"></canvas>
        </div>
        <script>
            const ctx = document.getElementById("myChart");
            
            const chartData = {
                labels: ' . json_encode($labels) . ',
                datasets: [{
                    label: "' . $chartTitle . '",
                    data: ' . json_encode($data) . ',
                    backgroundColor: ' . json_encode($backgroundColor) . ',
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
            };
            
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "' . $chartTitle . '"
                    }
                }
            };
            
            new Chart(ctx, {
                type: "' . $chartType . '",
                data: chartData,
                options: chartOptions
            });
        </script>
    </body>
    </html>';
    
    // Save HTML to a file
    file_put_contents($chartFilePath . '.html', $html);
    
    // Convert HTML to image using GD library directly
    $chartImagePath = $chartFilePath . '.png';
    
    // Create the chart image
    createChartImage($chartType, $labels, $data, $backgroundColor, $chartTitle, $chartImagePath);
    
    return $chartImagePath;
}

// Directly create chart image without parsing HTML
function createChartImage($chartType, $labels, $data, $backgroundColor, $chartTitle, $pngFile) {
    // Create image
    $width = 800;
    $height = 400;
    $image = imagecreatetruecolor($width, $height);

    // Enable alpha channel
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    // Set colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 200, 200, 200);

    // Fill background
    imagefilledrectangle($image, 0, 0, $width-1, $height-1, $white);

    // Draw chart based on type
    $padding = 50;
    $chartWidth = $width - (2 * $padding);
    $chartHeight = $height - (2 * $padding);
    
    // Convert RGBA colors to GD colors
    $gdColors = [];
    foreach ($backgroundColor as $color) {
        if (preg_match('/rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)/', $color, $matches)) {
            $gdColors[] = imagecolorallocate($image, $matches[1], $matches[2], $matches[3]);
        } else {
            // Default colors if pattern doesn't match
            $gdColors[] = imagecolorallocate($image, 54, 162, 235); // Blue
        }
    }
    
    // If we don't have enough colors, add default ones
    if (count($gdColors) < count($data)) {
        $defaultColors = [
            imagecolorallocate($image, 54, 162, 235),   // Blue
            imagecolorallocate($image, 255, 99, 132),   // Red
            imagecolorallocate($image, 75, 192, 192),   // Green
            imagecolorallocate($image, 255, 206, 86),   // Yellow
            imagecolorallocate($image, 153, 102, 255),  // Purple
            imagecolorallocate($image, 255, 159, 64),   // Orange
            imagecolorallocate($image, 199, 199, 199),  // Gray
            imagecolorallocate($image, 83, 102, 255),   // Blue-Purple
            imagecolorallocate($image, 40, 159, 64),    // Dark Green
            imagecolorallocate($image, 210, 199, 199)   // Light Gray
        ];
        
        for ($i = count($gdColors); $i < count($data); $i++) {
            $gdColors[] = $defaultColors[$i % count($defaultColors)];
        }
    }

    // Draw title
    $titleY = 20;
    imagestring($image, 5, (int)(($width - (strlen($chartTitle) * imagefontwidth(5))) / 2), $titleY, $chartTitle, $black);

    switch ($chartType) {
        case 'bar':
            // Draw bars
            $barCount = count($data);
            if ($barCount === 0) {
                error_log('No data available for bar chart generation');
                return false;
            }
            $barWidth = ($chartWidth / $barCount) * 0.8;
            $barSpacing = ($chartWidth / $barCount) * 0.2;
            $maxValue = empty($data) ? 0 : max($data);
            if ($maxValue <= 0) {
                $maxValue = 1; // Prevent division by zero
            }

            // Draw Y axis
            imageline($image, $padding, $padding, $padding, $height-$padding, $black);
            $ySteps = 5;
            for ($i = 0; $i <= $ySteps; $i++) {
                $y = $height - $padding - ($i * ($chartHeight / $ySteps));
                $value = ($maxValue * $i / $ySteps);
                imagestring($image, 2, 5, $y-7, number_format($value, 1), $black);
                imageline($image, $padding-5, $y, $padding, $y, $black);
            }

            // Draw bars and X axis labels
            for ($i = 0; $i < $barCount; $i++) {
                $x = $padding + ($i * ($chartWidth / $barCount)) + ($barSpacing / 2);
                $barHeight = ($data[$i] / $maxValue) * $chartHeight;
                $y = $height - $padding - $barHeight;
                
                // Draw bar
                imagefilledrectangle(
                    $image,
                    (int)$x,
                    (int)$y,
                    (int)($x + $barWidth),
                    (int)($height - $padding),
                    $gdColors[$i % count($gdColors)]
                );

                // Draw label
                $label = $labels[$i];
                $labelWidth = strlen($label) * imagefontwidth(2);
                imagestring(
                    $image,
                    2,
                    (int)($x + ($barWidth/2) - ($labelWidth/2)),
                    $height-$padding+5,
                    $label,
                    $black
                );
            }
            break;

        case 'pie':
            // Check for data
            if (empty($data) || empty($labels)) {
                error_log('No data available for pie chart generation');
                return false;
            }
            
            // Calculate total for percentages
            $total = array_sum($data);
            if ($total <= 0) {
                $total = 1; // Prevent division by zero
            }
            $centerX = $width / 2;
            $centerY = ($height / 2) + 20;
            $radius = min($chartWidth, $chartHeight) / 3;

            // Draw pie segments
            $start = 0;
            for ($i = 0; $i < count($data); $i++) {
                $angle = ($data[$i] / $total) * 360;
                $end = $start + $angle;

                // Draw segment
                imagefilledarc(
                    $image,
                    (int)$centerX,
                    (int)$centerY,
                    (int)($radius * 2),
                    (int)($radius * 2),
                    (int)$start,
                    (int)$end,
                    $gdColors[$i % count($gdColors)],
                    IMG_ARC_PIE
                );

                // Draw label
                $labelAngle = deg2rad($start + ($angle/2));
                $labelX = $centerX + cos($labelAngle) * ($radius + 30);
                $labelY = $centerY + sin($labelAngle) * ($radius + 30);
                $percent = round(($data[$i] / $total) * 100, 1);
                $label = $labels[$i] . ' (' . $percent . '%)';
                imagestring($image, 2, (int)$labelX, (int)$labelY, $label, $black);

                $start = $end;
            }
            break;

        case 'line':
            // Draw line chart
            $pointCount = count($data);
            if ($pointCount <= 1) {
                error_log('Insufficient data points for line chart generation');
                return false;
            }
            $maxValue = empty($data) ? 0 : max($data);
            if ($maxValue <= 0) {
                $maxValue = 1; // Prevent division by zero
            }
            $xStep = $chartWidth / ($pointCount - 1);

            // Draw Y axis
            imageline($image, $padding, $padding, $padding, $height-$padding, $black);
            $ySteps = 5;
            for ($i = 0; $i <= $ySteps; $i++) {
                $y = $height - $padding - ($i * ($chartHeight / $ySteps));
                $value = ($maxValue * $i / $ySteps);
                imagestring($image, 2, 5, $y-7, number_format($value, 1), $black);
                imageline($image, $padding-5, $y, $padding, $y, $black);
            }

            // Draw X axis
            imageline($image, $padding, $height-$padding, $width-$padding, $height-$padding, $black);

            // Draw line and points
            $points = [];
            for ($i = 0; $i < $pointCount; $i++) {
                $x = $padding + ($i * $xStep);
                $y = $height - $padding - (($data[$i] / $maxValue) * $chartHeight);
                $points[] = ['x' => $x, 'y' => $y];

                // Draw point
                imagefilledellipse($image, (int)$x, (int)$y, 6, 6, $gdColors[0]);

                // Draw label
                $label = $labels[$i];
                $labelWidth = strlen($label) * imagefontwidth(2);
                imagestring(
                    $image,
                    2,
                    (int)($x - ($labelWidth/2)),
                    $height-$padding+5,
                    $label,
                    $black
                );
            }

            // Draw lines between points
            for ($i = 0; $i < $pointCount-1; $i++) {
                imageline(
                    $image,
                    (int)$points[$i]['x'],
                    (int)$points[$i]['y'],
                    (int)$points[$i+1]['x'],
                    (int)$points[$i+1]['y'],
                    $gdColors[0]
                );
            }
            break;
    }

    // Save as PNG
    imagepng($image, $pngFile);
    imagedestroy($image);
    
    return true;
}

// Legacy function kept for backward compatibility
function convertHtmlToPng($htmlFile, $pngFile) {
    // Get chart data from the HTML file
    $html = file_get_contents($htmlFile);
    if (preg_match('/chartData\s*=\s*({[^;]+});/', $html, $matches)) {
        $chartDataJson = $matches[1];
        $chartData = json_decode($chartDataJson, true);
    } else {
        error_log('Failed to extract chart data from HTML');
        return false;
    }

    // Get chart type
    if (preg_match('/type:\s*"([^"]+)"/', $html, $matches)) {
        $chartType = $matches[1];
    } else {
        error_log('Failed to extract chart type from HTML');
        return false;
    }

    // Get chart title
    if (preg_match('/text:\s*"([^"]+)"/', $html, $matches)) {
        $chartTitle = $matches[1];
    } else {
        $chartTitle = '';
    }
    
    // Extract data
    $labels = isset($chartData['labels']) ? $chartData['labels'] : [];
    $data = isset($chartData['datasets'][0]['data']) ? $chartData['datasets'][0]['data'] : [];
    $backgroundColor = isset($chartData['datasets'][0]['backgroundColor']) ? $chartData['datasets'][0]['backgroundColor'] : [];
    
    // Create the chart image directly
    return createChartImage($chartType, $labels, $data, $backgroundColor, $chartTitle, $pngFile);
}
?>
