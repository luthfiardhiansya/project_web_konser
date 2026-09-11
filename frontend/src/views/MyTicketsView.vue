<template>
  <div class="min-h-screen bg-[#f5f5f0] flex flex-col">

    <!-- =========================
         NAVBAR
    ========================== -->
    <Navbar
      :currentView="currentView"
      :favoritesCount="wishlist.length"
      @navigate="navigateTo"
      @open-search="openSearch"
      @filter-category="filterCategoryQuick"
    />

    <!-- =========================
         MAIN CONTENT
    ========================== -->
    <main class="flex-1 px-4 py-6 md:px-8">

      <div class="max-w-6xl mx-auto">

        <!-- HEADER -->
        <div class="mb-6">

          <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">

            <div>
              <p class="text-xs md:text-sm font-black uppercase tracking-wide">
                InfoMusikBDG
              </p>

              <h1 class="text-3xl md:text-5xl font-black uppercase mt-1">
                E-TICKET SAYA
              </h1>

              <p class="mt-2 text-sm md:text-base font-bold text-gray-600">
                Tiket event yang sudah kamu beli.
              </p>
            </div>

            <button
              @click="navigateTo('view-home')"
              class="border-4 border-black bg-white px-4 py-3 font-black shadow-[4px_4px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] transition-all"
            >
              ← KEMBALI
            </button>

          </div>

        </div>


        <!-- =========================
             FILTER
        ========================== -->
        <div
          class="mb-6 border-4 border-black bg-white p-4 md:p-5 shadow-[5px_5px_0_#000]"
        >

          <div class="flex items-center justify-between mb-4">
            <h2 class="font-black uppercase text-lg">
              Filter Tiket
            </h2>

            <button
              v-if="hasActiveFilter"
              @click="resetFilters"
              class="text-xs md:text-sm font-black uppercase underline"
            >
              Reset
            </button>
          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- SEARCH -->
            <div class="lg:col-span-1">

              <label class="block text-xs font-black uppercase mb-2">
                Cari Tiket
              </label>

              <input
                v-model="search"
                type="text"
                placeholder="Event / kode pesanan..."
                class="w-full border-4 border-black bg-white px-3 py-3 text-sm font-bold outline-none focus:bg-yellow-100"
              />

            </div>


            <!-- STATUS -->
            <div>

              <label class="block text-xs font-black uppercase mb-2">
                Status
              </label>

              <select
                v-model="statusFilter"
                class="w-full border-4 border-black bg-white px-3 py-3 text-sm font-bold outline-none"
              >
                <option value="all">
                  Semua Status
                </option>

                <option value="valid">
                  Belum Digunakan
                </option>

                <option value="used">
                  Sudah Digunakan
                </option>
              </select>

            </div>


            <!-- DARI TANGGAL -->
            <div>

              <label class="block text-xs font-black uppercase mb-2">
                Dari Tanggal Pesan
              </label>

              <input
                v-model="dateFrom"
                type="date"
                class="w-full border-4 border-black bg-white px-3 py-3 text-sm font-bold outline-none"
              />

            </div>


            <!-- SAMPAI TANGGAL -->
            <div>

              <label class="block text-xs font-black uppercase mb-2">
                Sampai Tanggal Pesan
              </label>

              <input
                v-model="dateTo"
                type="date"
                class="w-full border-4 border-black bg-white px-3 py-3 text-sm font-bold outline-none"
              />

            </div>

          </div>


          <!-- INFO FILTER -->
          <div
            class="mt-4 border-t-2 border-black pt-3 text-xs md:text-sm font-bold"
          >
            Menampilkan
            <span class="font-black">
              {{ filteredTickets.length }}
            </span>
            tiket
          </div>

        </div>


        <!-- =========================
             LOADING
        ========================== -->
        <div
          v-if="loading"
          class="border-4 border-black bg-white p-8 text-center shadow-[6px_6px_0_#000]"
        >
          <p class="font-black text-xl uppercase">
            MEMUAT E-TICKET...
          </p>
        </div>


        <!-- =========================
             ERROR
        ========================== -->
        <div
          v-else-if="error"
          class="border-4 border-black bg-white p-8 shadow-[6px_6px_0_#000]"
        >

          <p class="font-black text-xl mb-3 uppercase">
            GAGAL MEMUAT TIKET
          </p>

          <p class="text-gray-600 mb-5 font-bold">
            {{ error }}
          </p>

          <button
            @click="getTickets"
            class="border-4 border-black bg-black text-white px-5 py-3 font-black uppercase"
          >
            COBA LAGI
          </button>

        </div>


        <!-- =========================
             EMPTY
        ========================== -->
        <div
          v-else-if="filteredTickets.length === 0"
          class="border-4 border-black bg-white p-10 text-center shadow-[6px_6px_0_#000]"
        >

          <div class="text-5xl mb-4">
            🎟️
          </div>

          <h2 class="text-2xl font-black uppercase mb-2">
            TIKET TIDAK DITEMUKAN
          </h2>

          <p class="text-gray-600 font-bold mb-6">
            Tidak ada tiket yang sesuai dengan filter.
          </p>

          <button
            @click="resetFilters"
            class="border-4 border-black bg-yellow-300 px-6 py-3 font-black shadow-[4px_4px_0_#000]"
          >
            RESET FILTER
          </button>

        </div>


        <!-- =========================
             TICKET LIST
        ========================== -->
        <div
          v-else
          class="grid grid-cols-1 lg:grid-cols-2 gap-6"
        >

          <div
            v-for="(ticket, index) in paginatedTickets"
            :key="ticket.id"
            class="border-4 border-black shadow-[6px_6px_0_#000] transition-all"
            :class="
              ticket.status === 'used'
                ? 'bg-gray-300'
                : 'bg-white'
            "
          >

            <!-- =========================
                 TICKET HEADER
            ========================== -->
            <div class="border-b-4 border-black p-4 md:p-5">

              <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">

                  <p class="text-[10px] md:text-xs font-black uppercase">
                    E-TICKET #{{ ticketNumber(index) }}
                  </p>

                  <h2
                    class="text-xl md:text-2xl font-black uppercase mt-2 break-words"
                  >
                    {{ ticket.ticket?.event?.nama_event || 'Nama Event' }}
                  </h2>

                </div>


                <!-- STATUS -->
                <span
                  class="shrink-0 border-2 border-black px-2 md:px-3 py-1 text-[10px] md:text-xs font-black uppercase"
                  :class="
                    ticket.status === 'used'
                      ? 'bg-gray-500 text-white'
                      : 'bg-yellow-300 text-black'
                  "
                >
                  {{
                    ticket.status === 'used'
                      ? 'Sudah Digunakan'
                      : 'Belum Digunakan'
                  }}
                </span>

              </div>

            </div>


            <!-- =========================
                 TICKET BODY
            ========================== -->
            <div class="p-4 md:p-5">

              <!-- INFO -->
              <div class="grid grid-cols-2 gap-4 mb-5">

                <!-- JENIS -->
                <div>

                  <p class="text-[10px] md:text-xs font-black text-gray-500 uppercase">
                    Jenis Tiket
                  </p>

                  <p class="font-black mt-1">
                    {{ ticket.ticket?.nama_tiket || '-' }}
                  </p>

                </div>


                <!-- KODE -->
                <div>

                  <p class="text-[10px] md:text-xs font-black text-gray-500 uppercase">
                    Kode Pesanan
                  </p>

                  <p class="font-black mt-1 break-all">
                    {{ ticket.order?.kode_pesanan || '-' }}
                  </p>

                </div>


                <!-- TANGGAL PESAN -->
                <div>

                  <p class="text-[10px] md:text-xs font-black text-gray-500 uppercase">
                    Tanggal Pesan
                  </p>

                  <p class="font-black mt-1">
                    {{ formatOrderDate(ticket) }}
                  </p>

                </div>


                <!-- WAKTU EVENT -->
                <div>

                  <p class="text-[10px] md:text-xs font-black text-gray-500 uppercase">
                    Waktu Event
                  </p>

                  <p class="font-black mt-1">
                    {{ ticket.ticket?.event?.waktu || '-' }}
                  </p>

                </div>


                <!-- LOKASI -->
                <div class="col-span-2">

                  <p class="text-[10px] md:text-xs font-black text-gray-500 uppercase">
                    Lokasi Event
                  </p>

                  <p class="font-black mt-1">
                    {{ ticket.ticket?.event?.lokasi || '-' }}
                  </p>

                </div>

              </div>


              <!-- =========================
                   QR CODE
              ========================== -->
              <div
                class="border-4 border-black p-4 text-center"
                :class="
                  ticket.status === 'used'
                    ? 'bg-gray-200'
                    : 'bg-white'
                "
              >

                <p class="font-black text-sm uppercase mb-3">
                  {{
                    ticket.status === 'used'
                      ? 'TIKET SUDAH DIGUNAKAN'
                      : 'SCAN QR CODE SAAT MASUK'
                  }}
                </p>


                <div
                  :id="'qr-' + ticket.id"
                  class="flex justify-center"
                  :class="
                    ticket.status === 'used'
                      ? 'opacity-40 grayscale'
                      : ''
                  "
                >
                </div>


                <p
                  class="text-[9px] md:text-xs text-gray-500 mt-3 break-all font-bold"
                >
                  {{ ticket.qr_token }}
                </p>

              </div>


              <!-- USED INFO -->
              <div
                v-if="ticket.status === 'used'"
                class="mt-4 border-2 border-black bg-gray-400 p-3"
              >

                <p class="text-xs font-black uppercase">
                  ✓ Tiket telah digunakan
                </p>

                <p
                  v-if="ticket.scanned_at"
                  class="text-xs font-bold mt-1"
                >
                  Dipindai:
                  {{ formatDateTime(ticket.scanned_at) }}
                </p>

              </div>

            </div>

          </div>

        </div>


        <!-- =========================
             PAGINATION
        ========================== -->
        <div
          v-if="totalPages > 1"
          class="mt-8 flex flex-wrap items-center justify-center gap-2"
        >

          <!-- PREVIOUS -->
          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="border-4 border-black px-4 py-2 font-black bg-white disabled:opacity-40 disabled:cursor-not-allowed hover:bg-yellow-300"
          >
            ←
          </button>


          <!-- PAGE NUMBERS -->
          <button
            v-for="page in totalPages"
            :key="page"
            @click="changePage(page)"
            class="border-4 border-black px-4 py-2 font-black min-w-[44px]"
            :class="
              currentPage === page
                ? 'bg-black text-white'
                : 'bg-white hover:bg-yellow-300'
            "
          >
            {{ page }}
          </button>


          <!-- NEXT -->
          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="border-4 border-black px-4 py-2 font-black bg-white disabled:opacity-40 disabled:cursor-not-allowed hover:bg-yellow-300"
          >
            →
          </button>

        </div>


        <!-- PAGE INFO -->
        <p
          v-if="filteredTickets.length > 0"
          class="text-center text-xs font-black uppercase mt-4 mb-6"
        >
          Halaman {{ currentPage }} dari {{ totalPages }}
        </p>

      </div>

    </main>


    <!-- =========================
         FOOTER
    ========================== -->
    <Footer @navigate="navigateTo" />

  </div>
</template>


<script setup>

import {
  ref,
  computed,
  nextTick,
  onMounted,
  watch
} from 'vue'

import { useRouter } from 'vue-router'

import QRCode from 'qrcode'

import api from '../utils/api'

import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'


/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/

const router = useRouter()


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const tickets = ref([])

const loading = ref(true)

const error = ref('')

const search = ref('')

const statusFilter = ref('all')

const dateFrom = ref('')

const dateTo = ref('')

const currentPage = ref(1)

const perPage = 4


/*
|--------------------------------------------------------------------------
| NAVBAR STATE
|--------------------------------------------------------------------------
*/

const currentView = ref('view-my-tickets')

const wishlist = ref([])


/*
|--------------------------------------------------------------------------
| NAVIGATION
|--------------------------------------------------------------------------
*/

const navigateTo = (viewName) => {

  switch (viewName) {

    case 'view-home':
      router.push('/')
      break

    case 'view-events':
      router.push('/events')
      break

    case 'view-my-tickets':
      router.push('/my-tickets')
      break

    case 'view-profile':
      router.push('/profile')
      break

    case 'view-pesanan':
      router.push('/pesanan')
      break

    case 'view-community':
      router.push('/community')
      break

    case 'view-organizer-reg':
      router.push('/organizer')
      break

    case 'view-favorites':
      router.push('/wishlist')
      break

    default:
      router.push('/')
      break
  }

}


/*
|--------------------------------------------------------------------------
| SEARCH NAVBAR
|--------------------------------------------------------------------------
*/

const openSearch = () => {

  window.dispatchEvent(
    new CustomEvent('open-global-search')
  )

}


/*
|--------------------------------------------------------------------------
| FILTER CATEGORY
|--------------------------------------------------------------------------
*/

const filterCategoryQuick = (category) => {

  router.push({
    path: '/',
    query: {
      category
    }
  })

}


/*
|--------------------------------------------------------------------------
| GET TICKETS
|--------------------------------------------------------------------------
*/

const getTickets = async () => {

  loading.value = true

  error.value = ''

  try {

    const response = await api.get('/my-tickets')

    tickets.value = response.data.data || []

    currentPage.value = 1

    await nextTick()

    await renderQRCodes()

  } catch (err) {

    console.error(err)

    if (err.response?.status === 401) {

      error.value =
        'Sesi login kamu sudah habis. Silakan login kembali.'

    } else {

      error.value =
        err.response?.data?.message ||
        'Terjadi kesalahan saat mengambil E-Ticket.'

    }

  } finally {

    loading.value = false

  }

}


/*
|--------------------------------------------------------------------------
| RENDER QR
|--------------------------------------------------------------------------
*/

const renderQRCodes = async () => {

  await nextTick()

  for (const ticket of paginatedTickets.value) {

    const element =
      document.getElementById(`qr-${ticket.id}`)

    if (!element || !ticket.qr_token) {
      continue
    }

    element.innerHTML = ''

    const canvas =
      document.createElement('canvas')

    try {

      await QRCode.toCanvas(
        canvas,
        ticket.qr_token,
        {
          width: 200,
          margin: 2
        }
      )

      element.appendChild(canvas)

    } catch (error) {

      console.error(
        'QR gagal dibuat:',
        error
      )

    }

  }

}


/*
|--------------------------------------------------------------------------
| ORDER DATE
|--------------------------------------------------------------------------
|
| Prioritas:
| order.created_at
| lalu issued ticket created_at
|
*/

const getOrderDateValue = (ticket) => {

  return (
    ticket.order?.created_at ||
    ticket.created_at ||
    null
  )

}


/*
|--------------------------------------------------------------------------
| FORMAT ORDER DATE
|--------------------------------------------------------------------------
*/

const formatOrderDate = (ticket) => {

  const date =
    getOrderDateValue(ticket)

  if (!date) {
    return '-'
  }

  return new Date(date).toLocaleDateString(
    'id-ID',
    {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }
  )

}


/*
|--------------------------------------------------------------------------
| FORMAT DATE TIME
|--------------------------------------------------------------------------
*/

const formatDateTime = (date) => {

  if (!date) {
    return '-'
  }

  return new Date(date).toLocaleString(
    'id-ID',
    {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }
  )

}


/*
|--------------------------------------------------------------------------
| FILTERED TICKETS
|--------------------------------------------------------------------------
*/

const filteredTickets = computed(() => {

  let result = [...tickets.value]


  /*
  | SEARCH
  */

  const keyword =
    search.value
      .trim()
      .toLowerCase()

  if (keyword) {

    result = result.filter(ticket => {

      const eventName =
        ticket.ticket?.event?.nama_event
          ?.toLowerCase() || ''

      const ticketName =
        ticket.ticket?.nama_tiket
          ?.toLowerCase() || ''

      const orderCode =
        ticket.order?.kode_pesanan
          ?.toLowerCase() || ''

      const qrToken =
        ticket.qr_token
          ?.toLowerCase() || ''

      return (
        eventName.includes(keyword) ||
        ticketName.includes(keyword) ||
        orderCode.includes(keyword) ||
        qrToken.includes(keyword)
      )

    })

  }


  /*
  | STATUS
  */

  if (statusFilter.value !== 'all') {

    result = result.filter(ticket => {

      return ticket.status === statusFilter.value

    })

  }


  /*
  | DATE FROM
  */

  if (dateFrom.value) {

    const from =
      new Date(
        `${dateFrom.value}T00:00:00`
      )

    result = result.filter(ticket => {

      const ticketDate =
        getOrderDateValue(ticket)

      if (!ticketDate) {
        return false
      }

      return new Date(ticketDate) >= from

    })

  }


  /*
  | DATE TO
  */

  if (dateTo.value) {

    const to =
      new Date(
        `${dateTo.value}T23:59:59`
      )

    result = result.filter(ticket => {

      const ticketDate =
        getOrderDateValue(ticket)

      if (!ticketDate) {
        return false
      }

      return new Date(ticketDate) <= to

    })

  }


  return result

})


/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

const totalPages = computed(() => {

  return Math.max(
    1,
    Math.ceil(
      filteredTickets.value.length /
      perPage
    )
  )

})


const paginatedTickets = computed(() => {

  const start =
    (currentPage.value - 1) *
    perPage

  const end =
    start + perPage

  return filteredTickets.value.slice(
    start,
    end
  )

})


/*
|--------------------------------------------------------------------------
| TICKET NUMBER
|--------------------------------------------------------------------------
*/

const ticketNumber = (index) => {

  return (
    (currentPage.value - 1) *
    perPage +
    index +
    1
  )

}


/*
|--------------------------------------------------------------------------
| CHANGE PAGE
|--------------------------------------------------------------------------
*/

const changePage = (page) => {

  if (page < 1) {
    return
  }

  if (page > totalPages.value) {
    return
  }

  currentPage.value = page

  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })

}


/*
|--------------------------------------------------------------------------
| ACTIVE FILTER
|--------------------------------------------------------------------------
*/

const hasActiveFilter = computed(() => {

  return (
    search.value !== '' ||
    statusFilter.value !== 'all' ||
    dateFrom.value !== '' ||
    dateTo.value !== ''
  )

})


/*
|--------------------------------------------------------------------------
| RESET FILTER
|--------------------------------------------------------------------------
*/

const resetFilters = () => {

  search.value = ''

  statusFilter.value = 'all'

  dateFrom.value = ''

  dateTo.value = ''

  currentPage.value = 1

}


/*
|--------------------------------------------------------------------------
| WATCH FILTER
|--------------------------------------------------------------------------
*/

watch(
  [
    search,
    statusFilter,
    dateFrom,
    dateTo
  ],
  async () => {

    currentPage.value = 1

    await nextTick()

    await renderQRCodes()

  }
)


/*
|--------------------------------------------------------------------------
| WATCH PAGE
|--------------------------------------------------------------------------
*/

watch(
  currentPage,
  async () => {

    await nextTick()

    await renderQRCodes()

  }
)


/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(() => {

  getTickets()

})

</script>