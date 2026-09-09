<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const events = ref([])
const categories = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({
  id: null,
  category_id: '',
  nama_event: '',
  deskripsi: '',
  tanggal: '',
  waktu: '',
  lokasi: '',
  alamat: '',
  poster: '',
  status: 'aktif'
})

// Search & Filter
const searchQuery = ref('')
const filterKategori = ref('')
const filterStatus = ref('')
const filterTanggalDari = ref('')
const filterTanggalSampai = ref('')

// Pagination
const currentPage = ref(1)
const perPage = 10

const fetchEvents = async () => {
  loading.value = true
  try {
    const response = await api.get('/events')
    events.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil event:', error)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil kategori:', error)
  }
}

onMounted(() => {
  fetchEvents()
  fetchCategories()
})

// Reset page saat filter berubah
watch([searchQuery, filterKategori, filterStatus, filterTanggalDari, filterTanggalSampai], () => {
  currentPage.value = 1
})

const filteredEvents = computed(() => {
  let data = events.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    data = data.filter(e =>
      e.nama_event?.toLowerCase().includes(q) ||
      e.lokasi?.toLowerCase().includes(q) ||
      e.deskripsi?.toLowerCase().includes(q) ||
      e.category?.nama_kategori?.toLowerCase().includes(q)
    )
  }

  if (filterKategori.value) {
    data = data.filter(e => String(e.category_id) === String(filterKategori.value))
  }

  if (filterStatus.value) {
    data = data.filter(e => e.status === filterStatus.value)
  }

  if (filterTanggalDari.value) {
    data = data.filter(e => e.tanggal >= filterTanggalDari.value)
  }

  if (filterTanggalSampai.value) {
    data = data.filter(e => e.tanggal <= filterTanggalSampai.value)
  }

  return data
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredEvents.value.length / perPage)))

const paginatedEvents = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredEvents.value.slice(start, start + perPage)
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
  searchQuery.value || filterKategori.value || filterStatus.value || filterTanggalDari.value || filterTanggalSampai.value
)

const resetFilters = () => {
  searchQuery.value = ''
  filterKategori.value = ''
  filterStatus.value = ''
  filterTanggalDari.value = ''
  filterTanggalSampai.value = ''
}

const openAddForm = () => {
  isEdit.value = false
  form.value = {
    id: null,
    category_id: categories.value[0]?.id || '',
    nama_event: '',
    deskripsi: '',
    tanggal: new Date().toISOString().split('T')[0],
    waktu: '19:00',
    lokasi: '',
    alamat: '',
    poster: '',
    status: 'aktif'
  }
  showForm.value = true
}

const openEditForm = (eventItem) => {
  isEdit.value = true
  form.value = {
    ...eventItem,
    category_id: eventItem.category_id || eventItem.category?.id || ''
  }
  showForm.value = true
}

const saveEvent = async () => {
  try {
    if (isEdit.value) {
      await api.put(`/events/${form.value.id}`, form.value)
      showFlash('Data event berhasil diperbarui!', 'success', 'BERHASIL')
    } else {
      await api.post('/events', form.value)
      showFlash('Event baru berhasil ditambahkan!', 'success', 'BERHASIL')
    }
    showForm.value = false
    fetchEvents()
  } catch (error) {
    showFlash(error.response?.data?.message || 'Gagal menyimpan data event.', 'error', 'GAGAL')
  }
}

const hapusEvent = async (id) => {
  if (confirm('Yakin ingin menghapus event ini?')) {
    try {
      await api.delete(`/events/${id}`)
      showFlash('Event berhasil dihapus.', 'success', 'BERHASIL')
      fetchEvents()
    } catch (error) {
      showFlash(error.response?.data?.message || 'Gagal menghapus event.', 'error', 'GAGAL')
    }
  }
}
</script>

<template>
  <AdminLayout>
    <div class="event-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manajemen Event</h1>
          <p class="text-muted text-sm">Kelola daftar konser, festival, dan gigs musik</p>
        </div>
        <button @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Event
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
              placeholder="Cari nama event, lokasi..."
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-btn" title="Hapus pencarian">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <!-- Filter Kategori -->
          <select v-model="filterKategori" class="form-control filter-select">
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.nama_kategori }}
            </option>
          </select>

          <!-- Filter Status -->
          <select v-model="filterStatus" class="form-control filter-select">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
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
            Menampilkan <strong>{{ filteredEvents.length }}</strong> dari <strong>{{ events.length }}</strong> event
          </span>
        </div>
      </div>

      <!-- Table View Card -->
      <div class="card">
        <div class="table-container">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p class="text-muted text-sm mt-2">Memuat data event...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 70px;">ID</th>
                <th>Nama Event</th>
                <th>Kategori</th>
                <th>Tanggal & Waktu</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th style="text-align: right; width: 180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in paginatedEvents" :key="e.id">
                <td>
                  <span class="badge badge-neutral">#{{ e.id }}</span>
                </td>
                <td>
                  <strong class="font-semibold text-main">{{ e.nama_event }}</strong>
                </td>
                <td>
                  <span class="badge badge-purple">{{ e.category?.nama_kategori || 'General' }}</span>
                </td>
                <td class="text-sm">
                  {{ e.tanggal }} • {{ e.waktu }}
                </td>
                <td class="text-sm text-muted">
                  {{ e.lokasi }}
                </td>
                <td>
                  <span :class="{
                    'badge badge-success': e.status === 'aktif',
                    'badge badge-neutral': e.status === 'selesai',
                    'badge badge-danger': e.status === 'dibatalkan'
                  }">
                    {{ e.status }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="d-flex gap-2 justify-end">
                    <button @click="openEditForm(e)" class="btn btn-secondary btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      Edit
                    </button>
                    <button @click="hapusEvent(e.id)" class="btn btn-danger btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedEvents.length === 0">
                <td colspan="7" class="text-center text-muted py-4">
                  {{ hasActiveFilter ? 'Tidak ada event yang cocok dengan filter.' : 'Belum ada event yang ditambahkan.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredEvents.length > perPage" class="pagination-wrapper">
          <div class="pagination-info">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredEvents.length) }} dari {{ filteredEvents.length }} data
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

    <!-- MODAL TAMBAH / EDIT EVENT -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card modal-card-lg">
        <div class="modal-header">
          <h3 class="font-bold text-base">{{ isEdit ? 'Edit Data Event' : 'Tambah Event Baru' }}</h3>
          <button @click="showForm = false" class="btn-close">✕</button>
        </div>
        <div class="modal-body modal-scroll">
          <form @submit.prevent="saveEvent">
            <div class="form-row mb-3">
              <div class="form-group flex-1">
                <label class="form-label">Nama Event</label>
                <input type="text" v-model="form.nama_event" class="form-control" placeholder="Contoh: Bandung Indie Fest 2026" required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Kategori</label>
                <select v-model="form.category_id" class="form-control" required>
                  <option value="" disabled>-- Pilih Kategori --</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.nama_kategori }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row mb-3">
              <div class="form-group flex-1">
                <label class="form-label">Tanggal</label>
                <input type="date" v-model="form.tanggal" class="form-control" required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Waktu</label>
                <input type="text" v-model="form.waktu" class="form-control" placeholder="19:00 WIB" required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Status</label>
                <select v-model="form.status" class="form-control" required>
                  <option value="aktif">Aktif</option>
                  <option value="selesai">Selesai</option>
                  <option value="dibatalkan">Dibatalkan</option>
                </select>
              </div>
            </div>

            <div class="form-row mb-3">
              <div class="form-group flex-1">
                <label class="form-label">Lokasi / Venue</label>
                <input type="text" v-model="form.lokasi" class="form-control" placeholder="Contoh: Gudang Selatan, Bandung" required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">URL Poster (Opsional)</label>
                <input type="text" v-model="form.poster" class="form-control" placeholder="https://example.com/poster.jpg">
              </div>
            </div>

            <div class="form-group mb-4">
              <label class="form-label">Deskripsi Event</label>
              <textarea v-model="form.deskripsi" class="form-control" rows="3" placeholder="Jelaskan rincian acara..." required></textarea>
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
  max-width: 160px;
  min-width: 130px;
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
  max-width: 560px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}

.modal-card-lg {
  max-width: 720px;
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

.modal-scroll {
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
