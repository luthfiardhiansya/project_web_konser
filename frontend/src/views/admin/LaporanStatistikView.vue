<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

// ─── Tab aktif ────────────────────────────────────────────────────────────────
const activeTab = ref('statistik')
const TABS = [
  { key: 'statistik', label: 'Dashboard Statistik', icon: 'fa-chart-pie' },
  { key: 'penjualan', label: 'Laporan Penjualan',   icon: 'fa-receipt' },
  { key: 'event',     label: 'Laporan Event',        icon: 'fa-calendar-days' },
  { key: 'tiket',     label: 'Laporan Tiket',        icon: 'fa-ticket' },
  { key: 'scan',      label: 'Laporan Scan QR',      icon: 'fa-qrcode' },
  { key: 'pengguna',  label: 'Laporan Pengguna',     icon: 'fa-users' },
]

// ─── Loading ──────────────────────────────────────────────────────────────────
const loading = ref(false)
const exporting = ref(false)

// ─── Filter penjualan ─────────────────────────────────────────────────────────
const filterPenjualan = ref({ date_from: '', date_to: '', event_id: '', status: '' })
const filterScan      = ref({ date_from: '', date_to: '', event_id: '' })
const events          = ref([])

// ─── Data per tab ─────────────────────────────────────────────────────────────
const statistik  = ref(null)
const penjualan  = ref([])
const laporanEvent = ref([])
const laporanTiket = ref([])
const laporanScan  = ref([])
const scanStats    = ref({})
const laporanUser  = ref([])
const userStats    = ref({})

// ─── Canvas refs ──────────────────────────────────────────────────────────────
const canvasBulanan  = ref(null)
const canvasHarian   = ref(null)
const canvasDoughnut = ref(null)
const canvasScanEvent = ref(null)

// ─── Format helpers ───────────────────────────────────────────────────────────
const formatRp = (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(v) || 0)
const formatNum = (v) => new Intl.NumberFormat('id-ID').format(Number(v) || 0)

// ─── Fetch ────────────────────────────────────────────────────────────────────
const fetchEvents = async () => {
  const res = await api.get('/events')
  events.value = res.data.data || res.data || []
}

const fetchStatistik = async () => {
  loading.value = true
  try {
    const res = await api.get('/reports/statistik')
    statistik.value = res.data.data
  } catch { showFlash('Gagal memuat statistik.', 'error', 'ERROR') }
  finally {
    loading.value = false
    await nextTick()
    drawCharts()
  }
}

const fetchPenjualan = async () => {
  loading.value = true
  try {
    const res = await api.get('/reports/penjualan', { params: filterPenjualan.value })
    penjualan.value = res.data.data || []
  } catch { showFlash('Gagal memuat laporan penjualan.', 'error', 'ERROR') }
  finally { loading.value = false }
}

const fetchEvent = async () => {
  loading.value = true
  try {
    const res = await api.get('/reports/event')
    laporanEvent.value = res.data.data || []
  } catch { showFlash('Gagal memuat laporan event.', 'error', 'ERROR') }
  finally { loading.value = false }
}

const fetchTiket = async () => {
  loading.value = true
  try {
    const res = await api.get('/reports/tiket')
    laporanTiket.value = res.data.data || []
  } catch { showFlash('Gagal memuat laporan tiket.', 'error', 'ERROR') }
  finally { loading.value = false }
}

const fetchScan = async () => {
  loading.value = true
  try {
    const res = await api.get('/reports/scan', { params: filterScan.value })
    laporanScan.value  = res.data.data  || []
    scanStats.value    = res.data.stats || {}
  } catch { showFlash('Gagal memuat laporan scan.', 'error', 'ERROR') }
  finally {
    loading.value = false
    await nextTick()
    drawScanEventChart()
  }
}

const fetchPengguna = async () => {
  loading.value = true
  try {
    const res = await api.get('/reports/pengguna')
    laporanUser.value = res.data.data  || []
    userStats.value   = res.data.stats || {}
  } catch { showFlash('Gagal memuat laporan pengguna.', 'error', 'ERROR') }
  finally { loading.value = false }
}

const loadTab = (key) => {
  const map = {
    statistik : fetchStatistik,
    penjualan : fetchPenjualan,
    event     : fetchEvent,
    tiket     : fetchTiket,
    scan      : fetchScan,
    pengguna  : fetchPengguna,
  }
  map[key]?.()
}

watch(activeTab, (key) => loadTab(key))

onMounted(async () => {
  await fetchEvents()
  loadTab(activeTab.value)
})

// ─── Export helpers ───────────────────────────────────────────────────────────
const doExport = async (type, fmt) => {
  if (exporting.value) return
  exporting.value = true
  try {
    const params = type === 'penjualan' ? filterPenjualan.value
                 : type === 'scan'      ? filterScan.value
                 : {}

    const res = await api.get(`/reports/export/${fmt}/${type}`, {
      params,
      responseType: 'blob',
    })

    const mime = fmt === 'pdf'
      ? 'application/pdf'
      : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    const ext  = fmt === 'pdf' ? 'pdf' : 'xlsx'

    const blob = new Blob([res.data], { type: mime })
    const url  = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href     = url
    link.download = `laporan_${type}_${new Date().toISOString().slice(0, 10)}.${ext}`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    showFlash(`Export ${fmt.toUpperCase()} berhasil!`, 'success', 'EXPORT BERHASIL')
  } catch (err) {
    console.error('Export gagal:', err)
    showFlash('Gagal mengekspor laporan.', 'error', 'EXPORT GAGAL')
  } finally {
    exporting.value = false
  }
}

// ─── Canvas drawing ───────────────────────────────────────────────────────────
function niceStep(max) {
  if (max <= 5)   return 1
  if (max <= 20)  return 5
  if (max <= 50)  return 10
  const p = Math.pow(10, Math.floor(Math.log10(max)))
  return Math.ceil(max / p) * p > max ? p : p * 2
}

function fmtCompact(v) {
  if (v >= 1e9) return (v / 1e9).toFixed(1) + 'M'
  if (v >= 1e6) return (v / 1e6).toFixed(1) + 'jt'
  if (v >= 1e3) return (v / 1e3).toFixed(0) + 'rb'
  return String(Math.round(v))
}

function drawBar(canvas, labels, datasets) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const W = canvas.width  = canvas.offsetWidth  || 600
  const H = canvas.height = canvas.offsetHeight || 220
  const P = { t: 20, r: 16, b: 42, l: 56 }
  const cW = W - P.l - P.r, cH = H - P.t - P.b
  ctx.clearRect(0, 0, W, H)

  const all  = datasets.flatMap(d => d.data)
  const maxV = Math.max(...all, 1)
  const top  = Math.ceil(maxV / niceStep(maxV)) * niceStep(maxV)

  ctx.strokeStyle = '#f3f4f6'; ctx.lineWidth = 1
  ctx.font = '10px system-ui'; ctx.fillStyle = '#9ca3af'; ctx.textAlign = 'right'
  for (let i = 0; i <= 5; i++) {
    const v = (top / 5) * i, y = P.t + cH - (v / top) * cH
    ctx.beginPath(); ctx.moveTo(P.l, y); ctx.lineTo(P.l + cW, y); ctx.stroke()
    ctx.fillText(fmtCompact(v), P.l - 6, y + 4)
  }

  const n = labels.length, gW = cW / n, bCnt = datasets.length
  const pad = gW * 0.15, bW = (gW - pad * 2) / bCnt

  datasets.forEach((ds, di) => {
    ds.data.forEach((v, i) => {
      const bH = (v / top) * cH
      const x  = P.l + i * gW + pad + di * bW
      const y  = P.t + cH - bH
      ctx.fillStyle = ds.color
      ctx.beginPath()
      const r = Math.min(3, bW / 2, bH || 1)
      if (bH > 0) {
        ctx.moveTo(x + r, y); ctx.lineTo(x + bW - r, y)
        ctx.quadraticCurveTo(x + bW, y, x + bW, y + r)
        ctx.lineTo(x + bW, y + bH); ctx.lineTo(x, y + bH); ctx.lineTo(x, y + r)
        ctx.quadraticCurveTo(x, y, x + r, y)
      }
      ctx.fill()
    })
  })

  ctx.fillStyle = '#6b7280'; ctx.font = '10px system-ui'; ctx.textAlign = 'center'
  labels.forEach((l, i) => ctx.fillText(l, P.l + i * gW + gW / 2, P.t + cH + 16))

  // legend
  let lx = P.l
  datasets.forEach(ds => {
    ctx.fillStyle = ds.color; ctx.fillRect(lx, H - 10, 10, 3)
    ctx.fillStyle = '#374151'; ctx.textAlign = 'left'
    ctx.fillText(ds.label, lx + 13, H - 4)
    lx += ctx.measureText(ds.label).width + 28
  })
}

function drawDonut(canvas, labels, values, colors) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const W = canvas.width  = canvas.offsetWidth  || 300
  const H = canvas.height = canvas.offsetHeight || 220
  ctx.clearRect(0, 0, W, H)

  const total = values.reduce((s, v) => s + v, 0)
  const cx = W * 0.38, cy = H / 2
  const R = Math.min(cx, cy) - 12, r = R * 0.55

  if (!total) {
    ctx.fillStyle = '#e5e7eb'; ctx.beginPath(); ctx.arc(cx, cy, R, 0, Math.PI * 2); ctx.fill()
    ctx.fillStyle = '#fff';    ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.fill()
    return
  }

  let start = -Math.PI / 2
  values.forEach((v, i) => {
    const s = (v / total) * Math.PI * 2
    ctx.beginPath(); ctx.moveTo(cx, cy)
    ctx.arc(cx, cy, R, start, start + s)
    ctx.closePath(); ctx.fillStyle = colors[i]; ctx.fill()
    start += s
  })
  ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.fillStyle = '#fff'; ctx.fill()
  ctx.fillStyle = '#111'; ctx.font = 'bold 16px system-ui'; ctx.textAlign = 'center'
  ctx.fillText(total, cx, cy + 3)
  ctx.fillStyle = '#6b7280'; ctx.font = '10px system-ui'; ctx.fillText('total', cx, cy + 15)

  const lX = W * 0.72, lH = 24, sY = cy - ((labels.length - 1) * lH) / 2
  labels.forEach((lbl, i) => {
    const y = sY + i * lH, pct = total ? ((values[i] / total) * 100).toFixed(1) : '0.0'
    ctx.fillStyle = colors[i]
    try { ctx.roundRect(lX - 14, y - 7, 10, 10, 3); ctx.fill() } catch { ctx.fillRect(lX - 14, y - 7, 10, 10) }
    ctx.fillStyle = '#374151'; ctx.font = '10px system-ui'; ctx.textAlign = 'left'
    ctx.fillText(lbl, lX, y + 2)
    ctx.fillStyle = '#6b7280'; ctx.font = '9px system-ui'
    ctx.fillText(`${values[i]} (${pct}%)`, lX, y + 13)
  })
}

function drawCharts() {
  if (!statistik.value) return
  const s = statistik.value

  drawBar(canvasBulanan.value,
    s.grafikBulanan.map(x => x.label),
    [
      { label: 'Pesanan',    data: s.grafikBulanan.map(x => x.pesanan),    color: '#7c3aed' },
      { label: 'Pendapatan', data: s.grafikBulanan.map(x => x.pendapatan / 1000), color: '#10b981' },
    ]
  )
  drawBar(canvasHarian.value,
    s.grafikHarian.map(x => x.label),
    [{ label: 'Pesanan / hari', data: s.grafikHarian.map(x => x.pesanan), color: '#0ea5e9' }]
  )
  drawDonut(canvasDoughnut.value,
    ['Pending', 'Dibayar/Selesai'],
    [s.pesananPending, s.pesananDibayar],
    ['#f59e0b', '#10b981']
  )
}

function drawScanEventChart() {
  if (!canvasScanEvent.value || !scanStats.value?.scanPerEvent) return
  const entries = Object.entries(scanStats.value.scanPerEvent)
  drawBar(canvasScanEvent.value,
    entries.map(([k]) => k.length > 12 ? k.slice(0, 12) + '…' : k),
    [{ label: 'Scan per Event', data: entries.map(([, v]) => v), color: '#7c3aed' }]
  )
}
</script>

<template>
  <AdminLayout>
    <div class="laporan-page">

      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Laporan &amp; Statistik</h1>
          <p class="text-muted text-sm">Ringkasan data, analitik, dan ekspor laporan</p>
        </div>
        <button @click="loadTab(activeTab)" :disabled="loading" class="btn btn-outline btn-sm">
          <svg :class="{ 'spin-icon': loading }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.09"/>
          </svg>
          Refresh
        </button>
      </div>

      <!-- Tabs -->
      <div class="tab-bar card mb-4">
        <button
          v-for="tab in TABS"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="tab-btn"
          :class="{ 'tab-btn-active': activeTab === tab.key }"
        >
          <i :class="'fa-solid ' + tab.icon"></i>
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- Loading overlay -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p class="text-muted text-sm mt-2">Memuat data...</p>
      </div>

      <!-- ══════════════════════════════════════════════════════════
           TAB 1: STATISTIK
      ══════════════════════════════════════════════════════════ -->
      <template v-if="!loading && activeTab === 'statistik' && statistik">
        <div class="export-header card mb-3">
          <span class="text-muted text-sm font-semibold">Ringkasan Statistik Dashboard</span>
          <div class="export-btns">
            <button @click="doExport('statistik','excel')" :disabled="exporting" class="btn-export excel">
              <i class="fa-solid fa-file-excel"></i> Excel
            </button>
            <button @click="doExport('statistik','pdf')" :disabled="exporting" class="btn-export pdf">
              <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
          </div>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid mb-4">
          <div class="stat-card">
            <div class="stat-icon" style="background:#f3e8ff;color:#7c3aed"><i class="fa-solid fa-sack-dollar"></i></div>
            <div>
              <span class="stat-label">Total Pendapatan</span>
              <strong class="stat-val revenue-val">{{ formatRp(statistik.totalPendapatan) }}</strong>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7"><i class="fa-solid fa-cart-shopping"></i></div>
            <div><span class="stat-label">Total Pesanan</span><strong class="stat-val">{{ formatNum(statistik.totalPesanan) }}</strong></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="fa-solid fa-ticket"></i></div>
            <div><span class="stat-label">Tiket Terjual</span><strong class="stat-val">{{ formatNum(statistik.totalTiketTerjual) }}</strong></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#ffedd5;color:#ea580c"><i class="fa-solid fa-users"></i></div>
            <div><span class="stat-label">Total Pengguna</span><strong class="stat-val">{{ formatNum(statistik.totalPengguna) }}</strong></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#f3e8ff;color:#7c3aed"><i class="fa-solid fa-calendar-days"></i></div>
            <div><span class="stat-label">Total Event</span><strong class="stat-val">{{ formatNum(statistik.totalEvent) }}</strong></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#ccfbf1;color:#0d9488"><i class="fa-solid fa-money-check-dollar"></i></div>
            <div><span class="stat-label">Tiket Terpakai</span><strong class="stat-val">{{ formatNum(statistik.tiketTerpakai) }}</strong></div>
          </div>
        </div>

        <!-- Charts row 1 -->
        <div class="charts-row mb-4">
          <div class="chart-card chart-wide">
            <div class="chart-hdr">
              <div><p class="chart-title">Penjualan per Bulan (12 bln)</p><p class="chart-sub">Jumlah pesanan & pendapatan bulanan</p></div>
            </div>
            <div class="chart-wrap"><canvas ref="canvasBulanan"></canvas></div>
          </div>
          <div class="chart-card">
            <div class="chart-hdr">
              <div><p class="chart-title">Status Pesanan</p><p class="chart-sub">Distribusi pending vs dibayar</p></div>
            </div>
            <div class="chart-wrap"><canvas ref="canvasDoughnut"></canvas></div>
          </div>
        </div>

        <!-- Charts row 2 -->
        <div class="chart-card mb-4">
          <div class="chart-hdr">
            <div><p class="chart-title">Pesanan Harian (30 hari terakhir)</p><p class="chart-sub">Tren pesanan per hari</p></div>
          </div>
          <div class="chart-wrap" style="height:200px"><canvas ref="canvasHarian"></canvas></div>
        </div>

        <!-- Tiket cards -->
        <div class="mini-stats-row">
          <div class="mini-card">
            <p class="mini-label">Tiket Belum Digunakan</p>
            <p class="mini-val">{{ formatNum(statistik.tiketBelum) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Tiket Terpakai</p>
            <p class="mini-val" style="color:#16a34a">{{ formatNum(statistik.tiketTerpakai) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Pesanan Pending</p>
            <p class="mini-val" style="color:#d97706">{{ formatNum(statistik.pesananPending) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Pesanan Dibayar</p>
            <p class="mini-val" style="color:#16a34a">{{ formatNum(statistik.pesananDibayar) }}</p>
          </div>
        </div>
      </template>

      <!-- ══════════════════════════════════════════════════════════
           TAB 2: PENJUALAN
      ══════════════════════════════════════════════════════════ -->
      <template v-if="!loading && activeTab === 'penjualan'">
        <div class="filter-bar card mb-3">
          <div class="filter-bar-inner">
            <div class="date-filter-group"><label class="date-label">Dari</label><input type="date" v-model="filterPenjualan.date_from" class="form-control filter-date"/></div>
            <div class="date-filter-group"><label class="date-label">Sampai</label><input type="date" v-model="filterPenjualan.date_to" class="form-control filter-date"/></div>
            <select v-model="filterPenjualan.event_id" class="form-control filter-select">
              <option value="">Semua Event</option>
              <option v-for="e in events" :key="e.id" :value="e.id">{{ e.nama_event }}</option>
            </select>
            <select v-model="filterPenjualan.status" class="form-control filter-select">
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="dibayar">Dibayar</option>
              <option value="selesai">Selesai</option>
              <option value="dibatalkan">Dibatalkan</option>
            </select>
            <button @click="fetchPenjualan" class="btn btn-primary btn-sm">Tampilkan</button>
          </div>
          <div class="export-row">
            <span class="text-muted text-sm">{{ penjualan.length }} data</span>
            <div class="export-btns">
              <button @click="doExport('penjualan','excel')" class="btn-export excel">
                <i class="fa-solid fa-file-excel"></i> Excel
              </button>
              <button @click="doExport('penjualan','pdf')" class="btn-export pdf">
                <i class="fa-solid fa-file-pdf"></i> PDF
              </button>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="table-container">
            <table class="table-custom">
              <thead><tr>
                <th>Kode Pesanan</th><th>Nama Pembeli</th><th>Event</th><th>Jenis Tiket</th>
                <th>Jml</th><th>Total Harga</th><th>Status</th><th>Tanggal</th>
              </tr></thead>
              <tbody>
                <tr v-for="r in penjualan" :key="r.id">
                  <td><strong class="font-semibold">{{ r.kode_pesanan }}</strong></td>
                  <td>{{ r.nama_pembeli }}</td>
                  <td class="text-sm text-muted">{{ r.event }}</td>
                  <td class="text-sm">{{ r.jenis_tiket }}</td>
                  <td>{{ r.jumlah_tiket }}</td>
                  <td class="font-semibold">{{ formatRp(r.total_harga) }}</td>
                  <td><span :class="['badge', r.status_pembayaran==='berhasil'||r.status_pembayaran==='dibayar'?'badge-success':r.status_pembayaran==='pending'?'badge-warning':'badge-danger']">{{ r.status_pembayaran }}</span></td>
                  <td class="text-sm text-muted">{{ r.tanggal_pembelian }}</td>
                </tr>
                <tr v-if="!penjualan.length"><td colspan="8" class="text-center text-muted py-4">Belum ada data.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ══════════════════════════════════════════════════════════
           TAB 3: LAPORAN EVENT
      ══════════════════════════════════════════════════════════ -->
      <template v-if="!loading && activeTab === 'event'">
        <div class="export-header card mb-3">
          <span class="text-muted text-sm">{{ laporanEvent.length }} event</span>
          <div class="export-btns">
            <button @click="doExport('event','excel')" class="btn-export excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
            <button @click="doExport('event','pdf')" class="btn-export pdf"><i class="fa-solid fa-file-pdf"></i> PDF</button>
          </div>
        </div>
        <div class="card">
          <div class="table-container">
            <table class="table-custom">
              <thead><tr>
                <th>Nama Event</th><th>Tanggal</th><th>Lokasi</th>
                <th>Tersedia</th><th>Terjual</th><th>Tersisa</th><th>Pendapatan</th><th>Status</th>
              </tr></thead>
              <tbody>
                <tr v-for="r in laporanEvent" :key="r.id">
                  <td><strong class="font-semibold">{{ r.nama_event }}</strong></td>
                  <td class="text-sm">{{ r.tanggal }}</td>
                  <td class="text-sm text-muted">{{ r.lokasi }}</td>
                  <td>{{ r.tiket_tersedia }}</td>
                  <td class="font-semibold" style="color:#16a34a">{{ r.tiket_terjual }}</td>
                  <td>{{ r.tiket_tersisa }}</td>
                  <td class="font-semibold">{{ formatRp(r.total_pendapatan) }}</td>
                  <td><span :class="['badge', r.status==='aktif'?'badge-success':r.status==='selesai'?'badge-neutral':'badge-danger']">{{ r.status }}</span></td>
                </tr>
                <tr v-if="!laporanEvent.length"><td colspan="8" class="text-center text-muted py-4">Belum ada data.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ══════════════════════════════════════════════════════════
           TAB 4: LAPORAN TIKET
      ══════════════════════════════════════════════════════════ -->
      <template v-if="!loading && activeTab === 'tiket'">
        <div class="export-header card mb-3">
          <span class="text-muted text-sm">{{ laporanTiket.length }} tiket</span>
          <div class="export-btns">
            <button @click="doExport('tiket','excel')" class="btn-export excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
            <button @click="doExport('tiket','pdf')" class="btn-export pdf"><i class="fa-solid fa-file-pdf"></i> PDF</button>
          </div>
        </div>
        <div class="card">
          <div class="table-container">
            <table class="table-custom">
              <thead><tr>
                <th>Jenis Tiket</th><th>Event</th><th>Harga</th>
                <th>Stok Awal</th><th>Terjual</th><th>Tersisa</th><th>Digunakan</th><th>Belum</th>
              </tr></thead>
              <tbody>
                <tr v-for="r in laporanTiket" :key="r.id">
                  <td><span class="badge badge-purple">{{ r.jenis_tiket }}</span></td>
                  <td class="text-sm text-muted">{{ r.event }}</td>
                  <td class="font-semibold">{{ formatRp(r.harga) }}</td>
                  <td>{{ r.stok_awal }}</td>
                  <td class="font-semibold" style="color:#16a34a">{{ r.terjual }}</td>
                  <td>{{ r.tersisa }}</td>
                  <td style="color:#7c3aed">{{ r.digunakan }}</td>
                  <td class="text-muted">{{ r.belum_digunakan }}</td>
                </tr>
                <tr v-if="!laporanTiket.length"><td colspan="8" class="text-center text-muted py-4">Belum ada data.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ══════════════════════════════════════════════════════════
           TAB 5: LAPORAN SCAN QR
      ══════════════════════════════════════════════════════════ -->
      <template v-if="!loading && activeTab === 'scan'">

        <!-- Mini stats scan -->
        <div class="mini-stats-row mb-3">
          <div class="mini-card">
            <p class="mini-label">Total Discan</p>
            <p class="mini-val">{{ formatNum(scanStats.totalScan) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Tiket Valid (belum scan)</p>
            <p class="mini-val" style="color:#0284c7">{{ formatNum(scanStats.tiketValid) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Scan Hari Ini</p>
            <p class="mini-val" style="color:#7c3aed">{{ formatNum(scanStats.scanHariIni) }}</p>
          </div>
        </div>

        <!-- Grafik scan per event -->
        <div class="chart-card mb-3">
          <p class="chart-title mb-2">Scan per Event</p>
          <div class="chart-wrap" style="height:190px"><canvas ref="canvasScanEvent"></canvas></div>
        </div>

        <!-- Filter -->
        <div class="filter-bar card mb-3">
          <div class="filter-bar-inner">
            <div class="date-filter-group"><label class="date-label">Dari</label><input type="date" v-model="filterScan.date_from" class="form-control filter-date"/></div>
            <div class="date-filter-group"><label class="date-label">Sampai</label><input type="date" v-model="filterScan.date_to" class="form-control filter-date"/></div>
            <select v-model="filterScan.event_id" class="form-control filter-select">
              <option value="">Semua Event</option>
              <option v-for="e in events" :key="e.id" :value="e.id">{{ e.nama_event }}</option>
            </select>
            <button @click="fetchScan" class="btn btn-primary btn-sm">Tampilkan</button>
          </div>
          <div class="export-row">
            <span class="text-muted text-sm">{{ laporanScan.length }} scan</span>
            <div class="export-btns">
              <button @click="doExport('scan','excel')" class="btn-export excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
              <button @click="doExport('scan','pdf')" class="btn-export pdf"><i class="fa-solid fa-file-pdf"></i> PDF</button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="table-container">
            <table class="table-custom">
              <thead><tr>
                <th>Kode Tiket</th><th>Kode Pesanan</th><th>Nama Pembeli</th><th>Email</th>
                <th>Event</th><th>Jenis Tiket</th><th>Status</th><th>Waktu Scan</th><th>Scanner</th>
              </tr></thead>
              <tbody>
                <tr v-for="r in laporanScan" :key="r.id">
                  <td class="text-xs font-mono text-muted">{{ r.kode_tiket?.slice(0,12) }}…</td>
                  <td><strong class="font-semibold">{{ r.kode_pesanan }}</strong></td>
                  <td>{{ r.nama_pembeli }}</td>
                  <td class="text-sm text-muted">{{ r.email }}</td>
                  <td class="text-sm">{{ r.event }}</td>
                  <td><span class="badge badge-purple">{{ r.jenis_tiket }}</span></td>
                  <td><span class="badge badge-success">{{ r.status }}</span></td>
                  <td class="text-sm text-muted">{{ r.waktu_scan }}</td>
                  <td class="text-sm">{{ r.scanner }}</td>
                </tr>
                <tr v-if="!laporanScan.length"><td colspan="9" class="text-center text-muted py-4">Belum ada data scan.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- ══════════════════════════════════════════════════════════
           TAB 6: LAPORAN PENGGUNA
      ══════════════════════════════════════════════════════════ -->
      <template v-if="!loading && activeTab === 'pengguna'">
        <div class="mini-stats-row mb-3">
          <div class="mini-card">
            <p class="mini-label">Total Pengguna</p>
            <p class="mini-val">{{ formatNum(userStats.totalPengguna) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Pengguna Baru (bulan ini)</p>
            <p class="mini-val" style="color:#7c3aed">{{ formatNum(userStats.penggunaBaru) }}</p>
          </div>
          <div class="mini-card">
            <p class="mini-label">Pengguna Aktif (pernah pesan)</p>
            <p class="mini-val" style="color:#16a34a">{{ formatNum(userStats.penggunaAktif) }}</p>
          </div>
        </div>

        <div class="export-header card mb-3">
          <span class="text-muted text-sm">{{ laporanUser.length }} pengguna</span>
          <div class="export-btns">
            <button @click="doExport('pengguna','excel')" class="btn-export excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
            <button @click="doExport('pengguna','pdf')" class="btn-export pdf"><i class="fa-solid fa-file-pdf"></i> PDF</button>
          </div>
        </div>
        <div class="card">
          <div class="table-container">
            <table class="table-custom">
              <thead><tr>
                <th>Nama</th><th>Email</th><th>Role</th><th>Bergabung</th><th>Total Pesanan</th><th>Total Pembelian</th>
              </tr></thead>
              <tbody>
                <tr v-for="r in laporanUser" :key="r.id">
                  <td><strong class="font-semibold">{{ r.nama }}</strong></td>
                  <td class="text-muted text-sm">{{ r.email }}</td>
                  <td><span :class="['badge', r.role==='admin'?'badge-purple':'badge-neutral']">{{ r.role }}</span></td>
                  <td class="text-sm text-muted">{{ r.bergabung }}</td>
                  <td>{{ r.total_pesanan }}</td>
                  <td class="font-semibold">{{ formatRp(r.total_pembelian) }}</td>
                </tr>
                <tr v-if="!laporanUser.length"><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

    </div>
  </AdminLayout>
</template>

<style scoped>
.laporan-page { max-width: 1400px; }

.page-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.25rem;
}
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }

/* Tabs */
.tab-bar {
  display: flex; gap: 0; padding: 0; overflow-x: auto;
  border-radius: 10px; overflow: hidden;
}
.tab-btn {
  display: inline-flex; align-items: center; gap: 0.45rem;
  padding: 0.7rem 1rem; font-size: 0.82rem; font-weight: 600;
  border: none; cursor: pointer; white-space: nowrap;
  background: var(--card-bg, #fff); color: var(--text-muted, #888);
  border-right: 1px solid var(--border-color, #e5e7eb);
  transition: all 0.15s;
}
.tab-btn:last-child { border-right: none; }
.tab-btn:hover { background: var(--bg-subtle, #f9fafb); color: var(--text-main, #222); }
.tab-btn-active { background: var(--primary, #7c3aed) !important; color: #fff !important; }

/* Stats */
.stats-grid {
  display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.9rem;
}
.stat-card {
  background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px; padding: 1rem 1.1rem;
  display: flex; align-items: center; gap: 0.85rem;
}
.stat-icon {
  width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.stat-label { display: block; font-size: 0.74rem; color: #6b7280; margin-bottom: 0.2rem; }
.stat-val { display: block; font-size: 1.4rem; font-weight: 700; color: var(--text-main, #111); line-height: 1.1; }
.revenue-val { font-size: 0.95rem !important; }

/* Mini stats */
.mini-stats-row { display: flex; gap: 1rem; flex-wrap: wrap; }
.mini-card {
  flex: 1; min-width: 150px; background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb); border-radius: 10px; padding: 0.9rem 1rem;
}
.mini-label { font-size: 0.74rem; color: #6b7280; margin-bottom: 0.2rem; }
.mini-val   { font-size: 1.5rem; font-weight: 700; }

/* Charts */
.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.chart-wide { grid-column: span 1; }
.chart-card {
  background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px; padding: 1.1rem;
}
.chart-hdr { display: flex; justify-content: space-between; margin-bottom: 0.9rem; }
.chart-title { font-size: 0.9rem; font-weight: 600; color: var(--text-main, #111); margin: 0; }
.chart-sub   { font-size: 0.76rem; color: #6b7280; margin: 0.15rem 0 0; }
.chart-wrap  { width: 100%; height: 210px; }
.chart-wrap canvas { width: 100% !important; height: 100% !important; display: block; }

/* Filter bar */
.filter-bar { padding: 0.75rem 1rem 0.6rem; }
.filter-bar-inner { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
.filter-select { max-width: 180px; min-width: 140px; }
.date-filter-group { display: flex; align-items: center; gap: 0.35rem; }
.date-label { font-size: 0.78rem; color: #888; white-space: nowrap; }
.filter-date { max-width: 145px; }

/* Export */
.export-header {
  display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 1rem;
}
.export-row {
  display: flex; align-items: center; justify-content: space-between; padding-top: 0.4rem;
}
.export-btns { display: flex; gap: 0.4rem; }
.btn-export {
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.35rem 0.8rem; font-size: 0.78rem; font-weight: 600;
  border: 1px solid; border-radius: 6px; cursor: pointer; transition: all 0.15s;
}
.btn-export.excel {
  background: #dcfce7; color: #166534; border-color: #86efac;
}
.btn-export.excel:hover { background: #16a34a; color: #fff; }
.btn-export.pdf {
  background: #fee2e2; color: #991b1b; border-color: #fca5a5;
}
.btn-export.pdf:hover { background: #dc2626; color: #fff; }

/* Loading */
.loading-state { padding: 3rem; text-align: center; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin-icon { animation: spin 0.8s linear infinite; }

/* Responsive */
@media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 800px)  { .stats-grid { grid-template-columns: repeat(2,1fr); } .charts-row { grid-template-columns: 1fr; } }
@media (max-width: 480px)  { .stats-grid { grid-template-columns: 1fr; } }
</style>
