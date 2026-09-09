<template>
  <div class="min-h-screen bg-[#f5f0e8] flex flex-col">

    <!-- NAVBAR -->
    <Navbar
      :currentView="currentView"
      :favoritesCount="wishlist.length"
      @navigate="navigateTo"
      @open-search="$emit('open-search')"
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
            <p class="text-xs font-black uppercase tracking-[3px] mb-2">
              Saved Events
            </p>

            <h1
              class="text-4xl md:text-5xl font-black uppercase leading-none"
            >
              My Wishlist
            </h1>

            <p class="mt-3 text-sm md:text-base font-bold">
              Event yang kamu simpan untuk nanti.
            </p>
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
              class="bg-white border-4 border-black shadow-[6px_6px_0_#000] overflow-hidden"
            >

              <!-- IMAGE -->
              <div
                class="relative h-52 bg-gray-200 overflow-hidden"
              >

                <img
                  v-if="event.image || event.image_url || event.poster"
                  :src="
                    event.image ||
                    event.image_url ||
                    event.poster
                  "
                  :alt="event.nama_event || event.name"
                  class="w-full h-full object-cover"
                />

                <!-- FALLBACK -->
                <div
                  v-else
                  class="w-full h-full bg-[#7E22CE] flex items-center justify-center text-white"
                >
                  <i class="fa-solid fa-music text-5xl"></i>
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
                  class="text-xs font-black uppercase text-[#7E22CE] mb-2"
                >
                  <i class="fa-regular fa-calendar mr-1"></i>

                  {{ formatDate(event.tanggal || event.date) }}
                </p>


                <!-- TITLE -->
                <h2
                  class="text-xl font-black uppercase leading-tight"
                >
                  {{ event.nama_event || event.name || 'Event' }}
                </h2>


                <!-- LOCATION -->
                <p
                  v-if="event.lokasi || event.location || event.venue"
                  class="mt-3 text-sm font-bold"
                >
                  <i class="fa-solid fa-location-dot mr-2"></i>

                  {{
                    event.lokasi ||
                    event.location ||
                    event.venue
                  }}
                </p>


                <!-- PRICE -->
                <div
                  class="flex items-center justify-between gap-3 mt-5 pt-4 border-t-2 border-black"
                >

                  <div>
                    <p class="text-[10px] font-black uppercase">
                      Mulai dari
                    </p>

                    <p class="font-black">
                      {{ formatPrice(event.harga || event.price) }}
                    </p>
                  </div>


                  <!-- DETAIL -->
                  <button
                    @click="openEvent(event.id)"
                    class="border-3 border-black bg-[#FFD84D] px-4 py-2 font-black uppercase text-xs shadow-[3px_3px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
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
import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'

export default {

  name: 'WishlistView',

  components: {
    Navbar,
    Footer
  },

  props: {

    currentView: {
      type: String,
      default: 'view-wishlist'
    },

    favorites: {
      type: Array,
      default: () => []
    }

  },

  data() {

    return {

      wishlist: []

    }

  },


  mounted() {

    this.loadWishlist()

    window.addEventListener(
      'wishlist-updated',
      this.loadWishlist
    )

  },


  beforeUnmount() {

    window.removeEventListener(
      'wishlist-updated',
      this.loadWishlist
    )

  },


  methods: {

    // ==========================================
    // LOAD WISHLIST
    // ==========================================

    loadWishlist() {

      try {

        const saved =
          localStorage.getItem('wishlist')

        this.wishlist =
          saved
            ? JSON.parse(saved)
            : []

      } catch (error) {

        console.error(
          'Gagal membaca wishlist:',
          error
        )

        this.wishlist = []

      }

    },


    // ==========================================
    // REMOVE
    // ==========================================

    removeFromWishlist(id) {

      this.wishlist =
        this.wishlist.filter(
          event => event.id !== id
        )

      this.saveWishlist()

    },


    // ==========================================
    // CLEAR
    // ==========================================

    clearWishlist() {

      if (this.wishlist.length === 0) {
        return
      }

      const confirmed =
        window.confirm(
          'Hapus semua event dari wishlist?'
        )

      if (!confirmed) {
        return
      }

      this.wishlist = []

      this.saveWishlist()

    },


    // ==========================================
    // SAVE
    // ==========================================

    saveWishlist() {

      localStorage.setItem(
        'wishlist',
        JSON.stringify(this.wishlist)
      )

      window.dispatchEvent(
        new Event('wishlist-updated')
      )

    },


    // ==========================================
    // EVENT DETAIL
    // ==========================================

    openEvent(id) {

      this.$router.push(
        `/event/${id}`
      )

    },


    // ==========================================
    // NAVIGATION
    // ==========================================

    navigateTo(view) {

      this.$emit(
        'navigate',
        view
      )

    },


    filterCategoryQuick(category) {

      this.$emit(
        'filter-category',
        category
      )

    },


    // ==========================================
    // FORMAT PRICE
    // ==========================================

    formatPrice(price) {

      if (
        price === null ||
        price === undefined ||
        price === ''
      ) {
        return 'Gratis'
      }

      const number =
        Number(price)

      if (Number.isNaN(number)) {
        return price
      }

      return new Intl.NumberFormat(
        'id-ID',
        {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0
        }
      ).format(number)

    },


    // ==========================================
    // FORMAT DATE
    // ==========================================

    formatDate(date) {

      if (!date) {
        return '-'
      }

      const parsed =
        new Date(date)

      if (
        Number.isNaN(
          parsed.getTime()
        )
      ) {
        return date
      }

      return parsed.toLocaleDateString(
        'id-ID',
        {
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        }
      )

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