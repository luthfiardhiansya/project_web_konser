<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const payments = ref([])
const orders = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({
  id: null,
  order_id: '',
  metode_pembayaran: 'Transfer Bank',
  jumlah_bayar: 0,
  bukti_pembayaran: '',
  status: 'pending'
})

// Search & Filter
const searchQuery = ref('')
const filterStatus = ref('')
const filterTanggalDari = ref('')
const filterTanggalSampai = ref('')

// Pagination
const currentPage = ref(1)
const perPage = 10

const fetchPayments = async () => {
  loading.value = true
  try {
    const response = await api.get('/payments')
    payments.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil pembayaran:', error)
  } finally {
    loading.value = false
  }
}

const fetchOrders = async () => {
  try {
    const response = await api.get('/orders')
    orders.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil pesanan:', error)
  }
}

onMounted(() => {
  fetchPayments()
  fetchOrders()
})

// Reset halaman saat filter berubah
watch([searchQuery, filterStatus, filterTanggalDari, filterTanggalSampai], () => {
  currentPage.value = 1
})

const filteredPayments = computed(() => {
  let data = payments.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    data = data.filter(p =>
      p.order?.kode_pesanan?.toLowerCase().includes(q) ||
      p.metode_pembayaran?.toLowerCase().includes(q) ||
      p.order?.user?.name?.toLowerCase().includes(q)
    )
  }

  if (filterStatus.value) {
    data = data.filter(p => p.status === filterStatus.value)
  }

  if (filterTanggalDari.value) {
    data = data.filter(p => {
      const tgl = p.dibayar_pada ? p.dibayar_pada.substring(0, 10) : (p.created_at ? p.created_at.substring(0, 10) : '')
      return tgl >= filterTanggalDari.value
    })
  }

  if (filterTanggalSampai.value) {
    data = data.filter(p => {
      const tgl = p.dibayar_pada ? p.dibayar_pada.substring(0, 10) : (p.created_at ? p.created_at.substring(0, 10) : '')
      return tgl <= filterTanggalSampai.value
    })
  }

  return data
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredPayments.value.length / perPage)))

const paginatedPayments = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredPayments.value.slice(start, start + perPage)
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

const openAddForm = () => {
  isEdit.value = false
  const selectedOrder = orders.value[0]
  form.value = {
    id: null,
    order_id: selectedOrder?.id || '',
    metode_pembayaran: 'Transfer Bank BCA',
    jumlah_bayar: selectedOrder?.total_harga || 0,
    bukti_pembayaran: '',
    status: 'pending'
  }
  showForm.value = true
}

const onOrderChange = () => {
  const selectedOrder = orders.value.find(o => o.id === Number(form.value.order_id))
  if (selectedOrder) {
    form.value.jumlah_bayar = selectedOrder.total_harga
  }
}

const openEditForm = (payment) => {
  isEdit.value = true
  form.value = {
    ...payment,
    order_id: payment.order_id || payment.order?.id || ''
  }
  showForm.value = true
}

const savePayment = async () => {
  try {
    if (isEdit.value) {
      await api.put(`/payments/${form.value.id}`, form.value)
      showFlash('Data pembayaran berhasil diperbarui!', 'success', 'BERHASIL')
    } else {
      await api.post('/payments', form.value)
      showFlash('Data pembayaran baru berhasil disimpan!', 'success', 'BERHASIL')
    }
    showForm.value = false
    fetchPayments()
  } catch (error) {
    showFlash(error.response?.data?.message || 'Gagal menyimpan data pembayaran.', 'error', 'GAGAL')
  }
}

const hapusPayment = async (id) => {
  if (confirm('Yakin ingin menghapus data pembayaran ini?')) {
    try {
      await api.delete(`/payments/${id}`)
      showFlash('Data pembayaran berhasil dihapus.', 'success', 'BERHASIL')
      fetchPayments()
    } catch (error) {
      showFlash(error.response?.data?.message || 'Gagal menghapus pembayaran.', 'error', 'GAGAL')
    }
  }
}

const formatNumber = (val) => {
  return new Intl.NumberFormat('id-ID').format(Number(val) || 0)
}
</script>

<template>
  <AdminLayout>
    <div class="pembayaran-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manajemen Pembayaran</h1>
          <p class="text-muted text-sm">Kelola verifikasi status transaksi dan pembayaran pesanan</p>
        </div>
        <button @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Input Pembayaran
        </button>
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
              placeholder="Cari kode pesanan, metode, nama pemesan..."
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
            <option value="berhasil">Berhasil</option>
            <option value="gagal">Gagal</option>
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
            Menampilkan <strong>{{ filteredPayments.length }}</strong> dari <strong>{{ payments.length }}</strong> pembayaran
          </span>
        </div>
      </div>

      <!-- Table View Card -->
      <div class="card">
        <div class="table-container">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p class="text-muted text-sm mt-2">Memuat data pembayaran...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 70px;">ID</th>
                <th>Kode Pesanan</th>
                <th>Metode</th>
                <th>Jumlah Bayar</th>
                <th>Tanggal Bayar</th>
                <th>Status</th>
                <th style="text-align: right; width: 180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in paginatedPayments" :key="p.id">
                <td>
                  <span class="badge badge-neutral">#{{ p.id }}</span>
                </td>
                <td>
                  <strong class="font-semibold text-main">
                    #{{ p.order?.kode_pesanan || 'Order #' + p.order_id }}
                  </strong>
                </td>
                <td>
                  <span class="badge badge-purple">{{ p.metode_pembayaran }}</span>
                </td>
                <td class="font-semibold text-main">
                  Rp {{ formatNumber(p.jumlah_bayar) }}
                </td>
                <td class="text-xs text-muted">
                  {{ p.dibayar_pada ? p.dibayar_pada : 'Belum Diverifikasi' }}
                </td>
                <td>
                  <span :class="{
                    'badge badge-warning': p.status === 'pending',
                    'badge badge-success': p.status === 'berhasil',
                    'badge badge-danger': p.status === 'gagal'
                  }">
                    {{ p.status }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="d-flex gap-2 justify-end">
                    <button @click="openEditForm(p)" class="btn btn-secondary btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      Edit
                    </button>
                    <button @click="hapusPayment(p.id)" class="btn btn-danger btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedPayments.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  {{ hasActiveFilter ? 'Tidak ada pembayaran yang cocok dengan filter.' : 'Belum ada data pembayaran.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredPayments.length > perPage" class="pagination-wrapper">
          <div class="pagination-info">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredPayments.length) }} dari {{ filteredPayments.length }} data
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
    </div>

    <!-- MODAL TAMBAH / EDIT PEMBAYARAN -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card modal-card-lg">
        <div class="modal-header">
          <h3 class="font-bold text-base">{{ isEdit ? 'Edit Data Pembayaran' : 'Input Pembayaran Baru' }}</h3>
          <button @click="showForm = false" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="savePayment">
            <div class="form-group mb-3">
              <label class="form-label">Pilih Pesanan (Order ID / Kode)</label>
              <select v-model="form.order_id" @change="onOrderChange" class="form-control" :disabled="isEdit" required>
                <option value="" disabled>-- Pilih Pesanan --</option>
                <option v-for="ord in orders" :key="ord.id" :value="ord.id">
                  #{{ ord.kode_pesanan }} - {{ ord.user?.name || 'User #' + ord.user_id }} (Rp {{ formatNumber(ord.total_harga) }})
                </option>
              </select>
            </div>
            <div class="form-group mb-3">
              <label class="form-label">Metode Pembayaran</label>
              <input type="text" v-model="form.metode_pembayaran" class="form-control" placeholder="BCA / Mandiri / QRIS..." required>
            </div>
            <div class="form-row mb-4">
              <div class="form-group flex-1">
                <label class="form-label">Jumlah Bayar (Rp)</label>
                <input type="number" v-model="form.jumlah_bayar" class="form-control" placeholder="0" required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Status Verifikasi</label>
                <select v-model="form.status" class="form-control" required>
                  <option value="pending">Pending (Menunggu Verifikasi)</option>
                  <option value="berhasil">Berhasil (Lunas)</option>
                  <option value="gagal">Gagal / Ditolak</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="showForm = false" class="btn btn-outline">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
            </div>
          </form>
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

.modal-card-lg {
  max-width: 640px;
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding-top: 0.5rem;
}
</style>
