<script setup>
import { ref, onMounted } from 'vue'
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
        <button v-if="!showForm" @click="openAddForm" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Input Pembayaran
        </button>
      </div>

      <!-- Form Card -->
      <div v-if="showForm" class="card form-card mb-4">
        <div class="card-header">
          <h3 class="font-semibold text-base">{{ isEdit ? 'Edit Data Pembayaran' : 'Input Pembayaran Baru' }}</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="savePayment">
            <div class="form-row mb-2">
              <div class="form-group flex-1">
                <label class="form-label">Pilih Pesanan (Order ID / Kode)</label>
                <select v-model="form.order_id" @change="onOrderChange" class="form-control" :disabled="isEdit" required>
                  <option value="" disabled>-- Pilih Pesanan --</option>
                  <option v-for="ord in orders" :key="ord.id" :value="ord.id">
                    #{{ ord.kode_pesanan }} - {{ ord.user?.name || 'User #' + ord.user_id }} (Rp {{ formatNumber(ord.total_harga) }})
                  </option>
                </select>
              </div>
              <div class="form-group flex-1">
                <label class="form-label">Metode Pembayaran</label>
                <input type="text" v-model="form.metode_pembayaran" class="form-control" placeholder="BCA / Mandiri / QRIS..." required>
              </div>
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

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
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
              <tr v-for="p in payments" :key="p.id">
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
              <tr v-if="payments.length === 0">
                <td colspan="7" class="text-center text-muted py-4">Belum ada data pembayaran.</td>
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
