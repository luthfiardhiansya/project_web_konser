<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const kategoris = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({ id: null, nama_kategori: '', deskripsi: '' })

// Search & Filter
const searchQuery = ref('')

// Pagination
const currentPage = ref(1)
const perPage = 10

const fetchKategoris = async () => {
  loading.value = true
  try {
    const response = await api.get('/categories')
    kategoris.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil kategori:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchKategoris()
})

// Reset page saat search berubah
watch(searchQuery, () => { currentPage.value = 1 })

const filteredKategoris = computed(() => {
  let data = kategoris.value
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    data = data.filter(k =>
      k.nama_kategori?.toLowerCase().includes(q) ||
      k.deskripsi?.toLowerCase().includes(q)
    )
  }
  return data
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredKategoris.value.length / perPage)))

const paginatedKategoris = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredKategoris.value.slice(start, start + perPage)
})

const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

const openAddForm = () => {
  isEdit.value = false
  form.value = { id: null, nama_kategori: '', deskripsi: '' }
  showForm.value = true
}

const openEditForm = (kategori) => {
  isEdit.value = true
  form.value = { ...kategori }
  showForm.value = true
}

const saveKategori = async () => {
  try {
    if (isEdit.value) {
      await api.put(`/categories/${form.value.id}`, form.value)
      showFlash('Kategori berhasil diperbarui!', 'success', 'BERHASIL')
    } else {
      await api.post('/categories', form.value)
      showFlash('Kategori baru berhasil ditambahkan!', 'success', 'BERHASIL')
    }
    showForm.value = false
    fetchKategoris()
  } catch (error) {
    showFlash(error.response?.data?.message || 'Gagal menyimpan data kategori.', 'error', 'GAGAL')
  }
}

const hapusKategori = async (id) => {
  if (confirm('Yakin ingin menghapus kategori ini?')) {
    try {
      await api.delete(`/categories/${id}`)
      showFlash('Kategori berhasil dihapus.', 'success', 'BERHASIL')
      fetchKategoris()
    } catch (error) {
      showFlash(error.response?.data?.message || 'Gagal menghapus kategori.', 'error', 'GAGAL')
    }
  }
}
</script>

<template>
  <AdminLayout>
    <div class="kategori-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manajemen Kategori</h1>
          <p class="text-muted text-sm">Kelola pengelompokan jenis event musik</p>
        </div>
        <button @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Kategori
        </button>
      </div>

      <!-- Search & Filter Bar -->
      <div class="filter-bar card mb-3">
        <div class="filter-bar-inner">
          <div class="search-group">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control search-input"
              placeholder="Cari nama kategori atau deskripsi..."
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="clear-btn" title="Hapus pencarian">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <div class="filter-info">
            <span class="text-muted text-sm">
              Menampilkan <strong>{{ filteredKategoris.length }}</strong> dari <strong>{{ kategoris.length }}</strong> kategori
            </span>
          </div>
        </div>
      </div>

      <!-- Table View Card -->
      <div class="card">
        <div class="table-container">
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p class="text-muted text-sm mt-2">Memuat data kategori...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 80px;">ID</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th style="text-align: right; width: 180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="k in paginatedKategoris" :key="k.id">
                <td>
                  <span class="badge badge-neutral">#{{ k.id }}</span>
                </td>
                <td>
                  <strong class="font-semibold text-main">{{ k.nama_kategori }}</strong>
                </td>
                <td class="text-muted">
                  {{ k.deskripsi || '-' }}
                </td>
                <td style="text-align: right;">
                  <div class="d-flex gap-2 justify-end">
                    <button @click="openEditForm(k)" class="btn btn-secondary btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      Edit
                    </button>
                    <button @click="hapusKategori(k.id)" class="btn btn-danger btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedKategoris.length === 0">
                <td colspan="4" class="text-center text-muted py-4">
                  {{ searchQuery ? 'Tidak ada kategori yang cocok dengan pencarian.' : 'Belum ada kategori yang ditambahkan.' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && filteredKategoris.length > perPage" class="pagination-wrapper">
          <div class="pagination-info">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredKategoris.length) }} dari {{ filteredKategoris.length }} data
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

    <!-- MODAL TAMBAH / EDIT KATEGORI -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="font-bold text-base">{{ isEdit ? 'Edit Data Kategori' : 'Tambah Kategori Baru' }}</h3>
          <button @click="showForm = false" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveKategori">
            <div class="form-group mb-3">
              <label class="form-label">Nama Kategori</label>
              <input type="text" v-model="form.nama_kategori" class="form-control" placeholder="Contoh: Indie, Rock, Jazz, Festival..." required>
            </div>
            <div class="form-group mb-4">
              <label class="form-label">Deskripsi</label>
              <input type="text" v-model="form.deskripsi" class="form-control" placeholder="Deskripsi singkat kategori (opsional)">
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
  padding: 0.75rem 1rem;
}

.filter-bar-inner {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
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

.clear-btn:hover {
  color: var(--text-main, #222);
}

.filter-info {
  white-space: nowrap;
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
