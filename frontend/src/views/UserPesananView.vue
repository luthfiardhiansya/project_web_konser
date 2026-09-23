<template>
  <div class="min-h-screen bg-[#f5f5f0] flex flex-col">

    <!-- NAVBAR -->
    <Navbar
      :currentView="currentView"
      :favoritesCount="favorites.length"
      @navigate="navigateTo"
      @open-search="isQuickSearchOpen = true"
      @filter-category="filterCategoryQuick"
    />

    <!-- QUICK SEARCH OVERLAY -->
    <div
      v-if="isQuickSearchOpen"
      class="fixed inset-0 bg-black/60 z-50 flex items-start justify-center pt-20 px-4"
      @click.self="isQuickSearchOpen = false"
    >
      <div class="bg-[#f5f5f0] border-4 border-black shadow-[8px_8px_0_#000] w-full max-w-2xl p-6 relative">
        <button
          @click="isQuickSearchOpen = false"
          class="absolute top-4 right-4 font-black text-xl hover:bg-[#FFD84D] px-2 border border-black"
        >✕</button>
        <h3 class="font-black text-xl mb-4 uppercase">Cari Event Musik Bandung</h3>
        <div class="flex gap-2">
          <input
            type="text"
            v-model="quickSearchQuery"
            @keyup.enter="isQuickSearchOpen = false"
            placeholder="Cari event..."
            class="border-4 border-black bg-white px-3 py-2 font-bold flex-1 outline-none focus:bg-yellow-100"
          />
          <button
            @click="isQuickSearchOpen = false"
            class="border-4 border-black bg-black text-white px-5 font-black uppercase"
          >Cari</button>
        </div>
      </div>
    </div>

    <!-- MAIN -->
    <main class="flex-1 px-4 py-8 md:px-8 lg:px-12">
      <div class="max-w-6xl mx-auto">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
          <div>
            <p class="text-xs font-black uppercase tracking-[3px] mb-2">Riwayat Transaksi</p>
            <h1 class="text-4xl md:text-5xl font-black uppercase leading-none">Pesanan Saya</h1>
            <p class="mt-3 text-sm font-bold text-gray-600">
              Daftar semua pesanan tiket yang pernah kamu buat.
            </p>
          </div>
          <div class="flex gap-3">
      
            <button
              @click="$router.push('/')"
              class="border-4 border-black bg-white px-4 py-3 font-black uppercase text-sm shadow-[5px_5px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
            >
              ← Beranda
            </button>
          </div>
        </div>

        <!-- FILTER BAR -->
        <div class="border-4 border-black bg-white shadow-[5px_5px_0_#000] p-5 mb-6">

  <!-- =========================
       FILTER SEJAJAR
  ========================== -->
  <div
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
  >

    <!-- SEARCH -->
    <div>
      <label class="block text-xs font-black uppercase mb-2">
        Cari Pesanan
      </label>

      <div class="relative">

        <!-- ICON SEARCH -->
        <svg
          class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
        >
          <circle cx="11" cy="11" r="8"></circle>
          <line
            x1="21"
            y1="21"
            x2="16.65"
            y2="16.65"
          ></line>
        </svg>

        <input
          v-model="searchQuery"
          type="text"
          placeholder="Kode pesanan, nama event..."
          class="w-full border-4 border-black bg-white pl-9 pr-8 py-2.5 text-sm font-bold outline-none focus:bg-yellow-50"
        />

        <!-- CLEAR SEARCH -->
        <button
          v-if="searchQuery"
          @click="searchQuery = ''"
          type="button"
          class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black font-black text-lg leading-none"
        >
          ×
        </button>

      </div>
    </div>


    <!-- STATUS -->
    <div>
      <label class="block text-xs font-black uppercase mb-2">
        Status Pembayaran
      </label>

      <select
        v-model="filterStatus"
        class="w-full border-4 border-black bg-white px-3 py-2.5 text-sm font-bold outline-none focus:bg-yellow-50"
      >
        <option value="">
          Semua Status
        </option>

        <option value="dibayar">
          Sudah Dibayar
        </option>

        <option value="pending">
          Belum Dibayar
        </option>

        <option value="dibatalkan">
          Dibatalkan
        </option>
      </select>
    </div>


    <!-- DARI TANGGAL -->
    <div>
      <label class="block text-xs font-black uppercase mb-2">
        Dari Tanggal
      </label>

      <input
        v-model="filterDateFrom"
        type="date"
        class="w-full border-4 border-black bg-white px-3 py-2.5 text-sm font-bold outline-none focus:bg-yellow-50"
      />
    </div>


    <!-- SAMPAI TANGGAL -->
    <div>
      <label class="block text-xs font-black uppercase mb-2">
        Sampai Tanggal
      </label>

      <input
        v-model="filterDateTo"
        type="date"
        class="w-full border-4 border-black bg-white px-3 py-2.5 text-sm font-bold outline-none focus:bg-yellow-50"
      />
    </div>

  </div>


  <!-- =========================
       INFO + RESET
  ========================== -->
  <div
    class="flex items-center justify-between border-t-2 border-black pt-3"
  >

    <!-- INFO -->
    <p class="text-xs font-bold text-gray-600">

      Menampilkan

      <span class="font-black text-black">
        {{ filtered.length }}
      </span>

      dari

      <span class="font-black text-black">
        {{ orders.length }}
      </span>

      pesanan

    </p>


    <!-- RESET -->
    <button
      v-if="hasActiveFilter"
      @click="resetFilters"
      type="button"
      class="text-xs font-black uppercase underline hover:no-underline flex items-center gap-1"
    >

      <svg
        width="12"
        height="12"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
      >
        <polyline points="1 4 1 10 7 10"></polyline>

        <path
          d="M3.51 15a9 9 0 1 0 .49-3.09"
        ></path>
      </svg>

      Reset Filter

    </button>

  </div>

</div>

        <!-- LOADING -->
        <div v-if="loading" class="border-4 border-black bg-white shadow-[5px_5px_0_#000] p-12 text-center">
          <div class="inline-block w-8 h-8 border-4 border-black border-t-[#FFD84D] rounded-full animate-spin mb-3"></div>
          <p class="font-black uppercase text-sm">Memuat pesanan...</p>
        </div>

        <!-- ERROR -->
        <div v-else-if="error" class="border-4 border-black bg-red-100 shadow-[5px_5px_0_#000] p-8 text-center space-y-4">
          <div class="text-4xl"></div>
          <h2 class="text-xl font-black uppercase">Gagal Memuat Pesanan</h2>
          <p class="font-bold text-sm text-gray-700">{{ error }}</p>
          <button @click="fetchOrders" class="border-4 border-black bg-black text-white px-6 py-3 font-black uppercase shadow-[4px_4px_0_#111]">
            Coba Lagi
          </button>
        </div>

        <!-- EMPTY -->
        <div v-else-if="filtered.length === 0 && !loading" class="border-4 border-black bg-white shadow-[5px_5px_0_#000] p-12 text-center space-y-4">
          <div class="text-5xl"></div>
          <h2 class="text-2xl font-black uppercase">
            {{ hasActiveFilter ? 'Tidak Ada Pesanan yang Cocok' : 'Belum Ada Pesanan' }}
          </h2>
          <p class="font-bold text-sm text-gray-600">
            {{ hasActiveFilter ? 'Coba ubah filter pencarian kamu.' : 'Kamu belum melakukan pemesanan tiket.' }}
          </p>
          <div class="flex justify-center gap-3">
            <button v-if="hasActiveFilter" @click="resetFilters" class="border-4 border-black bg-[#FFD84D] px-5 py-2.5 font-black uppercase text-sm shadow-[4px_4px_0_#000]">
              Reset Filter
            </button>
            <button @click="$router.push('/')" class="border-4 border-black bg-black text-white px-5 py-2.5 font-black uppercase text-sm shadow-[4px_4px_0_#000]">
              Cari Event
            </button>
          </div>
        </div>

        <!-- DAFTAR PESANAN -->
        <div v-else class="space-y-4">
          <div
            v-for="order in paginated"
            :key="order.id"
            class="border-4 border-black bg-white shadow-[5px_5px_0_#000]"
          >
            <!-- PESANAN HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 border-b-4 border-black">
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-[10px] font-black uppercase tracking-widest text-gray-500">Kode Pesanan</span>
                  <span
                    :class="statusClass(order.status)"
                    class="text-[10px] font-black uppercase px-2 py-0.5 border-2 border-black"
                  >{{ statusLabel(order.status) }}</span>
                </div>
                <p class="font-black text-lg uppercase mt-0.5 font-mono">{{ order.kode_pesanan }}</p>
              </div>
              <div class="text-right">
                <div class="text-[10px] font-black uppercase text-gray-500">Total Bayar</div>
                <div class="text-xl font-black">{{ formatRupiah(order.total_harga) }}</div>
                <div class="text-xs font-bold text-gray-500 mt-0.5">{{ formatDate(order.created_at) }}</div>
              </div>
            </div>

            <!-- PESANAN DETAIL TIKET -->
            <div class="p-4 space-y-2">
              <div
                v-for="item in (order.order_details || [])"
                :key="item.id"
                class="flex items-center justify-between gap-3 text-sm"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <span class="text-lg shrink-0"></span>
                  <div class="min-w-0">
                    <p class="font-black uppercase truncate">{{ item.ticket?.event?.nama_event || 'Event' }}</p>
                    <p class="text-xs font-bold text-gray-500">{{ item.ticket?.nama_tiket }} × {{ item.jumlah }}</p>
                  </div>
                </div>
                <p class="font-black shrink-0">{{ formatRupiah(item.subtotal) }}</p>
              </div>
            </div>

            <!-- PESANAN ACTIONS -->
            <div class="px-4 pb-4 flex flex-wrap items-center gap-2 border-t-2 border-black pt-3">
              <button
                @click="openDetail(order)"
                class="border-2 border-black bg-white px-4 py-2 text-xs font-black uppercase hover:bg-[#FFD84D] transition-colors"
              >
                <svg class="inline mr-1" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                Detail Pesanan
              </button>

              <button
                v-if="order.status === 'pending'"
                @click="bayarSekarang(order)"
                class="border-2 border-black bg-[#FFD84D] px-4 py-2 text-xs font-black uppercase hover:brightness-95 transition-all shadow-[3px_3px_0_#000]"
              >
                 Bayar Sekarang
              </button>
            </div>
          </div>
        </div>

        <!-- PAGINATION -->
        <div v-if="!loading && totalPages > 1" class="mt-8 flex flex-wrap items-center justify-between gap-4">
          <p class="text-xs font-black uppercase">
            Halaman {{ currentPage }} dari {{ totalPages }} &nbsp;|&nbsp;
            {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filtered.length) }} dari {{ filtered.length }}
          </p>
          <div class="flex items-center gap-1">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="border-4 border-black bg-white px-3 py-2 font-black disabled:opacity-40 hover:bg-[#FFD84D] transition-colors"
            >←</button>
            <button
              v-for="p in pageNumbers"
              :key="p"
              @click="goToPage(p)"
              :class="p === currentPage ? 'bg-black text-white' : 'bg-white hover:bg-[#FFD84D]'"
              class="border-4 border-black px-3 py-2 min-w-[42px] font-black transition-colors"
            >{{ p }}</button>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="border-4 border-black bg-white px-3 py-2 font-black disabled:opacity-40 hover:bg-[#FFD84D] transition-colors"
            >→</button>
          </div>
        </div>

      </div>
    </main>

    <!-- FOOTER -->
    <Footer @navigate="navigateTo" />

    <!-- ── MODAL DETAIL PESANAN ─────────────────────────────────────── -->
    <div
      v-if="selectedOrder"
      class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 overflow-y-auto"
      @click.self="selectedOrder = null"
    >
      <div class="bg-[#f5f5f0] border-4 border-black shadow-[10px_10px_0_#000] w-full max-w-2xl relative my-8">

        <!-- Modal Header -->
        <div class="flex items-center justify-between p-5 border-b-4 border-black bg-[#FFD84D]">
          <div>
            <span class="text-[10px] font-black uppercase tracking-widest">Detail Rincian</span>
            <h2 class="text-xl font-black uppercase">Pesanan #{{ selectedOrder.kode_pesanan }}</h2>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-5">

          <!-- Info Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="border-2 border-black bg-white p-3">
              <div class="text-[10px] font-black uppercase text-gray-500 mb-1">Status</div>
              <span :class="statusClass(selectedOrder.status)" class="text-xs font-black uppercase px-2 py-0.5 border-2 border-black">
                {{ statusLabel(selectedOrder.status) }}
              </span>
            </div>
            <div class="border-2 border-black bg-white p-3">
              <div class="text-[10px] font-black uppercase text-gray-500 mb-1">Tanggal Pesan</div>
              <p class="font-black text-sm">{{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <div class="border-2 border-black bg-white p-3">
              <div class="text-[10px] font-black uppercase text-gray-500 mb-1">Total Harga</div>
              <p class="font-black text-sm">{{ formatRupiah(selectedOrder.total_harga) }}</p>
            </div>
            <div class="border-2 border-black bg-white p-3 col-span-2">
              <div class="text-[10px] font-black uppercase text-gray-500 mb-1">Status Pembayaran</div>
              <p class="font-black text-sm">
                {{ selectedOrder.payment?.status || selectedOrder.status }}
                <span v-if="selectedOrder.payment?.dibayar_pada" class="font-bold text-gray-500 text-xs ml-2">
                  — {{ formatDate(selectedOrder.payment.dibayar_pada) }}
                </span>
              </p>
            </div>
            <div v-if="selectedOrder.payment?.metode_pembayaran" class="border-2 border-black bg-white p-3">
              <div class="text-[10px] font-black uppercase text-gray-500 mb-1">Metode Bayar</div>
              <p class="font-black text-sm">{{ selectedOrder.payment.metode_pembayaran }}</p>
            </div>
          </div>

          <!-- Tabel tiket -->
          <div>
            <h3 class="font-black uppercase text-sm mb-2 border-b-2 border-black pb-1">Tiket Dipesan</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-xs border-collapse border-2 border-black bg-white">
                <thead class="bg-[#FFD84D] border-b-2 border-black">
                  <tr>
                    <th class="p-2 border-r border-black text-left uppercase font-black">Event & Tiket</th>
                    <th class="p-2 border-r border-black text-center uppercase font-black">Jml</th>
                    <th class="p-2 border-r border-black text-right uppercase font-black">Harga Satuan</th>
                    <th class="p-2 text-right uppercase font-black">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.order_details" :key="item.id" class="border-b border-black/20">
                    <td class="p-2 border-r border-black">
                      <p class="font-black uppercase">{{ item.ticket?.event?.nama_event || 'Event' }}</p>
                      <p class="text-gray-500 font-bold">{{ item.ticket?.nama_tiket }}</p>
                    </td>
                    <td class="p-2 border-r border-black text-center font-bold">{{ item.jumlah }}</td>
                    <td class="p-2 border-r border-black text-right font-medium">{{ formatRupiah(item.harga_satuan) }}</td>
                    <td class="p-2 text-right font-black">{{ formatRupiah(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot class="border-t-2 border-black">
                  <tr>
                    <td colspan="3" class="p-2 text-right font-black uppercase">Total:</td>
                    <td class="p-2 text-right font-black text-base">{{ formatRupiah(selectedOrder.total_harga) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-5 pb-5 flex flex-wrap justify-end gap-2">
          <button
            v-if="selectedOrder.status === 'pending'"
            @click="bayarSekarang(selectedOrder); selectedOrder = null"
            class="border-4 border-black bg-[#FFD84D] px-5 py-2.5 text-sm font-black uppercase shadow-[4px_4px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
          >
            Bayar Sekarang
          </button>
          <button
            @click="selectedOrder = null"
            class="border-4 border-black bg-white px-5 py-2.5 text-sm font-black uppercase shadow-[4px_4px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../utils/api'
import { showHomeFlash } from '../utils/flash'
import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'

const router = useRouter()

// ── Navbar state ──────────────────────────────────────────────────────────────
const currentView     = ref('view-pesanan')
const isQuickSearchOpen = ref(false)
const quickSearchQuery  = ref('')
const favorites = ref(
  JSON.parse(localStorage.getItem('wishlist') || '[]').map(i => i.id)
)

const navigateTo = (view) => {
  const map = {
    'view-home'     : '/',
    'view-events'   : '/',
    'view-favorites': '/wishlist',
    'view-my-tickets': '/my-tickets',
    'view-profile'  : '/profile',
    'view-pesanan'  : '/pesanan',
  }
  if (map[view]) router.push(map[view])
}

const filterCategoryQuick = () => {}

// ── Wishlist sync ─────────────────────────────────────────────────────────────
const syncWishlist = () => {
  favorites.value = JSON.parse(localStorage.getItem('wishlist') || '[]').map(i => i.id)
}
window.addEventListener('wishlist-updated', syncWishlist)

// ── Data ─────────────────────────────────────────────────────────────────────
const orders  = ref([])
const loading = ref(true)
const error   = ref('')
const selectedOrder = ref(null)

// ── Filter & search ───────────────────────────────────────────────────────────
const searchQuery   = ref('')
const filterStatus  = ref('')
const filterDateFrom = ref('')
const filterDateTo   = ref('')

// ── Pagination ────────────────────────────────────────────────────────────────
const currentPage = ref(1)
const perPage     = 5

// Reset halaman saat filter berubah
watch([searchQuery, filterStatus, filterDateFrom, filterDateTo], () => {
  currentPage.value = 1
})

// ── Fetch ─────────────────────────────────────────────────────────────────────
const fetchOrders = async () => {
  loading.value = true
  error.value   = ''
  try {
    const user   = JSON.parse(localStorage.getItem('user') || '{}')
    const params = user.id ? { user_id: user.id } : {}
    const res    = await api.get('/orders', { params })
    orders.value = res.data.data || res.data || []
  } catch (err) {
    if (err.response?.status === 401) {
      error.value = 'Sesi login kamu sudah habis. Silakan login kembali.'
    } else {
      error.value = err.response?.data?.message || 'Gagal memuat data pesanan.'
    }
  } finally {
    loading.value = false
  }
}

onMounted(fetchOrders)

// ── Computed ─────────────────────────────────────────────────────────────────
const filtered = computed(() => {
  let data = orders.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    data = data.filter(o =>
      o.kode_pesanan?.toLowerCase().includes(q) ||
      o.order_details?.some(d =>
        d.ticket?.event?.nama_event?.toLowerCase().includes(q) ||
        d.ticket?.nama_tiket?.toLowerCase().includes(q)
      )
    )
  }

  if (filterStatus.value) {
    data = data.filter(o => o.status === filterStatus.value)
  }

  if (filterDateFrom.value) {
    data = data.filter(o => o.created_at?.substring(0, 10) >= filterDateFrom.value)
  }

  if (filterDateTo.value) {
    data = data.filter(o => o.created_at?.substring(0, 10) <= filterDateTo.value)
  }

  return data
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))

const paginated = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filtered.value.slice(start, start + perPage)
})

const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

const goToPage = (p) => {
  if (p >= 1 && p <= totalPages.value) {
    currentPage.value = p
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const hasActiveFilter = computed(() =>
  searchQuery.value || filterStatus.value || filterDateFrom.value || filterDateTo.value
)

const resetFilters = () => {
  searchQuery.value    = ''
  filterStatus.value   = ''
  filterDateFrom.value = ''
  filterDateTo.value   = ''
  currentPage.value    = 1
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatRupiah = (val) =>
  'Rp ' + new Intl.NumberFormat('id-ID').format(Number(val) || 0)

const formatDate = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

const statusLabel = (s) => ({
  pending   : 'Belum Dibayar',
  dibayar   : 'Sudah Dibayar',
  selesai   : 'Selesai',
  dibatalkan: 'Dibatalkan',
}[s] || s)

const statusClass = (s) => ({
  pending   : 'bg-yellow-300',
  dibayar   : 'bg-green-300',
  selesai   : 'bg-blue-200',
  dibatalkan: 'bg-red-300',
}[s] || 'bg-gray-200')

// ── Actions ───────────────────────────────────────────────────────────────────
const openDetail = (order) => {
  selectedOrder.value = order
}

const bayarSekarang = async (order) => {
  try {
    const res = await api.post('/payments/snap-token', { order_id: order.id })
    const snapToken = res.data.snap_token

    if (!snapToken) {
      showHomeFlash('Gagal mendapatkan token pembayaran.', 'error', 'PEMBAYARAN GAGAL')
      return
    }

    if (!window.snap) {
      showHomeFlash('SDK Midtrans belum dimuat.', 'error', 'MIDTRANS ERROR')
      return
    }

    window.snap.pay(snapToken, {
      onSuccess: async (result) => {
        await api.post('/payments/finish', {
          order_id: order.id,
          status: 'berhasil',
          metode_pembayaran: result.payment_type || 'Midtrans'
        })
        showHomeFlash('Pembayaran berhasil diproses!', 'success', 'PEMBAYARAN BERHASIL')
        fetchOrders()
      },
      onPending: () => {
        showHomeFlash('Pembayaran masih pending.', 'warning', 'PEMBAYARAN PENDING')
        fetchOrders()
      },
      onError: async () => {
        try {
          await api.post('/payments/finish', {
            order_id: order.id,
            status: 'gagal'
          })
        } catch (e) {}
        showHomeFlash('Pembayaran gagal atau dibatalkan.', 'error', 'PEMBAYARAN GAGAL')
        fetchOrders()
      },
      onClose: () => {
        fetchOrders()
      }
    })
  } catch (err) {
    showHomeFlash(
      err.response?.data?.message || 'Gagal memproses pembayaran.',
      'error',
      'PEMBAYARAN GAGAL'
    )
  }
}
</script>
