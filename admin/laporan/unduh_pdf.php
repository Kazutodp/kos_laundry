<?php
// admin/laporan/unduh_pdf.php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Akses ditolak. Silakan login sebagai admin.";
    exit();
}

require_once '../../db_connect.php';
require_once 'fpdf.php';

// Indonesian month names
$bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$selected_month = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$selected_year = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Clamp values
if ($selected_month < 1 || $selected_month > 12) $selected_month = (int)date('n');
if ($selected_year < 2024 || $selected_year > 2030) $selected_year = (int)date('Y');

$selected_label = $bulan_indo[$selected_month] . ' ' . $selected_year;

try {
    // Fetch active partners with order metrics filtered by selected period
    $stmt = $pdo->prepare("SELECT m.*, 
                               COUNT(o.id) as real_orders, 
                               COALESCE(SUM(o.total_harga), 0) as real_gross
                        FROM mitra_laundry m
                        LEFT JOIN orders o ON m.id = o.mitra_id 
                            AND o.status_pembayaran = 'success'
                            AND MONTH(o.created_at) = ?
                            AND YEAR(o.created_at) = ?
                        GROUP BY m.id
                        ORDER BY m.rating DESC");
    $stmt->execute([$selected_month, $selected_year]);
    $raw_mitras = $stmt->fetchAll();
    
    $mitra_list = [];
    $total_gross = 0;
    $total_orders = 0;
    
    foreach ($raw_mitras as $mitra) {
        $file_name = str_replace(' ', '_', $mitra['nama_mitra']) . '.php';
        if (file_exists('../../Mitra laundry/' . $file_name)) {
            $orders = (int)$mitra['real_orders'];
            $gross = (float)$mitra['real_gross'];
            
            $mitra['simulated_orders'] = $orders;
            $mitra['simulated_gross'] = $gross;
            $mitra['simulated_platform'] = $gross * 0.10;
            $mitra['simulated_net'] = $gross * 0.90;
            
            $total_orders += $orders;
            $total_gross += $gross;
            $mitra_list[] = $mitra;
        }
    }
    
    // If no transactions found, block download
    if ($total_orders == 0 && $total_gross == 0) {
        header("Location: ../financial_statements.php?error=nodata&bulan=$selected_month&tahun=$selected_year");
        exit();
    }
} catch (PDOException $e) {
    header("Location: ../financial_statements.php?error=db&bulan=$selected_month&tahun=$selected_year");
    exit();
}

// Start generating PDF
class PDF extends FPDF {
    // Page header
    function Header() {
        // Logo
        if (file_exists('../../Logo_MataramWash.png')) {
            $this->Image('../../Logo_MataramWash.png', 10, 8, 15);
        }
        // Arial bold 15
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(21, 28, 39); // on-surface color
        // Title
        $this->Cell(20); // Spacer
        $this->Cell(100, 10, 'MataramWash Laundry Platform', 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(114, 119, 133); // outline gray
        $this->Cell(70, 10, 'Laporan Keuangan Bulanan', 0, 1, 'R');
        
        // Line break / divider line
        $this->SetDrawColor(226, 232, 248); // outline-variant color
        $this->SetLineWidth(0.5);
        $this->Line(10, 26, 200, 26);
        $this->Ln(10);
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(114, 119, 133);
        // Page number
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb} - Dokumen resmi diproduksi secara otomatis oleh MataramWash', 0, 0, 'C');
    }
}

// Instantiation of inherited class
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Document Info Block
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'RINGKASAN PERIODE LAPORAN', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, 'Periode Siklus', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 6, strtoupper($selected_label), 0, 0, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, 'Tanggal Cetak', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, date('d F Y H:i:s'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, 'Jumlah Outlet Aktif', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->Cell(60, 6, count($mitra_list) . ' Toko Mitra', 0, 0, 'L');

$pdf->Cell(40, 6, 'Operator', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->Cell(0, 6, 'Administrator MataramWash', 0, 1, 'L');

$pdf->Ln(8);

// Summary Box Layout
$pdf->SetFillColor(240, 243, 255); // surface-container-low color
$pdf->Rect(10, 58, 190, 26, 'F');

$pdf->SetXY(15, 61);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(66, 71, 84); // on-surface-variant
$pdf->Cell(60, 5, 'TOTAL OMSET BRUTO', 0, 0, 'C');
$pdf->Cell(60, 5, 'TOTAL KOMISI PLATFORM (10%)', 0, 0, 'C');
$pdf->Cell(60, 5, 'TOTAL PAYOUT MITRA (90%)', 0, 1, 'C');

$pdf->SetXY(15, 68);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(21, 28, 39); // on-surface
$pdf->Cell(60, 10, 'Rp ' . number_format($total_gross, 0, ',', '.'), 0, 0, 'C');
$pdf->SetTextColor(0, 107, 95); // secondary / emerald green
$pdf->Cell(60, 10, 'Rp ' . number_format($total_gross * 0.10, 0, ',', '.'), 0, 0, 'C');
$pdf->SetTextColor(59, 130, 246); // primary / blue
$pdf->Cell(60, 10, 'Rp ' . number_format($total_gross * 0.90, 0, ',', '.'), 0, 1, 'C');

$pdf->SetTextColor(21, 28, 39); // reset text color
$pdf->SetY(92);

// Table Section
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, 'RINCIAN TRANSAKSI BAGI HASIL MITRA', 0, 1, 'L');
$pdf->Ln(2);

// Table Header
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(226, 232, 248); // surface-container-high
$pdf->SetDrawColor(194, 198, 214); // outline-variant

$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(60, 8, 'Nama Mitra Laundry', 1, 0, 'L', true);
$pdf->Cell(30, 8, 'Total Orders', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Omset Bruto', 1, 0, 'R', true);
$pdf->Cell(30, 8, 'Komisi (10%)', 1, 0, 'R', true);
$pdf->Cell(30, 8, 'Payout Mitra (90%)', 1, 1, 'R', true);

// Table Body
$pdf->SetFont('Arial', '', 9);
$no = 1;
foreach ($mitra_list as $mitra) {
    // Alternate row colors
    $fill = ($no % 2 === 0);
    $pdf->SetFillColor(249, 249, 255); // background light

    $pdf->Cell(10, 8, $no++, 1, 0, 'C', $fill);
    $pdf->Cell(60, 8, ' ' . $mitra['nama_mitra'], 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $mitra['simulated_orders'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 8, 'Rp ' . number_format($mitra['simulated_gross'], 0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(30, 8, 'Rp ' . number_format($mitra['simulated_platform'], 0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(30, 8, 'Rp ' . number_format($mitra['simulated_net'], 0, ',', '.'), 1, 1, 'R', $fill);
}

// Table Footer Total Row
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(240, 243, 255);
$pdf->Cell(70, 8, ' TOTAL KESELURUHAN', 1, 0, 'L', true);
$pdf->Cell(30, 8, $total_orders, 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Rp ' . number_format($total_gross, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(30, 8, 'Rp ' . number_format($total_gross * 0.10, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(30, 8, 'Rp ' . number_format($total_gross * 0.90, 0, ',', '.'), 1, 1, 'R', true);

$pdf->Ln(15);

// Signatures Block
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Mataram, ' . date('d F Y'), 0, 1, 'C');
$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Mengetahui,', 0, 1, 'C');
$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Direktur MataramWash', 0, 1, 'C');

$pdf->Ln(20);

// Underline signature line
$pdf->Cell(120, 5, '', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 5, 'Larbiansyah', 0, 1, 'C');
$pdf->Cell(120, 5, '', 0, 0);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(70, 5, 'System Administrator', 0, 1, 'C');

// Output PDF to browser download
$file_name = 'Laporan_Keuangan_' . str_replace(' ', '_', $selected_label) . '.pdf';
$pdf->Output('D', $file_name);
