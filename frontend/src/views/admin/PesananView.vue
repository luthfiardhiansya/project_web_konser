<script setup>
import { ref, onMounted } from 'vue'
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

      <!-- Form Update Status Card -->
      <div v-if="showForm" class="card form-card mb-4">
        <div class="card-header flex justify-between items-center">
          <h3 class="font-semibold text-base">
            Update Status Pesanan <span class="badge badge-purple">#{{ selectedPesanan?.kode_pesanan }}</span>
          </h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="savePesananStatus" class="form-row">
            <div class="form-group flex-1">
              <label class="form-label">Pemesan</label>
              <input type="text" :value="selectedPesanan?.user?.name || 'User #' + selectedPesanan?.user_id" class="form-control" disabled>
            </div>
            <div class="form-group flex-1">
              <label class="form-label">Total Harga</label>
              <input type="text" :value="'Rp ' + formatNumber(selectedPesanan?.total_harga)" class="form-control" disabled>
            </div>
            <div class="form-group flex-1">
              <label class="form-label">Status Pesanan</label>
              <select v-model="form.status" class="form-control" required>
                <option value="pending">Pending (Menunggu Pembayaran)</option>
                <option value="dibayar">Dibayar (Lunas)</option>
                <option value="selesai">Selesai</option>
                <option value="dibatalkan">Dibatalkan</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Simpan Status</button>
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
              <tr v-for="order in pesananList" :key="order.id">
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
              <tr v-if="pesananList.length === 0">
                <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi pesanan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- MODAL DETAIL PESANAN (ADMIN) -->
      <div v-if="selectedDetailOrder" class="modal-overlay" @click.self="selectedDetailOrder = null">
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

.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-card {
  background-color: #ffffff;
  border-radius: 10px;
  width: 100%;
  max-width: 650px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1.25rem;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  font-weight: bold;
  cursor: pointer;
  color: #6b7280;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.85rem;
  background-color: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}
</style>
