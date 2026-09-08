<script setup>
import { ref, onMounted } from 'vue'
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
        <button v-if="!showForm" @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Event
        </button>
      </div>

      <!-- Form Card -->
      <div v-if="showForm" class="card form-card mb-4">
        <div class="card-header">
          <h3 class="font-semibold text-base">{{ isEdit ? 'Edit Data Event' : 'Tambah Event Baru' }}</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveEvent">
            <div class="form-row mb-2">
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

            <div class="form-row mb-2">
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

            <div class="form-row mb-2">
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
              <tr v-for="e in events" :key="e.id">
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
              <tr v-if="events.length === 0">
                <td colspan="7" class="text-center text-muted py-4">Belum ada event yang ditambahkan.</td>
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
