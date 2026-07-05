<?php
// mitra/unduh_pdf.php
session_start();

// Authentication check
if (!isset($_SESSION['mitra_logged_in']) || !isset($_SESSION['mitra_id'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Akses ditolak. Silakan login sebagai mitra.";
    exit();
}

require_once '../db_connect.php';
require_once '../admin/laporan/fpdf.php';

$mitra_id = $_SESSION['mitra_id'];

// Indonesian month names
$bulan_indo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Fetch Partner Profile Data
$stmt_mitra = $pdo->prepare("SELECT * FROM mitra_laundry WHERE id = ?");
$stmt_mitra->execute([$mitra_id]);
$mitra = $stmt_mitra->fetch(PDO::FETCH_ASSOC);

if (!$mitra) {
    echo "Mitra tidak ditemukan.";
    exit();
}

$selected_month = trim($_GET['filter_month'] ?? 'all');
if ($selected_month !== 'all' && !preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = 'all';
}

if ($selected_month === 'all') {
    $selected_label = 'Semua Waktu';
    
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE mitra_id = ? AND status_pembayaran = 'success' AND is_hidden_mitra = 0 ORDER BY id DESC");
    $stmt_orders->execute([$mitra_id]);
} else {
    $parts = explode('-', $selected_month);
    $selected_label = ($bulan_indo[$parts[1]] ?? '') . ' ' . $parts[0];
    
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE mitra_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ? AND status_pembayaran = 'success' AND is_hidden_mitra = 0 ORDER BY id DESC");
    $stmt_orders->execute([$mitra_id, $selected_month]);
}

$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// If no transactions found, block download
if (empty($orders)) {
    header("Location: dashboard.php?error=nodata&filter_month=" . urlencode($selected_month));
    exit();
}

// Calculate totals
$total_gross = 0;
foreach ($orders as $order) {
    $total_gross += (float)$order['total_harga'];
}
$total_net = $total_gross * 0.90;
$total_commission = $total_gross * 0.10;
$total_count = count($orders);

// Start generating PDF
class PDF extends FPDF {
    protected $nama_mitra;
    
    public function setNamaMitra($nama) {
        $this->nama_mitra = $nama;
    }

    // Page header
    function Header() {
        if (file_exists('../Logo_MataramWash.png')) {
            $this->Image('../Logo_MataramWash.png', 10, 8, 15);
        }
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(21, 28, 39); // on-surface color
        $this->Cell(20); // Spacer
        $this->Cell(100, 10, 'MataramWash Kemitraan', 0, 0, 'L');
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
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(114, 119, 133);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb} - Laporan resmi outlet ' . $this->nama_mitra, 0, 0, 'C');
    }
}

// Instantiation of inherited class
$pdf = new PDF('P', 'mm', 'A4');
$pdf->setNamaMitra($mitra['nama_mitra']);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Document Info Block
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'LAPORAN REKAPITULASI MITRA', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 6, 'Nama Toko Mitra', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 6, $mitra['nama_mitra'], 0, 0, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(35, 6, 'Tanggal Cetak', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, date('d F Y H:i'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 6, 'Periode Siklus', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 6, strtoupper($selected_label), 0, 0, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(35, 6, 'Total Transaksi', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, $total_count . ' Sukses', 0, 1, 'L');

$pdf->Ln(8);

// Summary Box Layout
$pdf->SetFillColor(240, 243, 255); // surface-container-low color
$pdf->Rect(10, 58, 190, 26, 'F');

$pdf->SetXY(15, 61);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(66, 71, 84); // on-surface-variant
$pdf->Cell(60, 5, 'TOTAL OMSET BRUTO', 0, 0, 'C');
$pdf->Cell(60, 5, 'KOMISI PLATFORM (10%)', 0, 0, 'C');
$pdf->Cell(60, 5, 'PENDAPATAN BERSIH MITRA (90%)', 0, 1, 'C');

$pdf->SetXY(15, 68);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(21, 28, 39); // on-surface
$pdf->Cell(60, 10, 'Rp ' . number_format($total_gross, 0, ',', '.'), 0, 0, 'C');
$pdf->SetTextColor(185, 28, 28); // red-700
$pdf->Cell(60, 10, 'Rp ' . number_format($total_commission, 0, ',', '.'), 0, 0, 'C');
$pdf->SetTextColor(4, 120, 87); // emerald-700
$pdf->Cell(60, 10, 'Rp ' . number_format($total_net, 0, ',', '.'), 0, 1, 'C');

$pdf->SetTextColor(21, 28, 39); // reset text color
$pdf->SetY(92);

// Table Section
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, 'DAFTAR TRANSAKSI RINCI', 0, 1, 'L');
$pdf->Ln(2);

// Table Header
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(226, 232, 248); // surface-container-high
$pdf->SetDrawColor(194, 198, 214); // outline-variant

$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'ID Pesanan', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Tgl Transaksi', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Pelanggan', 1, 0, 'L', true);
$pdf->Cell(35, 8, 'Omset Bruto', 1, 0, 'R', true);
$pdf->Cell(35, 8, 'Bersih Mitra (90%)', 1, 1, 'R', true);

// Table Body
$pdf->SetFont('Arial', '', 9);
$no = 1;
foreach ($orders as $order) {
    $fill = ($no % 2 === 0);
    $pdf->SetFillColor(249, 249, 255);
    
    // Format date
    $date_formatted = date('d-m-Y H:i', strtotime($order['created_at']));
    $net_row = (float)$order['total_harga'] * 0.90;

    $pdf->Cell(10, 8, $no++, 1, 0, 'C', $fill);
    $pdf->Cell(35, 8, ' #' . $order['id'], 1, 0, 'C', $fill);
    $pdf->Cell(35, 8, $date_formatted, 1, 0, 'C', $fill);
    $pdf->Cell(40, 8, ' ' . $order['nama_pelanggan'], 1, 0, 'L', $fill);
    $pdf->Cell(35, 8, 'Rp ' . number_format($order['total_harga'], 0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(35, 8, 'Rp ' . number_format($net_row, 0, ',', '.'), 1, 1, 'R', $fill);
}

// Table Footer Total Row
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(240, 243, 255);
$pdf->Cell(120, 8, ' TOTAL KESELURUHAN', 1, 0, 'L', true);
$pdf->Cell(35, 8, 'Rp ' . number_format($total_gross, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(35, 8, 'Rp ' . number_format($total_net, 0, ',', '.'), 1, 1, 'R', true);

$pdf->Ln(15);

// Signatures Block
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Mataram, ' . date('d F Y'), 0, 1, 'C');
$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Mengetahui,', 0, 1, 'C');
$pdf->Cell(120, 5, '', 0, 0);
$pdf->Cell(70, 5, 'Pemilik Outlet ' . $mitra['nama_mitra'], 0, 1, 'C');

$pdf->Ln(20);

// Underline signature line
$pdf->Cell(120, 5, '', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 5, '____________________', 0, 1, 'C');
$pdf->Cell(120, 5, '', 0, 0);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(70, 5, 'Penanggung Jawab', 0, 1, 'C');

// Output PDF to browser download
$file_title = 'Laporan_Mitra_' . str_replace(' ', '_', $mitra['nama_mitra']) . '_' . str_replace(' ', '_', $selected_label) . '.pdf';
$pdf->Output('D', $file_title);
?>
