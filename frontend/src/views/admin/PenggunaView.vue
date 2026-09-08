<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const users = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({ id: null, name: '', email: '', password: '', role: 'user' })

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await api.get('/users')
    users.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Gagal mengambil pengguna:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchUsers()
})

const openAddForm = () => {
  isEdit.value = false
  form.value = { id: null, name: '', email: '', password: '', role: 'user' }
  showForm.value = true
}

const openEditForm = (user) => {
  isEdit.value = true
  form.value = { ...user, password: '' }
  showForm.value = true
}

const saveUser = async () => {
  try {
    if (isEdit.value) {
      await api.put(`/users/${form.value.id}`, form.value)
      showFlash('Data pengguna berhasil diperbarui!', 'success', 'BERHASIL')
    } else {
      await api.post('/users', form.value)
      showFlash('Pengguna baru berhasil ditambahkan!', 'success', 'BERHASIL')
    }
    showForm.value = false
    fetchUsers()
  } catch (error) {
    showFlash(error.response?.data?.message || 'Gagal menyimpan data pengguna.', 'error', 'GAGAL')
  }
}

const hapusUser = async (id) => {
  if (confirm('Yakin ingin menghapus pengguna ini?')) {
    try {
      await api.delete(`/users/${id}`)
      showFlash('Pengguna berhasil dihapus.', 'success', 'BERHASIL')
      fetchUsers()
    } catch (error) {
      showFlash(error.response?.data?.message || 'Gagal menghapus pengguna.', 'error', 'GAGAL')
    }
  }
}
</script>

<template>
  <AdminLayout>
    <div class="pengguna-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Manajemen Pengguna</h1>
          <p class="text-muted text-sm">Kelola pengguna terdaftar dan hak akses role admin/user</p>
        </div>
        <button v-if="!showForm" @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Pengguna
        </button>
      </div>

      <!-- Form Card -->
      <div v-if="showForm" class="card form-card mb-4">
        <div class="card-header">
          <h3 class="font-semibold text-base">{{ isEdit ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveUser">
            <div class="form-row mb-2">
              <div class="form-group flex-1">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" v-model="form.name" class="form-control" placeholder="Nama pengguna..." required>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Email</label>
                <input type="email" v-model="form.email" class="form-control" placeholder="user@example.com" required>
              </div>
            </div>

            <div class="form-row mb-4">
              <div class="form-group flex-1">
                <label class="form-label">{{ isEdit ? 'Password Baru (Opsional)' : 'Password' }}</label>
                <input type="password" v-model="form.password" class="form-control" :placeholder="isEdit ? 'Kosongkan jika tidak ingin diubah' : 'Minimal 6 karakter'" :required="!isEdit">
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Role Akses</label>
                <select v-model="form.role" class="form-control" required>
                  <option value="user">User Biasa</option>
                  <option value="admin">Administrator</option>
                </select>
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
            <p class="text-muted text-sm mt-2">Memuat data pengguna...</p>
          </div>

          <table v-else class="table-custom">
            <thead>
              <tr>
                <th style="width: 70px;">ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th style="text-align: right; width: 180px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in users" :key="u.id">
                <td>
                  <span class="badge badge-neutral">#{{ u.id }}</span>
                </td>
                <td>
                  <strong class="font-semibold text-main">{{ u.name }}</strong>
                </td>
                <td class="text-muted">
                  {{ u.email }}
                </td>
                <td>
                  <span :class="u.role === 'admin' ? 'badge badge-purple' : 'badge badge-neutral'">
                    {{ u.role || 'user' }}
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="d-flex gap-2 justify-end">
                    <button @click="openEditForm(u)" class="btn btn-secondary btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                      Edit
                    </button>
                    <button @click="hapusUser(u.id)" class="btn btn-danger btn-sm">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="users.length === 0">
                <td colspan="5" class="text-center text-muted py-4">Belum ada data pengguna.</td>
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
