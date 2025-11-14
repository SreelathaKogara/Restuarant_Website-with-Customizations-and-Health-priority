<?php
require('fpdf/fpdf.php');

function generateInvoice($order_id, $conn) {
    // Fetch order details
    $orderQuery = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $orderQuery->bind_param("i", $order_id);
    $orderQuery->execute();
    $orderResult = $orderQuery->get_result();
    $order = $orderResult->fetch_assoc();
    if (!$order) { return false; }

    // Create invoices folder if not exists
    if (!is_dir('invoices')) {
        mkdir('invoices', 0777, true);
    }

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Header
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Restaurant Invoice',0,1,'C');
    $pdf->Ln(5);

    // Order Info
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,8,"Order ID: {$order['id']}",0,1);
    $pdf->Cell(0,8,"Date: {$order['order_date']}",0,1);
    $pdf->Cell(0,8,"Restaurant: {$order['restaurant_name']}",0,1);
    $pdf->Ln(5);

    // Table Header
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(100,10,'Dish Name',1);
    $pdf->Cell(40,10,'Price',1);
    $pdf->Cell(50,10,'Total',1);
    $pdf->Ln();

    // Single row since no order_items
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(100,10,$order['dish_name'],1);
    $pdf->Cell(40,10,number_format($order['price'],2),1);
    $pdf->Cell(50,10,number_format($order['price'],2),1);

    // Save PDF
    $invoiceFile = "invoices/invoice_{$order['id']}.pdf";
    $pdf->Output('F', $invoiceFile);

    // Update orders table with path
    $updateQuery = $conn->prepare("UPDATE orders SET invoice_file = ? WHERE id = ?");
    $updateQuery->bind_param("si", $invoiceFile, $order_id);
    $updateQuery->execute();

    return $invoiceFile;
}
?>