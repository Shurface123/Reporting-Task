<?php
// Include TCPDF library (You may need to download this or include via composer)
require_once('tcpdf/tcpdf.php');

// Function to generate PDF report
function generatePDFReport($queryData, $resultData, $chartImage = null) {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('StudentID_FullName_db3');
    $pdf->SetAuthor('Student');
    $pdf->SetTitle($queryData['title']);
    $pdf->SetSubject('Database Report');
    
    // Remove header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 15, $queryData['title'], 0, 1, 'C');
    
    // Description
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 10, $queryData['description'], 0, 'L');
    
    $pdf->Ln(5);
    
    // Add chart if available
    if ($chartImage !== null && file_exists($chartImage)) {
        $pdf->Image($chartImage, 20, null, 170, 0, 'PNG');
        $pdf->Ln(5);
    }
    
    // Results table
    if (!empty($resultData)) {
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 11);
        
        // Get column headers from first row
        $headers = array_keys($resultData[0]);
        
        // Calculate column widths based on number of columns
        $columnCount = count($headers);
        $columnWidth = 180 / $columnCount;
        
        // Table header
        foreach ($headers as $header) {
            $pdf->Cell($columnWidth, 10, ucwords(str_replace('_', ' ', $header)), 1, 0, 'C');
        }
        $pdf->Ln();
        
        // Table data
        $pdf->SetFont('helvetica', '', 10);
        foreach ($resultData as $row) {
            foreach ($row as $cell) {
                // Format decimal numbers to 2 decimal places
                if (is_numeric($cell) && strpos($cell, '.') !== false) {
                    $cell = number_format((float)$cell, 2);
                }
                $pdf->Cell($columnWidth, 8, $cell, 1, 0, 'L');
            }
            $pdf->Ln();
        }
    } else {
        $pdf->Cell(0, 10, 'No results found.', 0, 1, 'L');
    }
    
    // Close and return the PDF file as a string
    return $pdf->Output('report.pdf', 'S');
}
?>
