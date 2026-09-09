<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'

// ─── State ────────────────────────────────────────────────────────────────────
const loading = ref(true)

const stats = ref({
  totalEvent: 0,
  totalTiket: 0,
  totalPesanan: 0,
  totalPengguna: 0,
  totalPembayaran: 0,
  totalKategori: 0,
  pendapatanTotal: 0,
  pesananBerhasil: 0,
})

const events     = ref([])
const orders     = ref([])
const payments   = ref([])
const recentOrders = ref([])

// ─── Canvas refs ──────────────────────────────────────────────────────────────
const canvasEventBulanan  = ref(null)
const canvasPesananBulanan = ref(null)
const canvasStatusPembayaran = ref(null)
const canvasPesananStatus = ref(null)

// ─── Helpers ──────────────────────────────────────────────────────────────────
const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

const formatRupiah = (val) =>
  'Rp ' + new Intl.NumberFormat('id-ID').format(Number(val) || 0)

const formatDate = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

// ─── Fetch ────────────────────────────────────────────────────────────────────
const fetchAll = async () => {
  loading.value = true
  try {
    const [evRes, tkRes, orRes, pyRes, usRes, catRes] = await Promise.all([
      api.get('/events'),
      api.get('/tickets'),
      api.get('/orders'),
      api.get('/payments'),
      api.get('/users'),
      api.get('/categories'),
    ])

    events.value   = evRes.data.data   || evRes.data   || []
    const tickets  = tkRes.data.data   || tkRes.data   || []
    orders.value   = orRes.data.data   || orRes.data   || []
    payments.value = pyRes.data.data   || pyRes.data   || []
    const users    = usRes.data.data   || usRes.data   || []
    const cats     = catRes.data.data  || catRes.data  || []

    const paid = payments.value.filter(p => p.status === 'berhasil')

    stats.value = {
      totalEvent      : events.value.length,
      totalTiket      : tickets.length,
      totalPesanan    : orders.value.length,
      totalPengguna   : users.length,
      totalPembayaran : payments.value.length,
      totalKategori   : cats.length,
      pendapatanTotal : paid.reduce((s, p) => s + Number(p.jumlah_bayar || 0), 0),
      pesananBerhasil : paid.length,
    }

    recentOrders.value = [...orders.value]
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 6)

    await nextTick()
    drawCharts()
  } catch (e) {
    console.error('Gagal fetch dashboard:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)

// ─── Chart helpers ────────────────────────────────────────────────────────────
function getThisYearMonthlyCount(items, dateField) {
  const year = new Date().getFullYear()
  const counts = Array(12).fill(0)
  items.forEach(item => {
    const d = new Date(item[dateField])
    if (d.getFullYear() === year) counts[d.getMonth()]++
  })
  return counts
}

function getThisYearMonthlySum(items, dateField, valueField) {
  const year = new Date().getFullYear()
  const sums = Array(12).fill(0)
  items.forEach(item => {
    const d = new Date(item[dateField])
    if (d.getFullYear() === year) sums[d.getMonth()] += Number(item[valueField] || 0)
  })
  return sums
}

// ─── Canvas drawing ───────────────────────────────────────────────────────────
function drawBarChart(canvas, labels, datasets, options = {}) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const W = canvas.width  = canvas.offsetWidth  || 600
  const H = canvas.height = canvas.offsetHeight || 240

  const PAD   = { top: 24, right: 20, bottom: 48, left: 58 }
  const chartW = W - PAD.left - PAD.right
  const chartH = H - PAD.top  - PAD.bottom

  ctx.clearRect(0, 0, W, H)

  // background
  ctx.fillStyle = options.bgColor || 'transparent'
  ctx.fillRect(0, 0, W, H)

  const allValues = datasets.flatMap(d => d.data)
  const maxVal = Math.max(...allValues, 1)
  const step   = niceStep(maxVal)
  const topVal = Math.ceil(maxVal / step) * step

  // grid + y-axis labels
  const gridSteps = 5
  ctx.textAlign    = 'right'
  ctx.font         = '11px system-ui,sans-serif'
  ctx.fillStyle    = '#9ca3af'
  ctx.strokeStyle  = '#f3f4f6'
  ctx.lineWidth    = 1

  for (let i = 0; i <= gridSteps; i++) {
    const val = (topVal / gridSteps) * i
    const y   = PAD.top + chartH - (val / topVal) * chartH

    ctx.beginPath()
    ctx.moveTo(PAD.left, y)
    ctx.lineTo(PAD.left + chartW, y)
    ctx.stroke()

    ctx.fillStyle = '#9ca3af'
    const label = options.currency
      ? formatCompact(val)
      : String(Math.round(val))
    ctx.fillText(label, PAD.left - 8, y + 4)
  }

  // bars
  const n         = labels.length
  const groupW    = chartW / n
  const barCount  = datasets.length
  const barPad    = groupW * 0.18
  const barW      = (groupW - barPad * 2) / barCount

  datasets.forEach((ds, di) => {
    ds.data.forEach((val, i) => {
      const barH = (val / topVal) * chartH
      const x    = PAD.left + i * groupW + barPad + di * barW
      const y    = PAD.top  + chartH - barH

      // bar fill with rounded top
      ctx.fillStyle = ds.color
      ctx.beginPath()
      const r = Math.min(4, barW / 2, barH)
      if (barH > 0) {
        ctx.moveTo(x + r, y)
        ctx.lineTo(x + barW - r, y)
        ctx.quadraticCurveTo(x + barW, y, x + barW, y + r)
        ctx.lineTo(x + barW, y + barH)
        ctx.lineTo(x, y + barH)
        ctx.lineTo(x, y + r)
        ctx.quadraticCurveTo(x, y, x + r, y)
      }
      ctx.fill()

      // value label on bar
      if (barH > 14) {
        ctx.fillStyle   = '#fff'
        ctx.font        = 'bold 10px system-ui,sans-serif'
        ctx.textAlign   = 'center'
        const label = options.currency ? formatCompact(val) : String(val)
        ctx.fillText(label, x + barW / 2, y + 13)
      }
    })
  })

  // x-axis labels
  ctx.fillStyle  = '#6b7280'
  ctx.font       = '11px system-ui,sans-serif'
  ctx.textAlign  = 'center'
  labels.forEach((lbl, i) => {
    const x = PAD.left + i * groupW + groupW / 2
    ctx.fillText(lbl, x, PAD.top + chartH + 18)
  })

  // legend
  if (datasets.length > 1 || options.showLegend) {
    let lx = PAD.left
    datasets.forEach(ds => {
      ctx.fillStyle = ds.color
      ctx.fillRect(lx, PAD.top + chartH + 30, 10, 10)
      ctx.fillStyle  = '#374151'
      ctx.textAlign  = 'left'
      ctx.font       = '11px system-ui,sans-serif'
      ctx.fillText(ds.label, lx + 14, PAD.top + chartH + 40)
      lx += ctx.measureText(ds.label).width + 28
    })
  }
}

function drawDonutChart(canvas, labels, values, colors) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const W = canvas.width  = canvas.offsetWidth  || 320
  const H = canvas.height = canvas.offsetHeight || 240

  ctx.clearRect(0, 0, W, H)

  const total  = values.reduce((s, v) => s + v, 0)
  const cx     = W * 0.38
  const cy     = H / 2
  const radius = Math.min(cx, cy) - 16
  const inner  = radius * 0.55

  if (total === 0) {
    ctx.fillStyle = '#e5e7eb'
    ctx.beginPath()
    ctx.arc(cx, cy, radius, 0, Math.PI * 2)
    ctx.fill()
    ctx.fillStyle = '#fff'
    ctx.beginPath()
    ctx.arc(cx, cy, inner, 0, Math.PI * 2)
    ctx.fill()
    ctx.fillStyle   = '#9ca3af'
    ctx.font        = '12px system-ui,sans-serif'
    ctx.textAlign   = 'center'
    ctx.fillText('Belum ada data', cx, cy + 4)
    return
  }

  let startAngle = -Math.PI / 2
  values.forEach((val, i) => {
    const slice = (val / total) * Math.PI * 2
    ctx.beginPath()
    ctx.moveTo(cx, cy)
    ctx.arc(cx, cy, radius, startAngle, startAngle + slice)
    ctx.closePath()
    ctx.fillStyle = colors[i]
    ctx.fill()
    startAngle += slice
  })

  // inner circle (hole)
  ctx.beginPath()
  ctx.arc(cx, cy, inner, 0, Math.PI * 2)
  ctx.fillStyle = getComputedBg()
  ctx.fill()

  // center text
  ctx.fillStyle   = '#111827'
  ctx.font        = 'bold 18px system-ui,sans-serif'
  ctx.textAlign   = 'center'
  ctx.fillText(total, cx, cy + 2)
  ctx.fillStyle   = '#6b7280'
  ctx.font        = '11px system-ui,sans-serif'
  ctx.fillText('total', cx, cy + 16)

  // legend
  const legendX = W * 0.72
  const lineH   = 26
  const startY  = cy - ((labels.length - 1) * lineH) / 2

  labels.forEach((lbl, i) => {
    const y    = startY + i * lineH
    const pct  = total > 0 ? ((values[i] / total) * 100).toFixed(1) : '0.0'

    ctx.fillStyle = colors[i]
    ctx.beginPath()
    ctx.roundRect(legendX - 16, y - 7, 10, 10, 3)
    ctx.fill()

    ctx.fillStyle  = '#374151'
    ctx.font       = '11px system-ui,sans-serif'
    ctx.textAlign  = 'left'
    ctx.fillText(lbl, legendX, y + 2)

    ctx.fillStyle  = '#6b7280'
    ctx.font       = '10px system-ui,sans-serif'
    ctx.fillText(`${values[i]} (${pct}%)`, legendX, y + 14)
  })
}

function drawLineChart(canvas, labels, datasets, options = {}) {
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const W = canvas.width  = canvas.offsetWidth  || 600
  const H = canvas.height = canvas.offsetHeight || 220

  const PAD    = { top: 24, right: 24, bottom: 44, left: 60 }
  const chartW = W - PAD.left - PAD.right
  const chartH = H - PAD.top  - PAD.bottom

  ctx.clearRect(0, 0, W, H)

  const allVals = datasets.flatMap(d => d.data)
  const maxVal  = Math.max(...allVals, 1)
  const topVal  = Math.ceil(maxVal / niceStep(maxVal)) * niceStep(maxVal)

  // grid
  ctx.strokeStyle = '#f3f4f6'
  ctx.lineWidth   = 1
  ctx.fillStyle   = '#9ca3af'
  ctx.font        = '11px system-ui,sans-serif'
  ctx.textAlign   = 'right'

  for (let i = 0; i <= 5; i++) {
    const val = (topVal / 5) * i
    const y   = PAD.top + chartH - (val / topVal) * chartH
    ctx.beginPath(); ctx.moveTo(PAD.left, y); ctx.lineTo(PAD.left + chartW, y); ctx.stroke()
    const lbl = options.currency ? formatCompact(val) : String(Math.round(val))
    ctx.fillText(lbl, PAD.left - 8, y + 4)
  }

  // lines + fill
  const n = labels.length
  datasets.forEach(ds => {
    const pts = ds.data.map((val, i) => ({
      x: PAD.left + (i / (n - 1)) * chartW,
      y: PAD.top  + chartH - (val / topVal) * chartH,
    }))

    // gradient fill
    const grad = ctx.createLinearGradient(0, PAD.top, 0, PAD.top + chartH)
    grad.addColorStop(0, ds.color + '55')
    grad.addColorStop(1, ds.color + '00')

    ctx.beginPath()
    ctx.moveTo(pts[0].x, pts[0].y)
    for (let i = 1; i < pts.length; i++) {
      const cp1x = pts[i-1].x + (pts[i].x - pts[i-1].x) / 2
      ctx.bezierCurveTo(cp1x, pts[i-1].y, cp1x, pts[i].y, pts[i].x, pts[i].y)
    }
    ctx.lineTo(pts[pts.length-1].x, PAD.top + chartH)
    ctx.lineTo(pts[0].x, PAD.top + chartH)
    ctx.closePath()
    ctx.fillStyle = grad
    ctx.fill()

    // line
    ctx.beginPath()
    ctx.moveTo(pts[0].x, pts[0].y)
    for (let i = 1; i < pts.length; i++) {
      const cp1x = pts[i-1].x + (pts[i].x - pts[i-1].x) / 2
      ctx.bezierCurveTo(cp1x, pts[i-1].y, cp1x, pts[i].y, pts[i].x, pts[i].y)
    }
    ctx.strokeStyle = ds.color
    ctx.lineWidth   = 2.5
    ctx.stroke()

    // dots
    pts.forEach(pt => {
      ctx.beginPath()
      ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2)
      ctx.fillStyle   = ds.color
      ctx.fill()
      ctx.strokeStyle = '#fff'
      ctx.lineWidth   = 2
      ctx.stroke()
    })
  })

  // x-axis labels
  ctx.fillStyle  = '#6b7280'
  ctx.font       = '11px system-ui,sans-serif'
  ctx.textAlign  = 'center'
  labels.forEach((lbl, i) => {
    const x = PAD.left + (i / (n - 1)) * chartW
    ctx.fillText(lbl, x, PAD.top + chartH + 18)
  })

  // legend
  if (datasets.length > 1 || options.showLegend) {
    let lx = PAD.left
    datasets.forEach(ds => {
      ctx.fillStyle = ds.color
      ctx.fillRect(lx, H - 10, 10, 3)
      ctx.fillStyle  = '#374151'
      ctx.textAlign  = 'left'
      ctx.font       = '11px system-ui,sans-serif'
      ctx.fillText(ds.label, lx + 14, H - 4)
      lx += ctx.measureText(ds.label).width + 30
    })
  }
}

// ─── Draw all charts ──────────────────────────────────────────────────────────
function drawCharts() {
  const eventData   = getThisYearMonthlyCount(events.value, 'created_at')
  const orderData   = getThisYearMonthlyCount(orders.value, 'created_at')
  const revenueData = getThisYearMonthlySum(
    payments.value.filter(p => p.status === 'berhasil'),
    'created_at',
    'jumlah_bayar'
  )

  // Chart 1 — Event per bulan (bar)
  drawBarChart(canvasEventBulanan.value, MONTHS, [
    { label: 'Event Dibuat', data: eventData, color: '#7c3aed' }
  ])

  // Chart 2 — Pesanan & Pendapatan per bulan (line)
  drawLineChart(canvasPesananBulanan.value, MONTHS, [
    { label: 'Pesanan', data: orderData,   color: '#0ea5e9' },
  ], { showLegend: true })

  // Chart 3 — Pendapatan per bulan (bar)
  drawBarChart(canvasStatusPembayaran.value, MONTHS, [
    { label: 'Pendapatan (Rp)', data: revenueData, color: '#10b981' }
  ], { currency: true })

  // Chart 4 — Status pesanan (donut)
  const statusCount = {
    pending    : orders.value.filter(o => o.status === 'pending').length,
    dibayar    : orders.value.filter(o => o.status === 'dibayar').length,
    selesai    : orders.value.filter(o => o.status === 'selesai').length,
    dibatalkan : orders.value.filter(o => o.status === 'dibatalkan').length,
  }
  drawDonutChart(
    canvasPesananStatus.value,
    ['Pending', 'Dibayar', 'Selesai', 'Dibatalkan'],
    [statusCount.pending, statusCount.dibayar, statusCount.selesai, statusCount.dibatalkan],
    ['#f59e0b', '#0ea5e9', '#10b981', '#ef4444']
  )
}

// ─── Utility ──────────────────────────────────────────────────────────────────
function niceStep(max) {
  if (max <= 5)   return 1
  if (max <= 20)  return 5
  if (max <= 50)  return 10
  if (max <= 200) return 50
  if (max <= 1e6) return Math.pow(10, Math.floor(Math.log10(max)))
  return Math.pow(10, Math.floor(Math.log10(max)))
}

function formatCompact(val) {
  if (val >= 1e9) return (val / 1e9).toFixed(1) + 'M'
  if (val >= 1e6) return (val / 1e6).toFixed(1) + 'jt'
  if (val >= 1e3) return (val / 1e3).toFixed(0) + 'rb'
  return String(Math.round(val))
}

function getComputedBg() {
  return getComputedStyle(document.documentElement).getPropertyValue('--card-bg').trim() || '#ffffff'
}

const statusBadge = (status) => {
  const map = {
    pending    : 'badge badge-warning',
    dibayar    : 'badge badge-success',
    selesai    : 'badge badge-success',
    dibatalkan : 'badge badge-danger',
  }
  return map[status] || 'badge badge-neutral'
}
</script>

<template>
  <AdminLayout>
    <div class="dashboard">

      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>Dashboard</h1>
          <p>Ringkasan pengelolaan InfoMusikBDG — {{ new Date().getFullYear() }}</p>
        </div>
        <button @click="fetchAll" class="btn-refresh" :disabled="loading">
          <svg :class="{ spin: loading }" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="1 4 1 10 7 10"></polyline>
            <path d="M3.51 15a9 9 0 1 0 .49-3.09"></path>
          </svg>
          Refresh
        </button>
      </div>

      <!-- Stat Cards -->
      <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Memuat data dashboard...</p>
      </div>

      <template v-else>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon purple">
              <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
              <span>Total Event</span>
              <strong>{{ stats.totalEvent }}</strong>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon blue">
              <i class="fa-solid fa-ticket"></i>
            </div>
            <div>
              <span>Total Tiket</span>
              <strong>{{ stats.totalTiket }}</strong>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green">
              <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
              <span>Total Pesanan</span>
              <strong>{{ stats.totalPesanan }}</strong>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange">
              <i class="fa-solid fa-users"></i>
            </div>
            <div>
              <span>Total Pengguna</span>
              <strong>{{ stats.totalPengguna }}</strong>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon teal">
              <i class="fa-solid fa-money-check-dollar"></i>
            </div>
            <div>
              <span>Pembayaran Berhasil</span>
              <strong>{{ stats.pesananBerhasil }}</strong>
            </div>
          </div>
          <div class="stat-card stat-card-revenue">
            <div class="stat-icon green">
              <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
              <span>Total Pendapatan</span>
              <strong class="revenue-text">{{ formatRupiah(stats.pendapatanTotal) }}</strong>
            </div>
          </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="charts-row">
          <!-- Event per Bulan -->
          <div class="chart-card chart-wide">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Event Dibuat per Bulan</h3>
                <p class="chart-sub">Jumlah event yang ditambahkan tahun {{ new Date().getFullYear() }}</p>
              </div>
              <span class="chart-badge purple">{{ stats.totalEvent }} event</span>
            </div>
            <div class="chart-wrap">
              <canvas ref="canvasEventBulanan"></canvas>
            </div>
          </div>

          <!-- Status Pesanan Donut -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Status Pesanan</h3>
                <p class="chart-sub">Distribusi status seluruh pesanan</p>
              </div>
              <span class="chart-badge blue">{{ stats.totalPesanan }} total</span>
            </div>
            <div class="chart-wrap">
              <canvas ref="canvasPesananStatus"></canvas>
            </div>
          </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="charts-row">
          <!-- Pesanan per Bulan -->
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Pesanan per Bulan</h3>
                <p class="chart-sub">Jumlah pesanan masuk tahun {{ new Date().getFullYear() }}</p>
              </div>
              <span class="chart-badge blue">{{ stats.totalPesanan }} total</span>
            </div>
            <div class="chart-wrap">
              <canvas ref="canvasPesananBulanan"></canvas>
            </div>
          </div>

          <!-- Pendapatan per Bulan -->
          <div class="chart-card chart-wide">
            <div class="chart-header">
              <div>
                <h3 class="chart-title">Pendapatan per Bulan</h3>
                <p class="chart-sub">Total pembayaran berhasil tahun {{ new Date().getFullYear() }}</p>
              </div>
              <span class="chart-badge green">{{ formatRupiah(stats.pendapatanTotal) }}</span>
            </div>
            <div class="chart-wrap">
              <canvas ref="canvasStatusPembayaran"></canvas>
            </div>
          </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="dashboard-card">
          <div class="recent-header">
            <div>
              <h3 class="chart-title">Pesanan Terbaru</h3>
              <p class="chart-sub">6 transaksi pesanan terakhir masuk</p>
            </div>
            <router-link to="/admin/pesanan" class="btn-link">Lihat Semua →</router-link>
          </div>
          <div class="table-container">
            <table class="table-custom">
              <thead>
                <tr>
                  <th>Kode Pesanan</th>
                  <th>Pemesan</th>
                  <th>Total</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="o in recentOrders" :key="o.id">
                  <td><strong class="font-semibold text-main">{{ o.kode_pesanan }}</strong></td>
                  <td>
                    <div class="font-semibold">{{ o.user?.name || 'User #' + o.user_id }}</div>
                    <div class="text-xs text-muted">{{ o.user?.email }}</div>
                  </td>
                  <td class="font-semibold">{{ formatRupiah(o.total_harga) }}</td>
                  <td class="text-muted text-sm">{{ formatDate(o.created_at) }}</td>
                  <td><span :class="statusBadge(o.status)">{{ o.status }}</span></td>
                </tr>
                <tr v-if="recentOrders.length === 0">
                  <td colspan="5" class="text-center text-muted py-4">Belum ada pesanan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </template>
    </div>
  </AdminLayout>
</template>

<style scoped>
.dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

/* ─── Header ─────────────────────────────────────────────────────────────── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.page-header h1 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--text-main, #111827);
}

.page-header p {
  margin-top: 0.3rem;
  color: #6b7280;
  font-size: 0.88rem;
}

.btn-refresh {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.9rem;
  background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 8px;
  font-size: 0.82rem;
  color: var(--text-main, #374151);
  cursor: pointer;
  transition: all 0.15s;
}

.btn-refresh:hover:not(:disabled) {
  background: var(--primary, #7c3aed);
  color: #fff;
  border-color: var(--primary, #7c3aed);
}

.btn-refresh:disabled { opacity: 0.5; cursor: not-allowed; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; }

/* ─── Loading ─────────────────────────────────────────────────────────────── */
.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  gap: 0.75rem;
  color: #6b7280;
  font-size: 0.9rem;
}

/* ─── Stats Grid ──────────────────────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px;
  padding: 1.1rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.9rem;
  transition: box-shadow 0.15s;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.07);
}

.stat-card-revenue {
  grid-column: span 2;
}

.stat-card span {
  display: block;
  color: #6b7280;
  font-size: 0.76rem;
  margin-bottom: 0.25rem;
  white-space: nowrap;
}

.stat-card strong {
  display: block;
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--text-main, #111827);
  line-height: 1.1;
}

.revenue-text {
  font-size: 1.1rem !important;
  white-space: nowrap;
}

.stat-icon {
  width: 44px;
  height: 44px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.1rem;
}

.stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
.stat-icon.blue   { background: #e0f2fe; color: #0284c7; }
.stat-icon.green  { background: #dcfce7; color: #16a34a; }
.stat-icon.orange { background: #ffedd5; color: #ea580c; }
.stat-icon.teal   { background: #ccfbf1; color: #0d9488; }

/* ─── Charts ──────────────────────────────────────────────────────────────── */
.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.chart-card {
  background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px;
  padding: 1.25rem;
}

.chart-card.chart-wide {
  grid-column: span 1;
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1rem;
  gap: 0.5rem;
}

.chart-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text-main, #111827);
}

.chart-sub {
  margin: 0.2rem 0 0;
  font-size: 0.78rem;
  color: #6b7280;
}

.chart-badge {
  flex-shrink: 0;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
}

.chart-badge.purple { background: #f3e8ff; color: #7c3aed; }
.chart-badge.blue   { background: #e0f2fe; color: #0284c7; }
.chart-badge.green  { background: #dcfce7; color: #16a34a; }

.chart-wrap {
  width: 100%;
  height: 220px;
  position: relative;
}

.chart-wrap canvas {
  width: 100% !important;
  height: 100% !important;
  display: block;
}

/* ─── Recent Orders ───────────────────────────────────────────────────────── */
.dashboard-card {
  background: var(--card-bg, #fff);
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px;
  padding: 1.25rem;
  margin-top: 1rem;
}

.recent-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.btn-link {
  font-size: 0.8rem;
  color: var(--primary, #7c3aed);
  text-decoration: none;
  font-weight: 500;
  white-space: nowrap;
}

.btn-link:hover { text-decoration: underline; }

/* ─── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
  .stat-card-revenue { grid-column: span 1; }
}

@media (max-width: 800px) {
  .stats-grid  { grid-template-columns: repeat(2, 1fr); }
  .charts-row  { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
  .stats-grid  { grid-template-columns: 1fr; }
}
</style>
