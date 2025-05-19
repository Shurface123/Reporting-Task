<?php
// Check if TCPDF is already installed
if (!file_exists(__DIR__ . '/tcpdf')) {
    echo "TCPDF library not found. Installing TCPDF...<br>";
    
    // Create a temporary file to download TCPDF
    $zipFile = __DIR__ . '/tcpdf.zip';
    
    // URL to the latest TCPDF release on GitHub
    $tcpdfUrl = 'https://github.com/tecnickcom/TCPDF/archive/6.4.4.zip';
    
    // Download TCPDF
    if (file_put_contents($zipFile, file_get_contents($tcpdfUrl))) {
        echo "TCPDF downloaded successfully.<br>";
        
        // Extract ZIP file
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo(__DIR__);
            $zip->close();
            
            // Rename the extracted folder to 'tcpdf'
            rename(__DIR__ . '/TCPDF-6.4.4', __DIR__ . '/tcpdf');
            
            // Remove the ZIP file
            unlink($zipFile);
            
            echo "TCPDF has been installed successfully.<br>";
        } else {
            echo "Failed to extract TCPDF.<br>";
        }
    } else {
        echo "Failed to download TCPDF.<br>";
    }
} else {
    echo "TCPDF is already installed.<br>";
}

// Check if GD library is available for chart generation
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo "GD library is available for chart generation.<br>";
} else {
    echo "<strong>Warning:</strong> GD library is not available. Charts may not be generated correctly.<br>";
}

// Create output directories
$outputDir = __DIR__ . '/StudentID_FullName_db2';
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
    echo "Created output directory: StudentID_FullName_db2<br>";
}

// Create subdirectories
$sqlDir = $outputDir . '/sql';
$pdfDir = $outputDir . '/pdf';
$chartDir = $outputDir . '/charts';

if (!file_exists($sqlDir)) {
    mkdir($sqlDir, 0777, true);
    echo "Created SQL directory<br>";
}
if (!file_exists($pdfDir)) {
    mkdir($pdfDir, 0777, true);
    echo "Created PDF directory<br>";
}
if (!file_exists($chartDir)) {
    mkdir($chartDir, 0777, true);
    echo "Created charts directory<br>";
}

// Create temp directory for temporary files
$tempDir = __DIR__ . '/temp';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
    echo "Created temp directory<br>";
}

echo "<br>Setup complete. <a href='index.php'>Click here</a> to go to the main application.";
?>
