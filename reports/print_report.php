<?php
require('../fpdf186/fpdf.php');
include("../config/db.php");
$start = $_GET['start'];
$end = $_GET['end'];
$query = "SELECT stock_transactions.*, products.product_name
FROM stock_transactions
LEFT JOIN products ON stock_transactions.product_id = products.product_id
WHERE DATE(transaction_date) BETWEEN '$start' AND '$end'";
$result = mysqli_query($conn,$query);
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Stock Transactions Report',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(190,10,"From: $start   To: $end",0,1,'C');
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(20,10,'ID',1);
$pdf->Cell(60,10,'Product',1);
$pdf->Cell(40,10,'Type',1);
$pdf->Cell(30,10,'Quantity',1);
$pdf->Cell(40,10,'Date',1);
$pdf->Ln();
$pdf->SetFont('Arial','',12);
while($row = mysqli_fetch_assoc($result)){
$pdf->Cell(20,10,$row['transaction_id'],1);
$pdf->Cell(60,10,$row['product_name'],1);
$pdf->Cell(40,10,$row['transaction_type'],1);
$pdf->Cell(30,10,$row['quantity'],1);
$pdf->Cell(40,10,$row['transaction_date'],1);
$pdf->Ln();
}
$pdf->Output();
?>