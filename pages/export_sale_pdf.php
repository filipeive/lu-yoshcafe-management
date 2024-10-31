<?php
require_once '../config/config.php';
require_once '../vendor/setasign/fpdf/fpdf.php'; // Inclua a biblioteca FPDF
require_login();

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "ID da venda não fornecido";
    header('Location: sales.php');
    exit;
}

$sale_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$sale = get_sale($sale_id);
$sale_items = get_sale_items($sale_id);

if (!$sale) {
    $_SESSION['error_message'] = "Venda não encontrada";
    header('Location: sales.php');
    exit;
}

// Criação do PDF
$pdf = new FPDF();
$pdf->AddPage();

// Cabeçalho
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode("Recibo de Venda #{$sale_id}"), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode("Data: " . date('d/m/Y à\s H:i', strtotime($sale['sale_date']))), 0, 1, 'C');
$pdf->Ln(5);

// Informações da venda
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("Detalhes da Venda"), 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, utf8_decode("Total: MZN " . number_format($sale['total_amount'], 2, ',', '.')), 0, 1);
$pdf->Cell(0, 8, utf8_decode("Itens Vendidos: " . count($sale_items)), 0, 1);
$pdf->Cell(0, 8, utf8_decode("Método de Pagamento: " . ucfirst($sale['payment_method'])), 0, 1);
$pdf->Ln(5);

// Itens da Venda
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("Itens da Venda"), 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(70, 8, utf8_decode("Produto"), 1);
$pdf->Cell(30, 8, utf8_decode("Quantidade"), 1, 0, 'C');
$pdf->Cell(40, 8, utf8_decode("Preço Unitário"), 1, 0, 'R');
$pdf->Cell(40, 8, utf8_decode("Subtotal"), 1, 1, 'R');

foreach ($sale_items as $item) {
    $product_name = get_product_name($item['product_id']);
    $quantity = $item['quantity'];
    $unit_price = number_format($item['unit_price'], 2, ',', '.');
    $subtotal = number_format($item['quantity'] * $item['unit_price'], 2, ',', '.');

    $pdf->Cell(70, 8, utf8_decode($product_name), 1);
    $pdf->Cell(30, 8, $quantity, 1, 0, 'C');
    $pdf->Cell(40, 8, "MZN {$unit_price}", 1, 0, 'R');
    $pdf->Cell(40, 8, "MZN {$subtotal}", 1, 1, 'R');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("Total Geral: MZN " . number_format($sale['total_amount'], 2, ',', '.')), 0, 1, 'R');

// Saída do PDF
$pdf->Output("D", "Venda_{$sale_id}.pdf"); // Define o arquivo para download
exit;
?>
