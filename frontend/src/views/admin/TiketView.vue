<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../components/AdminLayout.vue'
import api from '../../utils/api'
import { showFlash } from '../../utils/flash'

const tickets = ref([])
const events = ref([])
const loading = ref(true)

const showForm = ref(false)
const isEdit = ref(false)
const form = ref({ id: null, event_id: '', nama_tiket: '', harga: 0, stok: 0 })

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
        <button v-if="!showForm" @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Tambah Tiket
        </button>
      </div>

      <!-- Form Card -->
      <div v-if="showForm" class="card form-card mb-4">
        <div class="card-header">
          <h3 class="font-semibold text-base">{{ isEdit ? 'Edit Data Tiket' : 'Tambah Tiket Baru' }}</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveTicket">
            <div class="form-row mb-2">
              <div class="form-group flex-1">
                <label class="form-label">Pilih Event</label>
                <select v-model="form.event_id" class="form-control" required>
                  <option value="" disabled>-- Pilih Event --</option>
                  <option v-for="ev in events" :key="ev.id" :value="ev.id">
                    {{ ev.nama_event }}
                  </option>
                </select>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Nama / Tier Tiket</label>
                <input type="text" v-model="form.nama_tiket" class="form-control" placeholder="Contoh: Early Bird, Presale 1, VIP..." required>
              </div>
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
              <tr v-for="t in tickets" :key="t.id">
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
              <tr v-if="tickets.length === 0">
                <td colspan="6" class="text-center text-muted py-4">Belum ada tiket yang ditambahkan.</td>
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
