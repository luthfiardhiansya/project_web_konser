<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1f2937; background: #fff; }
  .header { background: #7c3aed; color: white; padding: 14px 18px; margin-bottom: 16px; }
  .header h1 { font-size: 15px; font-weight: 700; letter-spacing: 0.5px; }
  .header p  { font-size: 8px; margin-top: 3px; opacity: 0.85; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  thead tr { background: #5b21b6; color: #fff; }
  thead th { padding: 6px 7px; text-align: left; font-size: 8px; font-weight: 700; border: 1px solid #6d28d9; white-space: nowrap; }
  tbody tr { border-bottom: 1px solid #e5e7eb; }
  tbody tr:nth-child(even) { background: #f5f3ff; }
  tbody td { padding: 5px 7px; font-size: 8.5px; vertical-align: top; }
  .footer { font-size: 7.5px; color: #9ca3af; text-align: right; margin-top: 8px; border-top: 1px solid #e5e7eb; padding-top: 5px; }
  .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7.5px; font-weight: 700; }
  .badge-success { background: #d1fae5; color: #065f46; }
  .badge-warning { background: #fef3c7; color: #92400e; }
  .badge-danger  { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>
  <div class="header">
    <h1>{{ strtoupper($title) }}</h1>
    <p>Dicetak: {{ $generated }} | InfoMusikBDG Admin Panel</p>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        @foreach($columns as $col)
          <th>{{ $col }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @forelse($data as $i => $row)
        <tr>
          <td>{{ $i + 1 }}</td>
          @foreach(array_values((array)$row) as $val)
            <td>{{ $val }}</td>
          @endforeach
        </tr>
      @empty
        <tr><td colspan="{{ count($columns) + 1 }}" style="text-align:center;padding:12px;color:#9ca3af;">Tidak ada data</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="footer">
    Total: {{ count($data) }} baris data &bull; InfoMusikBDG &copy; {{ date('Y') }}
  </div>
</body>
</html>
