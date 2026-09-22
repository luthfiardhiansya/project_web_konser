<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1f2937; background: #fff; padding: 15px; }
  .header { background: #7c3aed; color: white; padding: 14px 18px; margin-bottom: 16px; border-radius: 4px; }
  .header h1 { font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }
  .header p  { font-size: 8.5px; margin-top: 3px; opacity: 0.9; }

  .section-title { font-size: 11px; font-weight: 700; color: #5b21b6; margin-bottom: 8px; margin-top: 14px; border-bottom: 2px solid #7c3aed; padding-bottom: 3px; }

  /* KPI Cards Grid */
  .kpi-table { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 10px; }
  .kpi-card { background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 6px; padding: 8px 10px; text-align: center; }
  .kpi-label { font-size: 8px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; }
  .kpi-value { font-size: 13px; font-weight: 700; color: #5b21b6; }
  .kpi-value.green { color: #059669; }
  .kpi-value.orange { color: #d97706; }

  /* Data Tables */
  table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  table.data-table thead tr { background: #5b21b6; color: #fff; }
  table.data-table thead th { padding: 6px 8px; text-align: left; font-size: 8.5px; font-weight: 700; border: 1px solid #6d28d9; }
  table.data-table tbody tr { border-bottom: 1px solid #e5e7eb; }
  table.data-table tbody tr:nth-child(even) { background: #f9fafb; }
  table.data-table tbody td { padding: 5px 8px; font-size; 8.5px; }

  .two-col { width: 100%; border-collapse: collapse; }
  .two-col td { width: 50%; vertical-align: top; }
  .two-col td:first-child { padding-right: 8px; }
  .two-col td:last-child { padding-left: 8px; }

  .footer { font-size: 8px; color: #9ca3af; text-align: right; margin-top: 12px; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
  <div class="header">
    <h1>{{ strtoupper($title) }}</h1>
    <p>Dicetak: {{ $generated }} | InfoMusikBDG Admin Panel</p>
  </div>

  <div class="section-title">RINGKASAN STATISTIK UTAMA</div>
  <table class="kpi-table">
    <tr>
      <td class="kpi-card">
        <div class="kpi-label">Total Pendapatan</div>
        <div class="kpi-value green">Rp {{ number_format($stats['totalPendapatan'] ?? 0, 0, ',', '.') }}</div>
      </td>
      <td class="kpi-card">
        <div class="kpi-label">Total Pesanan</div>
        <div class="kpi-value">{{ number_format($stats['totalPesanan'] ?? 0, 0, ',', '.') }}</div>
      </td>
      <td class="kpi-card">
        <div class="kpi-label">Tiket Terjual</div>
        <div class="kpi-value green">{{ number_format($stats['totalTiketTerjual'] ?? 0, 0, ',', '.') }}</div>
      </td>
    </tr>
    <tr>
      <td class="kpi-card">
        <div class="kpi-label">Total Pengguna</div>
        <div class="kpi-value">{{ number_format($stats['totalPengguna'] ?? 0, 0, ',', '.') }}</div>
      </td>
      <td class="kpi-card">
        <div class="kpi-label">Total Event</div>
        <div class="kpi-value">{{ number_format($stats['totalEvent'] ?? 0, 0, ',', '.') }}</div>
      </td>
      <td class="kpi-card">
        <div class="kpi-label">Tiket Terpakai</div>
        <div class="kpi-value">{{ number_format($stats['tiketTerpakai'] ?? 0, 0, ',', '.') }}</div>
      </td>
    </tr>
    <tr>
      <td class="kpi-card">
        <div class="kpi-label">Tiket Belum Digunakan</div>
        <div class="kpi-value">{{ number_format($stats['tiketBelum'] ?? 0, 0, ',', '.') }}</div>
      </td>
      <td class="kpi-card">
        <div class="kpi-label">Pesanan Pending</div>
        <div class="kpi-value orange">{{ number_format($stats['pesananPending'] ?? 0, 0, ',', '.') }}</div>
      </td>
      <td class="kpi-card">
        <div class="kpi-label">Pesanan Dibayar</div>
        <div class="kpi-value green">{{ number_format($stats['pesananDibayar'] ?? 0, 0, ',', '.') }}</div>
      </td>
    </tr>
  </table>

  <table class="two-col">
    <tr>
      <td>
        <div class="section-title">PENJUALAN BULANAN (12 BULAN)</div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Bulan</th>
              <th style="text-align:center;">Pesanan</th>
              <th style="text-align:right;">Pendapatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grafikBulanan as $b)
              <tr>
                <td>{{ $b['label'] }}</td>
                <td style="text-align:center;">{{ number_format($b['pesanan'], 0, ',', '.') }}</td>
                <td style="text-align:right;">Rp {{ number_format($b['pendapatan'], 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </td>
      <td>
        <div class="section-title">PESANAN HARIAN (30 HARI TERAKHIR)</div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th style="text-align:center;">Pesanan</th>
              <th style="text-align:right;">Pendapatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grafikHarian as $h)
              <tr>
                <td>{{ $h['label'] }}</td>
                <td style="text-align:center;">{{ number_format($h['pesanan'], 0, ',', '.') }}</td>
                <td style="text-align:right;">Rp {{ number_format($h['pendapatan'], 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </td>
    </tr>
  </table>

  <div class="footer">
    InfoMusikBDG Laporan &amp; Statistik &bull; Dicetak: {{ $generated }}
  </div>
</body>
</html>
