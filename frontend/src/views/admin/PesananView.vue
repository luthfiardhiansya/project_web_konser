<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const pesananList = ref([])
const users = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const selectedPesanan = ref(null)
const selectedDetailOrder = ref(null)
const form = ref({ id: null, status: 'pending' })

// Search & Filter
const searchQuery = ref('')
const filterStatus = ref('')
const filterTanggalDari = ref('')
const filterTanggalSampai = ref('')

// Pagination
const currentPage = ref(1)
const perPage = 10

const fetchPesanan = async () => {
  loading.value = true
  try {
    const response = await api.get('/orders')
    pesananList.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil pesanan:', error)
  } finally {
    loading.value = false
  }
}

const fetchUsers = async () => {
  try {
    const response = await api.get('/users')
    users.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil pengguna:', error)
  }
}

onMounted(() => {
  fetchPesanan()
  fetchUsers()
})

// Reset halaman saat filter berubah
watch([searchQuery, filterStatus, filterTanggalDari, filterTanggalSampai], () => {
  currentPage.value = 1
})

const filteredPesanan = computed(() => {
  let data = pesananList.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    data = data.filter(o =>
      o.kode_pesanan?.toLowerCase().includes(q) ||
      o.user?.name?.toLowerCase().includes(q) ||
      o.user?.email?.toLowerCase().includes(q)
    )
  }

  if (filterStatus.value) {
    data = data.filter(o => o.status === filterStatus.value)
  }

  if (filterTanggalDari.value) {
    data = data.filter(o => {
      const tgl = o.created_at ? o.created_at.substring(0, 10) : ''
      return tgl >= filterTanggalDari.value
    })
  }

  if (filterTanggalSampai.value) {
    data = data.filter(o => {
      const tgl = o.created_at ? o.created_at.substring(0, 10) : ''
      return tgl <= filterTanggalSampai.value
    })
  }

  return data
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredPesanan.value.length / perPage)))

const paginatedPesanan = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredPesanan.value.slice(start, start + perPage)
})

const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

const hasActiveFilter = computed(() =>
  searchQuery.value || filterStatus.value || filterTanggalDari.value || filterTanggalSampai.value
)

const resetFilters = () => {
  searchQuery.value = ''
  filterStatus.value = ''
  filterTanggalDari.value = ''
  filterTanggalSampai.value = ''
}

const openEditStatusForm = (order) => {
  isEdit.value = true
  selectedPesanan.value = order
  form.value = { id: order.id, status: order.status }
  showForm.value = true
}

const openDetailModal = (order) => {
  selectedDetailOrder.value = order
}

const savePesananStatus = async () => {
  try {
    await api.put(`/orders/${form.value.id}`, { status: form.value.status })
    showFlash('Status pesanan berhasil diperbarui!', 'success', 'BERHASIL')
    showForm.value = false
    fetchPesanan()
  } catch (error) {
    showFlash(error.response?.data?.message || 'Gagal merubah status pesanan.', 'error', 'GAGAL')
  }
}

const hapusPesanan = async (id) => {
  if (confirm('Yakin ingin menghapus data pesanan ini?')) {
    try {
      await api.delete(`/orders/${id}`)
      showFlash('Data pesanan berhasil dihapus.', 'success', 'BERHASIL')
      fetchPesanan()
    } catch (error) {
      showFlash(error.response?.data?.message || 'Gagal menghapus pesanan.', 'error', 'GAGAL')
    }
  }
}

const formatNumber = (val) => {
  return new Intl.NumberFormat('id-ID').format(Number(val) || 0)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <AdminLayout>
    <div class="pesanan-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manajemen Pesanan</h1>
          <p class="text-muted text-sm">Kelola transaksi dan status pemesanan tiket</p>
        </div>
      </div>

      <!-- Search & Filter Bar -->
      <div class="filter-bar card mb-3">
        <div class="filter-bar-inner">
          <!-- Search -->
          <div class="search-group">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input"
              placeholder="Cari kode pesanan, nama, email..."
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-btn" title="Hapus pencarian">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Filter Status -->
          <select v-model="filterStatus" class="form-control filter-select">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="dibayar">Dibayar</option>
            <option value="selesai">Selesai</option>
            <option value="dibatalkan">Dibatalkan</option>
          </select>

          <!-- Filter Tanggal Dari -->
          <div class="date-filter-group">
            <label class="date-label">Dari</label>
            <input type="date" v-model="filterTanggalDari" class="form-control filter-date" />
          </div>

          <!-- Filter Tanggal Sampai -->
          <div class="date-filter-group">
            <label class="date-label">Sampai</label>
            <input type="date" v-model="filterTanggalSampai" class="form-control filter-date" />
          </div>

          <!-- Reset -->
          <button v-if="hasActiveFilter" @click="resetFilters" class="btn btn-outline btn-sm reset-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="1 4 1 10 7 10"></polyline>
              <path d="M3.51 15a9 9 0 1 0 .49-3.09"></path>
            </svg>
            Reset
          </button>
        </div>

        <div class="filter-result-info">
          <span class="text-muted text-sm">
            Menampilkan <strong>{{ filteredPesanan.length }}</strong> dari <strong>{{ pesananList.length }}</strong> pesanan
          </span>
        </div>
      </div>

      <!-- Table View Card -->
      <div class="card">
        <div class="table-container">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p class="text-muted text-sm mt-2">Memuat data pesanan...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 120px;">Kode Pesanan</th>
                <th>Pemesan</th>
                <th>Detail Tiket</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th style="text-align: right; width: 240px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in paginatedPesanan" :key="order.id">
                <td>
                  <strong class="font-semibold text-main">{{ order.kode_pesanan }}</strong>
                  <div class="text-[11px] text-muted">{{ formatDate(order.created_at) }}</div>
                </td>
                <td>
                  <div>
                    <div class="font-semibold">{{ order.user?.name || 'User #' + order.user_id }}</div>
                    <div class="text-xs text-muted">{{ order.user?.email || '-' }}</div>
                  </div>
                </td>
                <td>
                  <div v-if="order.order_details && order.order_details.length">
                    <div v-for="item in order.order_details" :key="item.id" class="text-xs">
                      🎫 {{ item.ticket?.event?.nama_event || 'Event' }} ({{ item.ticket?.nama_tiket }}) x{{ item.jumlah }}
                    </div>
                  </div>
                  <span v-else class="text-muted text-xs">-</span>
                </td>
                <td class="font-semibold text-main">
                  Rp {{ formatNumber(order.total_harga) }}
                </td>
                <td>
                  <span :class="{
                    'badge badge-warning': order.status === 'pending',
                    'badge badge-success': order.status === 'dibayar' || order.status === 'selesai',
                    'badge badge-danger': order.status === 'dibatalkan'
                  }">
                    {{ order.status }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="d-flex gap-1 justify-end">
                    <button @click="openDetailModal(order)" class="btn btn-secondary btn-sm" title="Detail Pesanan">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                      Detail
                    </button>
                    <button @click="openEditStatusForm(order)" class="btn btn-outline btn-sm" title="Edit Status">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      Status
                    </button>
                    <button @click="hapusPesanan(order.id)" class="btn btn-danger btn-sm" title="Hapus">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedPesanan.length === 0">
                <td colspan="6" class="text-center text-muted py-4">
                  {{ hasActiveFilter ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada transaksi pesanan.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredPesanan.length > perPage" class="pagination-wrapper">
          <div class="pagination-info">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredPesanan.length) }} dari {{ filteredPesanan.length }} data
          </div>
          <div class="pagination-controls">
            <button class="page-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <template v-for="page in pageNumbers" :key="page">
              <button
                class="page-btn"
                :class="{ 'page-btn-active': page === currentPage }"
                @click="goToPage(page)"
              >{{ page }}</button>
            </template>
            <button class="page-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </button>
          </div>
        </div>
      </div>

    <!-- MODAL UPDATE STATUS PESANAN -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="font-bold text-base">
            Update Status &nbsp;<span class="badge badge-purple">#{{ selectedPesanan?.kode_pesanan }}</span>
          </h3>
          <button @click="showForm = false" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="savePesananStatus">
            <div class="form-group mb-3">
              <label class="form-label">Pemesan</label>
              <input type="text" :value="selectedPesanan?.user?.name || 'User #' + selectedPesanan?.user_id" class="form-control" disabled>
            </div>
            <div class="form-group mb-3">
              <label class="form-label">Total Harga</label>
              <input type="text" :value="'Rp ' + formatNumber(selectedPesanan?.total_harga)" class="form-control" disabled>
            </div>
            <div class="form-group mb-4">
              <label class="form-label">Status Pesanan</label>
              <select v-model="form.status" class="form-control" required>
                <option value="pending">Pending (Menunggu Pembayaran)</option>
                <option value="dibayar">Dibayar (Lunas)</option>
                <option value="selesai">Selesai</option>
                <option value="dibatalkan">Dibatalkan</option>
              </select>
            </div>
            <div class="modal-footer">
              <button type="button" @click="showForm = false" class="btn btn-outline">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan Status</button>
            </div>
          </form>
        </div>
      </div>
    </div>

      <!-- MODAL DETAIL PESANAN (ADMIN) -->      <div v-if="selectedDetailOrder" class="modal-overlay" @click.self="selectedDetailOrder = null">
        <div class="modal-card">
          <div class="modal-header">
            <h3 class="font-bold text-lg">Detail Rincian Pesanan #{{ selectedDetailOrder.kode_pesanan }}</h3>
            <button @click="selectedDetailOrder = null" class="btn-close">✕</button>
          </div>
          <div class="modal-body space-y-4">
            <!-- USER & ORDER SUMMARY -->
            <div class="detail-grid">
              <div>
                <span class="text-muted text-xs block font-semibold">Nama Pemesan</span>
                <strong class="text-sm">{{ selectedDetailOrder.user?.name || 'User #' + selectedDetailOrder.user_id }}</strong>
              </div>
              <div>
                <span class="text-muted text-xs block font-semibold">Email</span>
                <strong class="text-sm">{{ selectedDetailOrder.user?.email || '-' }}</strong>
              </div>
              <div>
                <span class="text-muted text-xs block font-semibold">Tanggal Pesanan</span>
                <strong class="text-sm">{{ formatDate(selectedDetailOrder.created_at) }}</strong>
              </div>
              <div>
                <span class="text-muted text-xs block font-semibold">Status Pesanan</span>
                <span :class="{
                  'badge badge-warning': selectedDetailOrder.status === 'pending',
                  'badge badge-success': selectedDetailOrder.status === 'dibayar' || selectedDetailOrder.status === 'selesai',
                  'badge badge-danger': selectedDetailOrder.status === 'dibatalkan'
                }">
                  {{ selectedDetailOrder.status }}
                </span>
              </div>
              <div>
                <span class="text-muted text-xs block font-semibold">Status Pembayaran</span>
                <span :class="selectedDetailOrder.payment?.status === 'berhasil' || selectedDetailOrder.status === 'dibayar' ? 'badge badge-success' : 'badge badge-warning'">
                  {{ selectedDetailOrder.payment?.status || selectedDetailOrder.status }}
                </span>
              </div>
              <div>
                <span class="text-muted text-xs block font-semibold">Tanggal Bayar</span>
                <strong class="text-sm">{{ selectedDetailOrder.payment?.dibayar_pada ? formatDate(selectedDetailOrder.payment.dibayar_pada) : '-' }}</strong>
              </div>
            </div>

            <!-- ITEMS TABLE -->
            <div>
              <h4 class="font-semibold text-sm mb-2">Daftar Tiket Dipesan</h4>
              <table class="table-custom text-xs">
                <thead>
                  <tr>
                    <th>Event & Tiket</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedDetailOrder.order_details" :key="item.id">
                    <td>
                      <strong class="block">{{ item.ticket?.event?.nama_event || 'Event' }}</strong>
                      <span class="text-muted text-xs">{{ item.ticket?.nama_tiket }}</span>
                    </td>
                    <td style="text-align: center;" class="font-bold">{{ item.jumlah }}</td>
                    <td style="text-align: right;">Rp {{ formatNumber(item.harga_satuan) }}</td>
                    <td style="text-align: right;" class="font-semibold">Rp {{ formatNumber(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" style="text-align: right;" class="font-bold">Total Harga:</td>
                    <td style="text-align: right;" class="font-bold text-main">Rp {{ formatNumber(selectedDetailOrder.total_harga) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="selectedDetailOrder = null" class="btn btn-secondary">Tutup</button>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<style scoped>
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

.form-card {
  margin-bottom: 1.5rem;
}

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
  min-width: 200px;
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

.clear-btn:hover {
  color: var(--text-main, #222);
}

.filter-select {
  max-width: 170px;
  min-width: 140px;
}

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

.filter-date {
  max-width: 145px;
}

.reset-btn {
  white-space: nowrap;
}

.filter-result-info {
  padding-top: 0.1rem;
}

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

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

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

.modal-body.modal-scroll {
  max-height: 75vh;
  overflow-y: auto;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding-top: 0.5rem;
}
</style>
