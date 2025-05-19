<?php
// Function to generate charts using Chart.js
function generateChart($queryData, $resultData, $chartFilePath) {
    // Check if chart type is specified and results exist
    if (!isset($queryData['chart_type']) || $queryData['chart_type'] == 'none' || empty($resultData)) {
        return false;
    }
    
    // Create a unique filename for the chart
    $chartType = $queryData['chart_type'];
    $chartTitle = $queryData['chart_title'];
    
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
    switch ($chartType) {
        case 'bar':
            foreach ($resultData as $key => $row) {
                $labels[] = $row[$queryData['chart_x']];
                $data[] = (float) $row[$queryData['chart_y']];
                $backgroundColor[] = $colors[$key % count($colors)];
            }
            break;
            
        case 'pie':
            foreach ($resultData as $key => $row) {
                $labels[] = $row[$queryData['chart_labels']];
                $data[] = (float) $row[$queryData['chart_data']];
                $backgroundColor[] = $colors[$key % count($colors)];
            }
            break;
            
        case 'line':
            foreach ($resultData as $key => $row) {
                $labels[] = $row[$queryData['chart_x']];
                $data[] = (float) $row[$queryData['chart_y']];
            }
            break;
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
    
    // Convert HTML to image using PhantomJS or another headless browser 
    // (this is a simplified example, in a real environment you'd need PhantomJS/Puppeteer/etc.)
    $chartImagePath = $chartFilePath . '.png';
    
    // For simplicity, we'll create a placeholder function to represent the HTML to PNG conversion
    // In a real environment, you would use a library or headless browser to do this
    convertHtmlToPng($chartFilePath . '.html', $chartImagePath);
    
    return $chartImagePath;
}

// Placeholder function - in real implementations, you'd use a library or external tool
function convertHtmlToPng($htmlFile, $pngFile) {
    // In a real implementation, you would use a tool like:
    // - PhantomJS
    // - Puppeteer
    // - wkhtmltopdf
    // - Chrome Headless
    
    // For this demo, we'll just create a placeholder image
    // In reality, this would capture the chart rendered by Chart.js
    
    // For simplicity, create a sample image with the GD library
    $width = 800;
    $height = 400;
    $image = imagecreatetruecolor($width, $height);
    
    // Fill with white background
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    
    // Draw a border
    $blue = imagecolorallocate($image, 54, 162, 235);
    imagerectangle($image, 0, 0, $width-1, $height-1, $blue);
    
    // Add text - this is a placeholder, in reality this would be a full chart
    $textColor = imagecolorallocate($image, 0, 0, 0);
    $text = "Chart Image Placeholder";
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    imagestring($image, $font, (int)(($width - $textWidth) / 2), (int)(($height - $textHeight) / 2), $text, $textColor);
    
    // Save as PNG
    imagepng($image, $pngFile);
    imagedestroy($image);
    
    return true;
}
?>
