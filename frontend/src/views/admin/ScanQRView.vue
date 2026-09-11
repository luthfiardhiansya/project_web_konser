<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'

const scannedList = ref([])
const loading = ref(true)
const selectedDetail = ref(null)

// Search & Filter
const searchQuery    = ref('')
const filterDateFrom = ref('')
const filterDateTo   = ref('')

// Pagination
const currentPage = ref(1)
const perPage     = 10

// ─── Fetch ───────────────────────────────────────────────────────────────────
const fetchScanned = async () => {
  loading.value = true
  try {
    const params = {}
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value)   params.date_to   = filterDateTo.value

    const res = await api.get('/issued-tickets', { params })
    scannedList.value = res.data.data || res.data || []
  } catch (e) {
    console.error('Gagal mengambil riwayat scan:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchScanned)

// Reset halaman saat filter/search berubah
watch([searchQuery, filterDateFrom, filterDateTo], () => {
  currentPage.value = 1
})

// Re-fetch saat tanggal berubah (server-side filter)
watch([filterDateFrom, filterDateTo], fetchScanned)

// ─── Computed ─────────────────────────────────────────────────────────────────
const filtered = computed(() => {
  if (!searchQuery.value.trim()) return scannedList.value
  const q = searchQuery.value.toLowerCase()
  return scannedList.value.filter(t =>
    t.order?.kode_pesanan?.toLowerCase().includes(q) ||
    t.order?.user?.name?.toLowerCase().includes(q)   ||
    t.ticket?.nama_tiket?.toLowerCase().includes(q)  ||
    t.ticket?.event?.nama_event?.toLowerCase().includes(q)
  )
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))

const paginated = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filtered.value.slice(start, start + perPage)
})

const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

const goToPage = (p) => {
  if (p >= 1 && p <= totalPages.value) currentPage.value = p
}

const hasActiveFilter = computed(() =>
  searchQuery.value || filterDateFrom.value || filterDateTo.value
)

const resetFilters = () => {
  searchQuery.value    = ''
  filterDateFrom.value = ''
  filterDateTo.value   = ''
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
const formatDateTime = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}
</script>

<template>
  <AdminLayout>
    <div class="scan-qr-page">

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Riwayat Scan QR Tiket</h1>
          <p class="text-muted text-sm">Daftar tiket yang sudah berhasil di-scan saat masuk event</p>
        </div>
        <button @click="fetchScanned" class="btn-refresh" :disabled="loading">
          <svg :class="{ spin: loading }" width="14" height="14" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="1 4 1 10 7 10"></polyline>
            <path d="M3.51 15a9 9 0 1 0 .49-3.09"></path>
          </svg>
          Refresh
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar card mb-3">
        <div class="filter-bar-inner">

          <!-- Search -->
          <div class="search-group">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input"
              placeholder="Cari kode pesanan, nama pemesan, jenis tiket..."
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-btn" title="Hapus">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Filter Tanggal Dari -->
          <div class="date-filter-group">
            <label class="date-label">Dari</label>
            <input type="date" v-model="filterDateFrom" class="form-control filter-date" />
          </div>

          <!-- Filter Tanggal Sampai -->
          <div class="date-filter-group">
            <label class="date-label">Sampai</label>
            <input type="date" v-model="filterDateTo" class="form-control filter-date" />
          </div>

          <!-- Reset -->
          <button v-if="hasActiveFilter" @click="resetFilters" class="btn btn-outline btn-sm reset-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5">
              <polyline points="1 4 1 10 7 10"></polyline>
              <path d="M3.51 15a9 9 0 1 0 .49-3.09"></path>
            </svg>
            Reset
          </button>
        </div>

        <div class="filter-result-info">
          <span class="text-muted text-sm">
            Menampilkan <strong>{{ filtered.length }}</strong> dari
            <strong>{{ scannedList.length }}</strong> riwayat scan
          </span>
        </div>
      </div>

      <!-- Table Card -->
      <div class="card">
        <div class="table-container">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p class="text-muted text-sm mt-2">Memuat riwayat scan...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 70px;">ID</th>
                <th style="width: 140px;">Kode Pesanan</th>
                <th>Nama Pemesan</th>
                <th>Jenis Tiket</th>
                <th>Waktu Scan</th>
                <th style="text-align: right; width: 100px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in paginated" :key="t.id">
                <td>
                  <span class="badge badge-neutral">#{{ t.id }}</span>
                </td>
                <td>
                  <strong class="font-semibold text-main">
                    {{ t.order?.kode_pesanan || '-' }}
                  </strong>
                </td>
                <td>
                  <div class="font-semibold">
                    {{ t.order?.user?.name || 'Pemesan #' + t.order?.user_id }}
                  </div>
                  <div class="text-xs text-muted">
                    {{ t.ticket?.event?.nama_event || '-' }}
                  </div>
                </td>
                <td>
                  <span class="badge badge-purple">
                    {{ t.ticket?.nama_tiket || '-' }}
                  </span>
                </td>
                <td class="text-sm text-muted">
                  {{ formatDateTime(t.scanned_at) }}
                </td>
                <td style="text-align: right;">
                  <button @click="selectedDetail = t" class="btn btn-secondary btn-sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    Detail
                  </button>
                </td>
              </tr>
              <tr v-if="paginated.length === 0">
                <td colspan="6" class="text-center text-muted py-4">
                  {{
                    loading ? '' :
                    hasActiveFilter
                      ? 'Tidak ada riwayat scan yang cocok dengan filter.'
                      : 'Belum ada tiket yang ter-scan.'
                  }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filtered.length > perPage" class="pagination-wrapper">
          <div class="pagination-info">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filtered.length) }}
            dari {{ filtered.length }} data
          </div>
          <div class="pagination-controls">
            <button class="page-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <template v-for="p in pageNumbers" :key="p">
              <button class="page-btn" :class="{ 'page-btn-active': p === currentPage }" @click="goToPage(p)">
                {{ p }}
              </button>
            </template>
            <button class="page-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>

    </div>

    <!-- ─── MODAL DETAIL ──────────────────────────────────────────────────── -->
    <div v-if="selectedDetail" class="modal-overlay" @click.self="selectedDetail = null">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-scan-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                <path d="M14 14h.01M14 17h3M17 14v3M20 17v3M20 14h.01"></path>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-base">Detail Scan Tiket</h3>
              <p class="text-xs text-muted">#{{ selectedDetail.id }}</p>
            </div>
          </div>
          <button @click="selectedDetail = null" class="btn-close">✕</button>
        </div>

        <div class="modal-body">
          <div class="detail-grid">
            <!-- ID -->
            <div class="detail-item">
              <span class="detail-label">ID Issued Ticket</span>
              <span class="detail-value">
                <span class="badge badge-neutral">#{{ selectedDetail.id }}</span>
              </span>
            </div>

            <!-- Kode Pesanan -->
            <div class="detail-item">
              <span class="detail-label">Kode Pesanan</span>
              <span class="detail-value font-semibold text-main">
                {{ selectedDetail.order?.kode_pesanan || '-' }}
              </span>
            </div>

            <!-- Nama Pemesan -->
            <div class="detail-item">
              <span class="detail-label">Nama Pemesan</span>
              <span class="detail-value font-semibold">
                {{ selectedDetail.order?.user?.name || '-' }}
              </span>
            </div>

            <!-- Email -->
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value text-muted">
                {{ selectedDetail.order?.user?.email || '-' }}
              </span>
            </div>

            <!-- Event -->
            <div class="detail-item detail-item-full">
              <span class="detail-label">Event</span>
              <span class="detail-value font-semibold">
                {{ selectedDetail.ticket?.event?.nama_event || '-' }}
              </span>
            </div>

            <!-- Jenis Tiket -->
            <div class="detail-item">
              <span class="detail-label">Jenis Tiket</span>
              <span class="detail-value">
                <span class="badge badge-purple">
                  {{ selectedDetail.ticket?.nama_tiket || '-' }}
                </span>
              </span>
            </div>

            <!-- Status -->
            <div class="detail-item">
              <span class="detail-label">Status Tiket</span>
              <span class="detail-value">
                <span class="badge badge-success">✓ Sudah Di-scan</span>
              </span>
            </div>

            <!-- Waktu Scan -->
            <div class="detail-item detail-item-full">
              <span class="detail-label">Waktu Scan</span>
              <span class="detail-value font-semibold text-main">
                {{ formatDateTime(selectedDetail.scanned_at) }}
              </span>
            </div>

            <!-- Scanned By -->
            <div class="detail-item detail-item-full" v-if="selectedDetail.scanner">
              <span class="detail-label">Di-scan Oleh</span>
              <span class="detail-value text-muted">
                {{ selectedDetail.scanner?.name || '-' }}
              </span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="selectedDetail = null" class="btn btn-outline">Tutup</button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<style scoped>
.scan-qr-page {
  max-width: 1200px;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-main);
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

/* Loading */
.loading-state {
  padding: 3rem;
  text-align: center;
}

/* Filter Bar */
.filter-bar {
  padding: 0.75rem 1rem 0.6rem;
}

.filter-bar-inner {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}

.search-group {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 220px;
}

.search-icon {
  position: absolute;
  left: 0.65rem;
  color: var(--text-muted, #888);
  pointer-events: none;
}

.search-input {
  padding-left: 2.2rem !important;
  padding-right: 2rem !important;
}

.clear-btn {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted, #888);
  display: flex;
  align-items: center;
  padding: 2px;
  border-radius: 50%;
}

.clear-btn:hover { color: var(--text-main, #222); }

.date-filter-group {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.date-label {
  font-size: 0.78rem;
  color: var(--text-muted, #888);
  white-space: nowrap;
}

.filter-date { max-width: 145px; }

.reset-btn { white-space: nowrap; }

.filter-result-info { padding-top: 0.1rem; }

/* Pagination */
.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid var(--border-color, #e5e7eb);
  flex-wrap: wrap;
  gap: 0.5rem;
}

.pagination-info {
  font-size: 0.8rem;
  color: var(--text-muted, #888);
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.page-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 0.4rem;
  border: 1px solid var(--border-color, #e5e7eb);
  background: var(--card-bg, #fff);
  color: var(--text-main, #222);
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}

.page-btn:hover:not(:disabled) {
  background: var(--primary, #7c3aed);
  color: #fff;
  border-color: var(--primary, #7c3aed);
}

.page-btn-active {
  background: var(--primary, #7c3aed) !important;
  color: #fff !important;
  border-color: var(--primary, #7c3aed) !important;
}

.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-card {
  background: var(--card-bg, #fff);
  border-radius: 12px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-scan-icon {
  width: 38px;
  height: 38px;
  border-radius: 8px;
  background: #f3e8ff;
  color: #7c3aed;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: var(--text-muted, #888);
  line-height: 1;
  padding: 4px 6px;
  border-radius: 6px;
}

.btn-close:hover {
  background: var(--border-color, #e5e7eb);
  color: var(--text-main, #222);
}

.modal-body {
  padding: 1.25rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid var(--border-color, #e5e7eb);
}

/* Detail grid inside modal */
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem 1.25rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.detail-item-full {
  grid-column: span 2;
}

.detail-label {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--text-muted, #888);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.detail-value {
  font-size: 0.88rem;
  color: var(--text-main, #111827);
}
</style>
