<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\IssuedTicket;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    /* ─────────────────────────────────────────────────────────────────────
     | 1. STATISTIK DASHBOARD
     | GET /api/reports/statistik
     ───────────────────────────────────────────────────────────────────── */
    public function statistik()
    {
        $totalPendapatan = Payment::where('status', 'berhasil')->sum('jumlah_bayar');
        $totalPesanan    = Order::count();
        $totalPengguna   = User::count();
        $totalEvent      = Event::count();

        $tiketTerpakai   = IssuedTicket::where('status', 'used')->count();
        $tiketBelum      = IssuedTicket::where('status', 'valid')->count();

        $pesananPending  = Order::where('status', 'pending')->count();
        $pesananDibayar  = Order::whereIn('status', ['dibayar', 'selesai'])->count();

        $totalTiketTerjual = OrderDetail::sum('jumlah');

        // Grafik penjualan 12 bulan terakhir (per bulan)
        $grafikBulanan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $label = $bulan->translatedFormat('M Y');
            $jumlah = Order::whereYear('created_at', $bulan->year)
                ->whereMonth('created_at', $bulan->month)
                ->count();
            $pendapatan = Payment::where('status', 'berhasil')
                ->whereYear('created_at', $bulan->year)
                ->whereMonth('created_at', $bulan->month)
                ->sum('jumlah_bayar');
            $grafikBulanan[] = [
                'label'      => $label,
                'pesanan'    => $jumlah,
                'pendapatan' => $pendapatan,
            ];
        }

        // Grafik harian 30 hari terakhir
        $grafikHarian = [];
        for ($i = 29; $i >= 0; $i--) {
            $hari = Carbon::today()->subDays($i);
            $grafikHarian[] = [
                'label'      => $hari->format('d/m'),
                'pesanan'    => Order::whereDate('created_at', $hari)->count(),
                'pendapatan' => Payment::where('status', 'berhasil')->whereDate('created_at', $hari)->sum('jumlah_bayar'),
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => compact(
                'totalPendapatan', 'totalPesanan', 'totalPengguna', 'totalEvent',
                'tiketTerpakai', 'tiketBelum', 'pesananPending', 'pesananDibayar',
                'totalTiketTerjual', 'grafikBulanan', 'grafikHarian'
            ),
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | 2. LAPORAN PENJUALAN
     | GET /api/reports/penjualan
     ───────────────────────────────────────────────────────────────────── */
    public function penjualan(Request $request)
    {
        $query = Order::with(['user', 'payment', 'orderDetails.ticket.event'])
            ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('event_id')) {
            $query->whereHas('orderDetails.ticket', fn($q) => $q->where('event_id', $request->event_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get()->map(function ($o) {
            $eventNames = $o->orderDetails->pluck('ticket.event.nama_event')->filter()->unique()->implode(', ');
            $tiketNames = $o->orderDetails->map(fn($d) => $d->ticket->nama_tiket . ' x' . $d->jumlah)->implode(', ');
            $totalTiket = $o->orderDetails->sum('jumlah');
            return [
                'id'                 => $o->id,
                'kode_pesanan'       => $o->kode_pesanan,
                'nama_pembeli'       => $o->user?->name ?? '-',
                'event'              => $eventNames ?: '-',
                'jenis_tiket'        => $tiketNames ?: '-',
                'jumlah_tiket'       => $totalTiket,
                'total_harga'        => $o->total_harga,
                'status_pembayaran'  => $o->payment?->status ?? $o->status,
                'tanggal_pembelian'  => $o->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json(['status' => true, 'data' => $orders]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | 3. LAPORAN EVENT
     | GET /api/reports/event
     ───────────────────────────────────────────────────────────────────── */
    public function laporanEvent()
    {
        $events = Event::with(['tickets.orderDetails'])->get()->map(function ($e) {
            $tiketTersedia = $e->tickets->sum('stok');
            $tiketTerjual  = $e->tickets->sum(fn($t) => $t->orderDetails->sum('jumlah'));
            $tiketTersisa  = $e->tickets->sum('stok');
            $pendapatan    = $e->tickets->sum(fn($t) => $t->orderDetails->sum('subtotal'));

            return [
                'id'              => $e->id,
                'nama_event'      => $e->nama_event,
                'tanggal'         => $e->tanggal,
                'lokasi'          => $e->lokasi,
                'tiket_tersedia'  => $tiketTersedia,
                'tiket_terjual'   => $tiketTerjual,
                'tiket_tersisa'   => $tiketTersisa,
                'total_pendapatan'=> $pendapatan,
                'status'          => $e->status,
            ];
        });

        return response()->json(['status' => true, 'data' => $events]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | 4. LAPORAN TIKET
     | GET /api/reports/tiket
     ───────────────────────────────────────────────────────────────────── */
    public function laporanTiket()
    {
        $tikets = Ticket::with(['event', 'orderDetails', 'issuedTickets'])->get()->map(function ($t) {
            $terjual    = $t->orderDetails->sum('jumlah');
            $digunakan  = $t->issuedTickets->where('status', 'used')->count();
            $belumDigunakan = $t->issuedTickets->where('status', 'valid')->count();

            return [
                'id'              => $t->id,
                'jenis_tiket'     => $t->nama_tiket,
                'event'           => $t->event?->nama_event ?? '-',
                'harga'           => $t->harga,
                'stok_awal'       => $t->stok + $terjual,
                'terjual'         => $terjual,
                'tersisa'         => $t->stok,
                'digunakan'       => $digunakan,
                'belum_digunakan' => $belumDigunakan,
            ];
        });

        return response()->json(['status' => true, 'data' => $tikets]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | 5. LAPORAN SCAN QR
     | GET /api/reports/scan
     ───────────────────────────────────────────────────────────────────── */
    public function laporanScan(Request $request)
    {
        $query = IssuedTicket::with(['order.user', 'ticket.event', 'scanner'])
            ->where('status', 'used');

        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }
        if ($request->filled('event_id')) {
            $query->whereHas('ticket', fn($q) => $q->where('event_id', $request->event_id));
        }

        $scans = $query->orderByDesc('scanned_at')->get()->map(function ($it) {
            return [
                'id'            => $it->id,
                'kode_tiket'    => $it->qr_token,
                'kode_pesanan'  => $it->order?->kode_pesanan ?? '-',
                'nama_pembeli'  => $it->order?->user?->name ?? '-',
                'email'         => $it->order?->user?->email ?? '-',
                'event'         => $it->ticket?->event?->nama_event ?? '-',
                'jenis_tiket'   => $it->ticket?->nama_tiket ?? '-',
                'status'        => 'Digunakan',
                'waktu_scan'    => $it->scanned_at?->format('d/m/Y H:i'),
                'scanner'       => $it->scanner?->name ?? '-',
            ];
        });

        // Statistik scan
        $totalScan       = IssuedTicket::where('status', 'used')->count();
        $tiketValid      = IssuedTicket::where('status', 'valid')->count();
        $scanHariIni     = IssuedTicket::where('status', 'used')->whereDate('scanned_at', today())->count();
        $scanPerEvent    = IssuedTicket::where('status', 'used')
            ->with('ticket.event')
            ->get()
            ->groupBy(fn($it) => $it->ticket?->event?->nama_event ?? 'Unknown')
            ->map->count();

        return response()->json([
            'status' => true,
            'data'   => $scans,
            'stats'  => compact('totalScan', 'tiketValid', 'scanHariIni', 'scanPerEvent'),
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | 6. LAPORAN PENGGUNA
     | GET /api/reports/pengguna
     ───────────────────────────────────────────────────────────────────── */
    public function laporanPengguna()
    {
        $users = User::with('orders.payment')->get()->map(function ($u) {
            $totalPesanan   = $u->orders->count();
            $totalPembelian = $u->orders->whereIn('status', ['dibayar', 'selesai'])->sum('total_harga');
            return [
                'id'              => $u->id,
                'nama'            => $u->name,
                'email'           => $u->email,
                'role'            => $u->role,
                'bergabung'       => $u->created_at?->format('d/m/Y'),
                'total_pesanan'   => $totalPesanan,
                'total_pembelian' => $totalPembelian,
            ];
        });

        $totalPengguna  = User::count();
        $penggunaBaru   = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $penggunaAktif  = User::has('orders')->count();

        return response()->json([
            'status' => true,
            'data'   => $users,
            'stats'  => compact('totalPengguna', 'penggunaBaru', 'penggunaAktif'),
        ]);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | EXPORT PDF
     | GET /api/reports/export/pdf/{type}
     ───────────────────────────────────────────────────────────────────── */
    public function exportPdf(Request $request, string $type)
    {
        $data  = $this->getDataForType($type, $request);
        $title = $this->titleForType($type);

        $viewData = [
            'data'       => $data['rows'],
            'title'      => $title,
            'stats'      => $data['stats'] ?? [],
            'generated'  => now()->format('d/m/Y H:i'),
            'columns'    => $data['columns'],
        ];

        if ($type === 'statistik') {
            $viewData['grafikBulanan'] = $data['grafikBulanan'] ?? [];
            $viewData['grafikHarian']  = $data['grafikHarian'] ?? [];
        }

        $pdf = Pdf::loadView('reports.' . $type, $viewData)->setPaper('a4', 'landscape');

        return $pdf->download("laporan_{$type}_" . now()->format('Ymd_His') . '.pdf');
    }

    /* ─────────────────────────────────────────────────────────────────────
     | EXPORT EXCEL LAPORAN
     | GET /api/reports/export/excel/{type}
     ───────────────────────────────────────────────────────────────────── */
    public function exportExcel(Request $request, string $type)
    {
        $data  = $this->getDataForType($type, $request);
        $title = $this->titleForType($type);

        if ($type === 'statistik') {
            $spreadsheet = $this->buildStatistikSpreadsheet(
                $data['stats'] ?? [],
                $data['grafikBulanan'] ?? [],
                $data['grafikHarian'] ?? []
            );
        } else {
            $columns = $data['columns'];
            $rows    = $data['rows'];
            $spreadsheet = $this->buildSpreadsheet($title, $columns, $rows);
        }

        $filename = "laporan_{$type}_" . now()->format('Ymd_His') . '.xlsx';
        return $this->streamXlsx($spreadsheet, $filename);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | EXPORT EXCEL PER-PAGE (untuk tabel halaman admin)
     | POST /api/reports/export/excel-page
     | Body: { title, columns: string[], rows: array[], filename? }
     ───────────────────────────────────────────────────────────────────── */
    public function exportExcelPage(Request $request)
    {
        $request->validate([
            'title'    => 'required|string',
            'columns'  => 'required|array',
            'rows'     => 'required|array',
        ]);

        $spreadsheet = $this->buildSpreadsheet(
            $request->title,
            $request->columns,
            $request->rows
        );

        $filename = $request->input('filename', 'export_' . now()->format('Ymd_His')) . '.xlsx';
        return $this->streamXlsx($spreadsheet, $filename);
    }

    /* ─────────────────────────────────────────────────────────────────────
     | PRIVATE HELPERS
     ───────────────────────────────────────────────────────────────────── */
    private function getDataForType(string $type, Request $request): array
    {
        return match ($type) {
            'statistik' => (function () {
                $statData = $this->statistik()->getData(true)['data'];
                return [
                    'rows'          => array_map(fn($b) => [
                        'Bulan'            => $b['label'],
                        'Jumlah Pesanan'   => $b['pesanan'],
                        'Total Pendapatan' => 'Rp ' . number_format($b['pendapatan'], 0, ',', '.'),
                    ], $statData['grafikBulanan'] ?? []),
                    'stats'         => $statData,
                    'grafikBulanan' => $statData['grafikBulanan'] ?? [],
                    'grafikHarian'  => $statData['grafikHarian'] ?? [],
                    'columns'       => ['Bulan', 'Jumlah Pesanan', 'Total Pendapatan'],
                ];
            })(),
            'penjualan' => [
                'rows'    => $this->penjualan($request)->getData(true)['data'],
                'stats'   => [],
                'columns' => ['Kode Pesanan', 'Nama Pembeli', 'Event', 'Jenis Tiket', 'Jml Tiket', 'Total Harga', 'Status', 'Tanggal'],
            ],
            'event' => [
                'rows'    => $this->laporanEvent()->getData(true)['data'],
                'stats'   => [],
                'columns' => ['Nama Event', 'Tanggal', 'Lokasi', 'Tersedia', 'Terjual', 'Tersisa', 'Pendapatan', 'Status'],
            ],
            'tiket' => [
                'rows'    => $this->laporanTiket()->getData(true)['data'],
                'stats'   => [],
                'columns' => ['Jenis Tiket', 'Event', 'Harga', 'Stok Awal', 'Terjual', 'Tersisa', 'Digunakan', 'Belum Digunakan'],
            ],
            'scan' => [
                'rows'    => $this->laporanScan($request)->getData(true)['data'],
                'stats'   => $this->laporanScan($request)->getData(true)['stats'],
                'columns' => ['Kode Tiket', 'Kode Pesanan', 'Nama Pembeli', 'Email', 'Event', 'Jenis Tiket', 'Status', 'Waktu Scan', 'Scanner'],
            ],
            'pengguna' => [
                'rows'    => $this->laporanPengguna()->getData(true)['data'],
                'stats'   => $this->laporanPengguna()->getData(true)['stats'],
                'columns' => ['Nama', 'Email', 'Role', 'Bergabung', 'Total Pesanan', 'Total Pembelian'],
            ],
            default => ['rows' => [], 'stats' => [], 'columns' => []],
        };
    }

    private function titleForType(string $type): string
    {
        return match ($type) {
            'statistik' => 'Dashboard Statistik',
            'penjualan' => 'Laporan Penjualan',
            'event'     => 'Laporan Event',
            'tiket'     => 'Laporan Tiket',
            'scan'      => 'Laporan Scan QR',
            'pengguna'  => 'Laporan Pengguna',
            default     => 'Laporan',
        };
    }

    private function buildStatistikSpreadsheet(array $stats, array $grafikBulanan, array $grafikHarian): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dashboard Statistik');

        // Title Header
        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue('A1', 'LAPORAN DASHBOARD STATISTIK');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sub-header
        $sheet->mergeCells("A2:F2");
        $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Section 1: Ringkasan Utama
        $sheet->mergeCells("A4:F4");
        $sheet->setCellValue('A4', 'RINGKASAN UTAMA');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '5B21B6']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
        ]);

        $kpis = [
            ['Total Pendapatan', 'Rp ' . number_format($stats['totalPendapatan'] ?? 0, 0, ',', '.'), 'Total Pengguna', number_format($stats['totalPengguna'] ?? 0, 0, ',', '.')],
            ['Total Pesanan', number_format($stats['totalPesanan'] ?? 0, 0, ',', '.'), 'Total Event', number_format($stats['totalEvent'] ?? 0, 0, ',', '.')],
            ['Tiket Terjual', number_format($stats['totalTiketTerjual'] ?? 0, 0, ',', '.'), 'Tiket Terpakai', number_format($stats['tiketTerpakai'] ?? 0, 0, ',', '.')],
            ['Tiket Belum Digunakan', number_format($stats['tiketBelum'] ?? 0, 0, ',', '.'), 'Pesanan Pending', number_format($stats['pesananPending'] ?? 0, 0, ',', '.')],
            ['Pesanan Dibayar', number_format($stats['pesananDibayar'] ?? 0, 0, ',', '.'), '', ''],
        ];

        $rowNum = 5;
        foreach ($kpis as $kpi) {
            $sheet->setCellValue("A{$rowNum}", $kpi[0]);
            $sheet->setCellValue("B{$rowNum}", $kpi[1]);
            $sheet->setCellValue("D{$rowNum}", $kpi[2]);
            $sheet->setCellValue("E{$rowNum}", $kpi[3]);
            $sheet->getStyle("A{$rowNum}")->getFont()->setBold(true);
            $sheet->getStyle("D{$rowNum}")->getFont()->setBold(true);
            $rowNum++;
        }

        $rowNum += 2;
        // Section 2: Penjualan Bulanan
        $sheet->mergeCells("A{$rowNum}:C{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'PENJUALAN BULANAN (12 BULAN)');
        $sheet->getStyle("A{$rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B21B6']],
        ]);
        $rowNum++;

        $sheet->setCellValue("A{$rowNum}", 'Bulan');
        $sheet->setCellValue("B{$rowNum}", 'Pesanan');
        $sheet->setCellValue("C{$rowNum}", 'Pendapatan');
        $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getFont()->setBold(true);
        $rowNum++;

        foreach ($grafikBulanan as $b) {
            $sheet->setCellValue("A{$rowNum}", $b['label']);
            $sheet->setCellValue("B{$rowNum}", $b['pesanan']);
            $sheet->setCellValue("C{$rowNum}", 'Rp ' . number_format($b['pendapatan'], 0, ',', '.'));
            $rowNum++;
        }

        $rowNum += 2;
        // Section 3: Pesanan Harian
        $sheet->mergeCells("A{$rowNum}:C{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'PESANAN HARIAN (30 HARI TERAKHIR)');
        $sheet->getStyle("A{$rowNum}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B21B6']],
        ]);
        $rowNum++;

        $sheet->setCellValue("A{$rowNum}", 'Tanggal');
        $sheet->setCellValue("B{$rowNum}", 'Pesanan');
        $sheet->setCellValue("C{$rowNum}", 'Pendapatan');
        $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getFont()->setBold(true);
        $rowNum++;

        foreach ($grafikHarian as $h) {
            $sheet->setCellValue("A{$rowNum}", $h['label']);
            $sheet->setCellValue("B{$rowNum}", $h['pesanan']);
            $sheet->setCellValue("C{$rowNum}", 'Rp ' . number_format($h['pendapatan'], 0, ',', '.'));
            $rowNum++;
        }

        foreach (range(1, 6) as $ci) {
            $sheet->getColumnDimensionByColumn($ci)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function buildSpreadsheet(string $title, array $columns, array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));

        // ── Header judul ──────────────────────────────────────────────
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Sub-header tanggal cetak ───────────────────────────────────
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header kolom ──────────────────────────────────────────────
        $colIdx = 1;
        foreach ($columns as $col) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . '3';
            $sheet->setCellValue($cellRef, $col);
            $colIdx++;
        }
        $headerRange = "A3:{$lastCol}3";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5B21B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // ── Data rows ─────────────────────────────────────────────────
        $rowNum = 4;
        foreach ($rows as $i => $row) {
            $values = is_array($row) ? array_values($row) : (array)$row;
            $colIdx = 1;
            foreach ($values as $val) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowNum;
                $sheet->setCellValue($cell, $val);
                $colIdx++;
            }
            // Zebra striping
            if ($i % 2 === 1) {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
                ]);
            }
            $rowNum++;
        }

        // ── Border seluruh tabel ──────────────────────────────────────
        if ($rowNum > 4) {
            $sheet->getStyle("A3:{$lastCol}" . ($rowNum - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            ]);
        }

        // ── Auto width ────────────────────────────────────────────────
        foreach (range(1, count($columns)) as $ci) {
            $sheet->getColumnDimensionByColumn($ci)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function streamXlsx(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);
        $tmp    = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }
}
