<template>
  <div class="min-h-screen bg-[#f5f0e8] flex flex-col">

    <!-- NAVBAR -->
    <Navbar
      :currentView="currentView"
      :favoritesCount="wishlist.length"
      @navigate="navigateTo"
      @open-search="$router.push('/')"
      @filter-category="filterCategoryQuick"
    />

    <!-- CONTENT -->
    <main class="flex-1 px-4 py-8 md:px-8 lg:px-12">

      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div
          class="flex flex-col md:flex-row md:items-end md:justify-between gap-5 mb-8"
        >

          <div>

            <h1
              class="text-4xl md:text-5xl font-black uppercase leading-none"
            >
              My Wishlist
            </h1>
          </div>

          <!-- BACK -->
          <button
            @click="$router.push('/')"
            class="border-4 border-black bg-white px-5 py-3 font-black uppercase text-sm shadow-[5px_5px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
          >
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Back Home
          </button>

        </div>


        <!-- ====================================== -->
        <!-- EMPTY WISHLIST -->
        <!-- ====================================== -->

        <div
          v-if="wishlist.length === 0"
          class="border-4 border-black bg-white shadow-[7px_7px_0_#000] p-8 md:p-12 text-center"
        >

          <div
            class="mx-auto w-20 h-20 md:w-24 md:h-24 border-4 border-black bg-[#FFD84D] flex items-center justify-center text-4xl shadow-[4px_4px_0_#000] mb-6"
          >
            <i class="fa-regular fa-heart"></i>
          </div>

          <h2
            class="text-2xl md:text-3xl font-black uppercase"
          >
            Wishlist Kamu Masih Kosong
          </h2>

          <p
            class="mt-3 text-sm md:text-base font-bold max-w-md mx-auto"
          >
            Simpan event yang ingin kamu datangi dengan menekan ikon hati.
          </p>

          <button
            @click="$router.push('/')"
            class="mt-6 border-4 border-black bg-[#FFD84D] px-6 py-3 font-black uppercase text-sm shadow-[5px_5px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
          >
            <i class="fa-solid fa-magnifying-glass mr-2"></i>
            Cari Event
          </button>

        </div>


        <!-- ====================================== -->
        <!-- WISHLIST -->
        <!-- ====================================== -->

        <div v-else>

          <!-- TOP INFO -->
          <div
            class="flex items-center justify-between mb-5"
          >

            <p class="font-black uppercase text-sm">
              {{ wishlist.length }} Event Disimpan
            </p>

            <button
              @click="clearWishlist"
              class="text-xs font-black uppercase underline hover:no-underline"
            >
              Hapus Semua
            </button>

          </div>


          <!-- EVENT GRID -->
          <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >

            <article
              v-for="event in wishlist"
              :key="event.id"
              class="border-4 border-black shadow-[6px_6px_0_#000] overflow-hidden transition-all"
              :class="isPastEvent(event) ? 'bg-gray-200 opacity-75' : 'bg-white'"
            >

              <!-- IMAGE -->
              <div
                class="relative h-52 overflow-hidden"
                :class="isPastEvent(event) ? 'bg-gray-400' : 'bg-gray-200'"
              >

                <img
                  v-if="event.image || event.image_url || event.poster"
                  :src="event.image || event.image_url || event.poster"
                  :alt="event.nama_event || event.name"
                  class="w-full h-full object-cover transition-all"
                  :class="isPastEvent(event) ? 'grayscale' : ''"
                />

                <!-- FALLBACK -->
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center text-white"
                  :class="isPastEvent(event) ? 'bg-gray-500' : 'bg-[#7E22CE]'"
                >
                  <i class="fa-solid fa-music text-5xl"></i>
                </div>

                <!-- Badge Event Berakhir -->
                <div
                  v-if="isPastEvent(event)"
                  class="absolute inset-0 flex items-center justify-center pointer-events-none"
                >
                  <span class="bg-gray-800 text-white text-xs font-black uppercase px-3 py-1.5 border-2 border-white shadow-lg tracking-widest">
                    EVENT BERAKHIR
                  </span>
                </div>

                <!-- REMOVE BUTTON -->
                <button
                  @click="removeFromWishlist(event.id)"
                  class="absolute top-3 right-3 w-11 h-11 border-4 border-black bg-white flex items-center justify-center shadow-[3px_3px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
                  title="Hapus dari wishlist"
                >
                  <i class="fa-solid fa-heart text-[#ff5757]"></i>
                </button>

              </div>


              <!-- CONTENT -->
              <div class="p-5">

                <!-- DATE -->
                <p
                  v-if="event.tanggal || event.date"
                  class="text-xs font-black uppercase mb-2"
                  :class="isPastEvent(event) ? 'text-gray-500' : 'text-[#7E22CE]'"
                >
                  <i class="fa-regular fa-calendar mr-1"></i>
                  {{ formatDate(event.tanggal || event.date) }}
                </p>

                <!-- TITLE -->
                <h2
                  class="text-xl font-black uppercase leading-tight"
                  :class="isPastEvent(event) ? 'text-gray-500' : ''"
                >
                  {{ event.nama_event || event.name || 'Event' }}
                </h2>

                <!-- Keterangan event berakhir -->
                <p v-if="isPastEvent(event)" class="mt-1 text-xs font-black text-gray-500 uppercase flex items-center gap-1">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                  Event sudah berakhir
                </p>

                <!-- LOCATION -->
                <p
                  v-if="event.lokasi || event.location || event.venue"
                  class="mt-3 text-sm font-bold"
                  :class="isPastEvent(event) ? 'text-gray-400' : ''"
                >
                  <i class="fa-solid fa-location-dot mr-2"></i>
                  {{ event.lokasi || event.location || event.venue }}
                </p>


                <!-- PRICE + DETAIL -->
                <div
                  class="flex items-center justify-between gap-3 mt-5 pt-4 border-t-2"
                  :class="isPastEvent(event) ? 'border-gray-300' : 'border-black'"
                >

                  <div>
                    <p class="text-[10px] font-black uppercase" :class="isPastEvent(event) ? 'text-gray-400' : ''">
                      Mulai dari
                    </p>
                    <p class="font-black" :class="isPastEvent(event) ? 'text-gray-500' : ''">
                      {{ formatPrice(getMinPrice(event)) }}
                    </p>
                  </div>

                  <!-- DETAIL -->
                  <button
                    @click="openEvent(event.id)"
                    class="border-4 border-black px-4 py-2 font-black uppercase text-xs shadow-[3px_3px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
                    :class="isPastEvent(event) ? 'bg-gray-300 text-gray-600 shadow-[3px_3px_0_#777]' : 'bg-[#FFD84D]'"
                  >
                    Detail
                    <i class="fa-solid fa-arrow-right ml-1"></i>
                  </button>

                </div>

              </div>

            </article>

          </div>

        </div>

      </div>

    </main>


    <!-- FOOTER -->
    <Footer @navigate="navigateTo" />

  </div>
</template>


<script>
import { useRouter } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'
import { showHomeFlash } from '../utils/flash'

export default {

  name: 'WishlistView',

  components: { Navbar, Footer },

  data() {
    return {
      currentView: 'view-wishlist',
      wishlist   : [],
    }
  },

  mounted() {
    this.loadWishlist()
    window.addEventListener('wishlist-updated', this.loadWishlist)
  },

  beforeUnmount() {
    window.removeEventListener('wishlist-updated', this.loadWishlist)
  },

  methods: {

    // ──────────────────────────────────────────────────
    // NAVIGATION — pakai $router langsung, tidak emit
    // ──────────────────────────────────────────────────
    navigateTo(view) {
      const map = {
        'view-home'      : '/',
        'view-events'    : '/',
        'view-favorites' : '/wishlist',
        'view-my-tickets': '/my-tickets',
        'view-profile'   : '/profile',
        'view-pesanan'   : '/pesanan',
      }
      if (map[view]) this.$router.push(map[view])
    },

    filterCategoryQuick(category) {
      this.$router.push({ path: '/', query: { category } })
    },

    isPastEvent(event) {
      const dateStr = event.tanggal || event.date
      if (!dateStr) return false
      const today = new Date(); today.setHours(0,0,0,0)
      const eventDate = new Date(dateStr); eventDate.setHours(0,0,0,0)
      return eventDate < today
    },

    getMinPrice(event) {
      if (event.minPrice !== undefined && event.minPrice !== null) return event.minPrice
      if (Array.isArray(event.tickets) && event.tickets.length > 0) {
        return Math.min(...event.tickets.map(t => Number(t.harga || t.price || 0)))
      }
      return event.harga || event.price || null
    },

    loadWishlist() {
      try {
        const saved = localStorage.getItem('wishlist')
        this.wishlist = saved ? JSON.parse(saved) : []
      } catch { this.wishlist = [] }
    },

    removeFromWishlist(id) {
      const event = this.wishlist.find(e => e.id === id)
      const eventName = event?.nama_event || event?.name || 'Event'
      this.wishlist = this.wishlist.filter(e => e.id !== id)
      this.saveWishlist()
      showHomeFlash(`${eventName} dihapus dari wishlist.`, 'info', 'WISHLIST DIPERBARUI')
    },

    clearWishlist() {
      if (!this.wishlist.length) return
      if (!confirm('Hapus semua event dari wishlist?')) return
      const jumlah = this.wishlist.length
      this.wishlist = []
      this.saveWishlist()
      showHomeFlash(`${jumlah} event berhasil dihapus dari wishlist.`, 'info', 'WISHLIST DIKOSONGKAN')
    },

    saveWishlist() {
      localStorage.setItem('wishlist', JSON.stringify(this.wishlist))
      window.dispatchEvent(new Event('wishlist-updated'))
    },

    openEvent(id) { this.$router.push(`/event/${id}`) },

    formatPrice(price) {
      if (price === null || price === undefined || price === '') return 'Gratis'
      const number = Number(price)
      if (Number.isNaN(number)) return price
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number)
    },

    formatDate(date) {
      if (!date) return '-'
      const parsed = new Date(date)
      if (Number.isNaN(parsed.getTime())) return date
      return parsed.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    }

  }

}
</script>


<style scoped>

button {
  cursor: pointer;
}

button:disabled {
  cursor: not-allowed;
}


/* ==========================================
   MOBILE
   ========================================== */

@media (max-width: 640px) {

  main {
    padding-top: 24px;
    padding-bottom: 32px;
  }

}

</style>