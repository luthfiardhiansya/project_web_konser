<template>
  <div class="min-h-screen flex flex-col justify-between selection:bg-accent selection:text-ink bg-paper">

    <!-- HEADER & NAVBAR -->
    <Navbar @navigate="(v) => $router.push('/')" />

    <!-- MAIN CONTENT EVENT DETAIL -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 w-full">
      <!-- BACK BUTTON -->
      <div>
        <button @click="$router.push('/')" class="font-bold text-xs uppercase flex items-center gap-2 nb-btn nb-btn-secondary px-3 py-1.5">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </button>
      </div>

      <!-- LOADING STATE -->
      <div v-if="loading" class="text-center py-16 space-y-4">
        <div class="inline-block w-8 h-8 border-4 border-ink border-t-accent rounded-full animate-spin"></div>
        <p class="font-black text-sm uppercase tracking-wider">Memuat Detail Event...</p>
      </div>

      <!-- ERROR STATE -->
      <div v-else-if="error" class="nb-card bg-red-100 p-8 text-center space-y-4 border-2 border-red-500">
        <h2 class="text-2xl font-black text-red-600 uppercase">EVENT TIDAK DITEMUKAN</h2>
        <p class="font-bold text-sm text-ink">{{ error }}</p>
        <button @click="$router.push('/')" class="nb-btn nb-btn-primary px-4 py-2 text-xs uppercase">Kembali ke Beranda</button>
      </div>

      <!-- EVENT DETAIL CONTENT -->
      <div v-else-if="event" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- LEFT COLUMN: POSTER & DETAILS -->
        <div class="lg:col-span-8 space-y-6">
          <div class="relative nb-card bg-white p-2">
            <img :src="event.poster || defaultImage" :alt="event.nama_event" class="w-full h-80 sm:h-96 object-cover nb-border">
            <span class="absolute top-4 left-4 bg-accent text-ink font-black text-xs px-3 py-1.5 nb-border uppercase">
              {{ event.category?.nama_kategori || 'Event' }}
            </span>
          </div>

          <div>
            <h1 class="text-3xl sm:text-4xl font-black uppercase tracking-tight text-ink">{{ event.nama_event }}</h1>
            <p class="text-sm font-bold text-muted mt-1">Diselenggarakan oleh <span class="text-ink font-black">{{ event.organizer?.name || 'Info Musik BDG' }}</span></p>
          </div>

          <!-- TIME & LOCATION CARD -->
          <div class="nb-card bg-white p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1">
              <div class="text-xs font-bold uppercase text-muted">🗓️ Waktu & Tanggal</div>
              <div class="font-black text-base">{{ event.tanggal }} • {{ event.waktu }}</div>
            </div>
            <div class="space-y-1">
              <div class="text-xs font-bold uppercase text-muted">📍 Lokasi / Venue</div>
              <div class="font-black text-base">{{ event.lokasi }}</div>
              <div v-if="event.alamat" class="text-xs text-muted font-medium">{{ event.alamat }}</div>
            </div>
          </div>

          <!-- EVENT DESCRIPTION -->
          <div class="nb-card bg-white p-6 space-y-3">
            <h3 class="font-black text-xl uppercase border-b-2 border-ink pb-2">Deskripsi Event</h3>
            <p class="text-sm leading-relaxed font-medium text-ink/90 whitespace-pre-line">{{ event.deskripsi }}</p>
          </div>
        </div>

        <!-- RIGHT COLUMN: TICKET SELECTION & CHECKOUT -->
        <div class="lg:col-span-4 space-y-4">
          <div class="nb-card bg-white p-6 space-y-4 sticky top-24">
            <h3 class="font-black text-xl uppercase border-b-2 border-ink pb-2 flex items-center justify-between">
              <span>PILIH TIKET</span>
              <i class="fa-solid fa-ticket"></i>
            </h3>

            <div v-if="!event.tickets || event.tickets.length === 0" class="text-center py-4 text-xs font-bold text-muted uppercase">
              Belum ada tiket tersedia untuk event ini.
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="ticket in event.tickets"
                :key="ticket.id"
                class="p-4 border-2 border-ink space-y-3 transition-all"
                :class="{ 'bg-accent/20 border-accent': selectedTicket?.id === ticket.id, 'bg-white': selectedTicket?.id !== ticket.id }"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="font-black text-base uppercase">{{ ticket.nama_tiket }}</h4>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 border border-ink" :class="ticket.stok > 0 ? 'bg-green-300' : 'bg-red-300'">
                      Stok: {{ ticket.stok }}
                    </span>
                  </div>
                  <div class="font-black text-lg text-ink">
                    Rp {{ formatNumber(ticket.harga) }}
                  </div>
                </div>

                <button
                  @click="openCheckoutModal(ticket)"
                  :disabled="ticket.stok <= 0 || checkoutLoading"
                  class="nb-btn nb-btn-primary w-full py-2 text-xs font-black uppercase flex items-center justify-center gap-2"
                >
                  <i class="fa-solid fa-cart-shopping"></i>
                  <span>{{ ticket.stok > 0 ? 'Beli Tiket Ini' : 'Tiket Habis' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- MODAL FORM PEMESANAN TIKET -->
    <div v-if="showCheckoutModal && selectedTicketForCheckout" class="fixed inset-0 bg-ink/70 z-50 flex items-center justify-center p-4 overflow-y-auto">
      <div class="nb-card bg-paper w-full max-w-lg p-6 relative space-y-6 my-8">
        <button @click="showCheckoutModal = false" class="absolute top-4 right-4 font-black text-xl hover:bg-accent px-2 border border-ink">✕</button>

        <div class="border-b-2 border-ink pb-3">
          <span class="bg-accent px-2 py-0.5 text-xs font-black border border-ink uppercase">Form Pemesanan Tiket</span>
          <h2 class="text-2xl font-black uppercase mt-1">{{ event?.nama_event }}</h2>
          <p class="text-xs font-bold text-muted mt-0.5">Tier Tiket: <span class="text-ink font-black">{{ selectedTicketForCheckout.nama_tiket }}</span></p>
        </div>

        <form @submit.prevent="processPayment" class="space-y-4">
          <div>
            <label class="block text-xs font-black uppercase mb-1">Nama Pemesan</label>
            <input type="text" v-model="checkoutForm.name" required class="nb-input w-full" placeholder="Nama lengkap pemesan">
          </div>

          <div>
            <label class="block text-xs font-black uppercase mb-1">Email Pemesan</label>
            <input type="email" v-model="checkoutForm.email" required class="nb-input w-full" placeholder="email@domain.com">
          </div>

          <div>
            <label class="block text-xs font-black uppercase mb-1">Jumlah Tiket</label>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="checkoutForm.jumlah = Math.max(1, checkoutForm.jumlah - 1)"
                class="nb-btn nb-btn-secondary px-3 py-1 text-lg font-black"
              >-</button>
              <input
                type="number"
                v-model.number="checkoutForm.jumlah"
                min="1"
                :max="selectedTicketForCheckout.stok"
                required
                class="nb-input text-center font-black text-lg w-24"
              >
              <button
                type="button"
                @click="checkoutForm.jumlah = Math.min(selectedTicketForCheckout.stok, checkoutForm.jumlah + 1)"
                class="nb-btn nb-btn-secondary px-3 py-1 text-lg font-black"
              >+</button>
              <span class="text-xs font-bold text-muted ml-2">(Sisa Stok: {{ selectedTicketForCheckout.stok }})</span>
            </div>
          </div>

          <div class="nb-card bg-white p-4 space-y-2 border-2 border-ink">
            <div class="flex justify-between text-xs font-bold text-muted uppercase">
              <span>Harga Satuan</span>
              <span>Rp {{ formatNumber(selectedTicketForCheckout.harga) }}</span>
            </div>
            <div class="flex justify-between text-xs font-bold text-muted uppercase">
              <span>Jumlah Tiket</span>
              <span>{{ checkoutForm.jumlah }} Tiket</span>
            </div>
            <div class="flex justify-between items-center text-base font-black border-t-2 border-ink pt-2 text-ink">
              <span>SUB TOTAL</span>
              <span class="text-xl">Rp {{ formatNumber(checkoutSubtotal) }}</span>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="showCheckoutModal = false" class="nb-btn nb-btn-secondary px-4 py-2 text-xs uppercase">Batal</button>
            <button
              type="submit"
              :disabled="checkoutLoading"
              class="nb-btn nb-btn-primary px-6 py-2.5 text-xs font-black uppercase flex items-center gap-2"
            >
              <i class="fa-solid fa-credit-card"></i>
              <span>{{ checkoutLoading ? 'Memproses...' : 'Lanjut Bayar' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- FOOTER -->
    <Footer @navigate="(v) => $router.push('/')" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../utils/api'
import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'
import { showFlash } from '../utils/flash'

const route = useRoute()
const router = useRouter()

const event = ref(null)
const loading = ref(true)
const checkoutLoading = ref(false)
const error = ref('')
const selectedTicket = ref(null)
const isProfileMenuOpen = ref(false)

const showCheckoutModal = ref(false)
const selectedTicketForCheckout = ref(null)
const checkoutForm = ref({
  name: '',
  email: '',
  jumlah: 1
})

const isLoggedIn = ref(!!localStorage.getItem('token'))
const currentUser = ref(null)

const defaultImage = 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=800&q=80'

const loadUser = () => {
  const savedUser = localStorage.getItem('user')
  if (savedUser) {
    try {
      currentUser.value = JSON.parse(savedUser)
    } catch {
      currentUser.value = null
    }
  }
}

const handleDocumentClick = () => {
  isProfileMenuOpen.value = false
}

const fetchEventDetail = async () => {
  loading.value = true
  error.value = ''
  try {
    const eventId = route.params.id
    const response = await api.get(`/events/${eventId}`)
    event.value = response.data.data || response.data
  } catch (err) {
    console.error('Gagal mengambil detail event:', err)
    error.value = err.response?.data?.message || 'Gagal memuat data detail event.'
  } finally {
    loading.value = false
  }
}

const openCheckoutModal = (ticket) => {
  if (!isLoggedIn.value) {
    showFlash('Silakan login terlebih dahulu untuk membeli tiket.', 'warning', 'LOGIN DIPERLUKAN')
    router.push('/login')
    return
  }

  selectedTicketForCheckout.value = ticket
  checkoutForm.value = {
    name: currentUser.value?.name || '',
    email: currentUser.value?.email || '',
    jumlah: 1
  }
  showCheckoutModal.value = true
}

const checkoutSubtotal = computed(() => {
  if (!selectedTicketForCheckout.value) return 0
  return (Number(selectedTicketForCheckout.value.harga) || 0) * (Number(checkoutForm.value.jumlah) || 1)
})

const processPayment = async () => {
  if (!checkoutForm.value.name || !checkoutForm.value.email) {
    showFlash('Nama dan email pemesan wajib diisi.', 'warning', 'DATA BELUM LENGKAP')
    return
  }

  if (checkoutForm.value.jumlah > selectedTicketForCheckout.value.stok) {
    showFlash(`Stok tiket tidak mencukupi. Sisa stok: ${selectedTicketForCheckout.value.stok}`, 'warning', 'STOK TIDAK CUKUP')
    return
  }

  checkoutLoading.value = true
  try {
    const savedUser = localStorage.getItem('user')
    let userId = currentUser.value?.id
    if (!userId && savedUser) {
      try {
        userId = JSON.parse(savedUser).id
      } catch (e) {}
    }

    const response = await api.post('/orders', {
      user_id: userId,
      ticket_id: selectedTicketForCheckout.value.id,
      jumlah: checkoutForm.value.jumlah
    })

    const newOrder = response.data.data
    showCheckoutModal.value = false
    showFlash(`Berhasil memesan ${checkoutForm.value.jumlah} tiket ${selectedTicketForCheckout.value.nama_tiket}!`, 'success', 'PEMESANAN BERHASIL')

    if (newOrder && window.snap) {
      try {
        const snapRes = await api.post('/payments/snap-token', { order_id: newOrder.id })
        const snapToken = snapRes.data.snap_token
        if (snapToken) {
          window.snap.pay(snapToken, {
            onSuccess: async (result) => {
              await api.post('/payments/finish', {
                order_id: newOrder.id,
                status: 'berhasil',
                metode_pembayaran: result.payment_type || 'Midtrans'
              })
              showFlash('Pembayaran Anda telah berhasil diproses!', 'success', 'PEMBAYARAN BERHASIL')
              router.push('/')
            },
            onPending: async () => { router.push('/') },
            onError: async () => { router.push('/') },
            onClose: async () => { router.push('/') }
          })
        } else {
          router.push('/')
        }
      } catch (snapErr) {
        console.error('Snap error:', snapErr)
        router.push('/')
      }
    } else {
      router.push('/')
    }
  } catch (err) {
    console.error('Gagal memesan tiket:', err)
    showFlash(err.response?.data?.message || 'Gagal melakukan pemesanan tiket.', 'error', 'PEMESANAN GAGAL')
  } finally {
    checkoutLoading.value = false
  }
}

const logout = async () => {
  try {
    await api.post('/logout')
  } catch (err) {
    console.error('Logout error:', err)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    isLoggedIn.value = false
    currentUser.value = null
    isProfileMenuOpen.value = false
    router.push('/')
  }
}

const formatNumber = (val) => {
  return new Intl.NumberFormat('id-ID').format(Number(val) || 0)
}

onMounted(() => {
  loadUser()
  fetchEventDetail()
  document.addEventListener('click', handleDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>
