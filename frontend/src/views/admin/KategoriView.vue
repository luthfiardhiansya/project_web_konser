<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const kategoris = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({ id: null, nama_kategori: '', deskripsi: '' })

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
        <button v-if="!showForm" @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Kategori
        </button>
      </div>

      <!-- Form Edit/Tambah Card -->
      <div v-if="showForm" class="card form-card mb-4">
        <div class="card-header">
          <h3 class="font-semibold text-base">{{ isEdit ? 'Edit Data Kategori' : 'Tambah Kategori Baru' }}</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveKategori" class="space-y-4">
            <div class="form-row">
              <div class="form-group flex-1">
                <label class="form-label">Nama Kategori</label>
                <input type="text" v-model="form.nama_kategori" class="form-control" placeholder="Contoh: Indie, Rock, Jazz, Festival..." required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Deskripsi</label>
                <input type="text" v-model="form.deskripsi" class="form-control" placeholder="Deskripsi singkat kategori (opsional)">
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Simpan</button>
              <button type="button" @click="showForm = false" class="btn btn-outline">Batal</button>
            </div>
          </form>
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
              <tr v-for="k in kategoris" :key="k.id">
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
              <tr v-if="kategoris.length === 0">
                <td colspan="4" class="text-center text-muted py-4">Belum ada kategori yang ditambahkan.</td>
              </tr>
            </tbody>
          </table>
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
</style>
