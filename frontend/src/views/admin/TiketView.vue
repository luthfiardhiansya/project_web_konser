<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const tickets = ref([])
const events = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({ id: null, event_id: '', nama_tiket: '', harga: 0, stok: 0 })

// Search & Filter
const searchQuery = ref('')
const filterEvent = ref('')
const filterStok = ref('')

// Pagination
const currentPage = ref(1)
const perPage = 10

const fetchTickets = async () => {
  loading.value = true
  try {
    const response = await api.get('/tickets')
    tickets.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil tiket:', error)
  } finally {
    loading.value = false
  }
}

const fetchEvents = async () => {
  try {
    const response = await api.get('/events')
    events.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil event:', error)
  }
}

onMounted(() => {
  fetchTickets()
  fetchEvents()
})

// Reset page saat filter berubah
watch([searchQuery, filterEvent, filterStok], () => {
  currentPage.value = 1
})

const filteredTickets = computed(() => {
  let data = tickets.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    data = data.filter(t =>
      t.nama_tiket?.toLowerCase().includes(q) ||
      t.event?.nama_event?.toLowerCase().includes(q)
    )
  }

  if (filterEvent.value) {
    data = data.filter(t => String(t.event_id) === String(filterEvent.value))
  }

  if (filterStok.value === 'tersedia') {
    data = data.filter(t => t.stok > 0)
  } else if (filterStok.value === 'habis') {
    data = data.filter(t => t.stok === 0 || t.stok === '0')
  }

  return data
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredTickets.value.length / perPage)))

const paginatedTickets = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredTickets.value.slice(start, start + perPage)
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
  searchQuery.value || filterEvent.value || filterStok.value
)

const resetFilters = () => {
  searchQuery.value = ''
  filterEvent.value = ''
  filterStok.value = ''
}

const openAddForm = () => {
  isEdit.value = false
  form.value = {
    id: null,
    event_id: events.value[0]?.id || '',
    nama_tiket: '',
    harga: 50000,
    stok: 100
  }
  showForm.value = true
}

const openEditForm = (ticket) => {
  isEdit.value = true
  form.value = {
    ...ticket,
    event_id: ticket.event_id || ticket.event?.id || ''
  }
  showForm.value = true
}

const saveTicket = async () => {
  try {
    if (isEdit.value) {
      await api.put(`/tickets/${form.value.id}`, form.value)
      showFlash('Data tiket berhasil diperbarui!', 'success', 'BERHASIL')
    } else {
      await api.post('/tickets', form.value)
      showFlash('Tiket baru berhasil ditambahkan!', 'success', 'BERHASIL')
    }
    showForm.value = false
    fetchTickets()
  } catch (error) {
    showFlash(error.response?.data?.message || 'Gagal menyimpan data tiket.', 'error', 'GAGAL')
  }
}

const hapusTicket = async (id) => {
  if (confirm('Yakin ingin menghapus tiket ini?')) {
    try {
      await api.delete(`/tickets/${id}`)
      showFlash('Tiket berhasil dihapus.', 'success', 'BERHASIL')
      fetchTickets()
    } catch (error) {
      showFlash(error.response?.data?.message || 'Gagal menghapus tiket.', 'error', 'GAGAL')
    }
  }
}

const formatNumber = (val) => {
  return new Intl.NumberFormat('id-ID').format(Number(val) || 0)
}
</script>

<template>
  <AdminLayout>
    <div class="tiket-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manajemen Tiket</h1>
          <p class="text-muted text-sm">Kelola kategori tiket, harga, dan stok event</p>
        </div>
        <button @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Tiket
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
              placeholder="Cari nama tiket atau event..."
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-btn" title="Hapus pencarian">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Filter Event -->
          <select v-model="filterEvent" class="form-control filter-select">
            <option value="">Semua Event</option>
            <option v-for="ev in events" :key="ev.id" :value="ev.id">
              {{ ev.nama_event }}
            </option>
          </select>

          <!-- Filter Stok -->
          <select v-model="filterStok" class="form-control filter-select">
            <option value="">Semua Stok</option>
            <option value="tersedia">Stok Tersedia</option>
            <option value="habis">Stok Habis</option>
          </select>

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
            Menampilkan <strong>{{ filteredTickets.length }}</strong> dari <strong>{{ tickets.length }}</strong> tiket
          </span>
        </div>
      </div>

      <!-- Table View Card -->
      <div class="card">
        <div class="table-container">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p class="text-muted text-sm mt-2">Memuat data tiket...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 70px;">ID</th>
                <th>Nama Event</th>
                <th>Jenis Tiket</th>
                <th>Harga</th>
                <th>Stok Sisa</th>
                <th style="text-align: right; width: 180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in paginatedTickets" :key="t.id">
                <td>
                  <span class="badge badge-neutral">#{{ t.id }}</span>
                </td>
                <td>
                  <strong class="font-semibold text-main">{{ t.event?.nama_event || 'Event #' + t.event_id }}</strong>
                </td>
                <td>
                  <span class="badge badge-purple">{{ t.nama_tiket }}</span>
                </td>
                <td class="font-semibold text-main">
                  Rp {{ formatNumber(t.harga) }}
                </td>
                <td>
                  <span :class="t.stok > 0 ? 'badge badge-success' : 'badge badge-danger'">
                    {{ t.stok }} tiket
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="d-flex gap-2 justify-end">
                    <button @click="openEditForm(t)" class="btn btn-secondary btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      Edit
                    </button>
                    <button @click="hapusTicket(t.id)" class="btn btn-danger btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedTickets.length === 0">
                <td colspan="6" class="text-center text-muted py-4">
                  {{ hasActiveFilter ? 'Tidak ada tiket yang cocok dengan filter.' : 'Belum ada tiket yang ditambahkan.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredTickets.length > perPage" class="pagination-wrapper">
          <div class="pagination-info">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredTickets.length) }} dari {{ filteredTickets.length }} data
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

    <!-- MODAL TAMBAH / EDIT TIKET -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="font-bold text-base">{{ isEdit ? 'Edit Data Tiket' : 'Tambah Tiket Baru' }}</h3>
          <button @click="showForm = false" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveTicket">
            <div class="form-group mb-3">
              <label class="form-label">Pilih Event</label>
              <select v-model="form.event_id" class="form-control" required>
                <option value="" disabled>-- Pilih Event --</option>
                <option v-for="ev in events" :key="ev.id" :value="ev.id">
                  {{ ev.nama_event }}
                </option>
              </select>
            </div>
            <div class="form-group mb-3">
              <label class="form-label">Nama / Tier Tiket</label>
              <input type="text" v-model="form.nama_tiket" class="form-control" placeholder="Contoh: Early Bird, Presale 1, VIP..." required>
            </div>
            <div class="form-row mb-4">
              <div class="form-group flex-1">
                <label class="form-label">Harga (Rp)</label>
                <input type="number" v-model="form.harga" class="form-control" min="0" step="1000" placeholder="100000" required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Stok Tiket</label>
                <input type="number" v-model="form.stok" class="form-control" min="0" placeholder="100" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="showForm = false" class="btn btn-outline">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
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
  max-width: 200px;
  min-width: 140px;
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding-top: 0.5rem;
}
</style>
