<template>
  <div class="min-h-screen bg-[#f5f5f5] p-4 md:p-8">

    <!-- HEADER -->
    <div class="max-w-6xl mx-auto mb-8">
      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-sm font-bold uppercase tracking-wide">
            InfoMusikBDG
          </p>

          <h1 class="text-3xl md:text-5xl font-black mt-2">
            E-TICKET SAYA
          </h1>

          <p class="mt-2 text-gray-600">
            Tiket event yang sudah kamu beli.
          </p>
        </div>

        <button
          @click="$router.push('/')"
          class="border-4 border-black bg-white px-4 py-3 font-black shadow-[4px_4px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000]"
        >
          KEMBALI
        </button>
      </div>
    </div>


    <!-- LOADING -->
    <div
      v-if="loading"
      class="max-w-6xl mx-auto border-4 border-black bg-white p-8 text-center shadow-[6px_6px_0_#000]"
    >
      <p class="font-black text-xl">
        MEMUAT E-TICKET...
      </p>
    </div>


    <!-- ERROR -->
    <div
      v-else-if="error"
      class="max-w-6xl mx-auto border-4 border-black bg-white p-8 shadow-[6px_6px_0_#000]"
    >
      <p class="font-black text-xl mb-3">
        GAGAL MEMUAT TIKET
      </p>

      <p class="text-gray-600 mb-5">
        {{ error }}
      </p>

      <button
        @click="getTickets"
        class="border-4 border-black bg-black text-white px-5 py-3 font-black"
      >
        COBA LAGI
      </button>
    </div>


    <!-- EMPTY -->
    <div
      v-else-if="tickets.length === 0"
      class="max-w-6xl mx-auto border-4 border-black bg-white p-10 text-center shadow-[6px_6px_0_#000]"
    >
      <div class="text-6xl mb-5">
        🎟️
      </div>

      <h2 class="text-2xl font-black mb-2">
        BELUM ADA E-TICKET
      </h2>

      <p class="text-gray-600 mb-6">
        Kamu belum memiliki tiket event.
      </p>

      <button
        @click="$router.push('/')"
        class="border-4 border-black bg-white px-6 py-3 font-black shadow-[4px_4px_0_#000]"
      >
        CARI EVENT
      </button>
    </div>


    <!-- TICKET LIST -->
    <div
      v-else
      class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8"
    >

      <div
        v-for="(ticket, index) in tickets"
        :key="ticket.id"
        class="border-4 border-black bg-white shadow-[6px_6px_0_#000]"
      >

        <!-- TICKET HEADER -->
        <div class="border-b-4 border-black p-5">
          <div class="flex items-start justify-between gap-4">

            <div>
              <p class="text-xs font-black uppercase">
                E-TICKET #{{ index + 1 }}
              </p>

              <h2 class="text-2xl md:text-3xl font-black mt-2">
                {{ ticket.ticket?.event?.nama_event || 'Nama Event' }}
              </h2>
            </div>

            <span
              class="border-2 border-black px-3 py-1 text-xs font-black uppercase"
              :class="
                ticket.status === 'valid'
                  ? 'bg-white'
                  : 'bg-gray-300'
              "
            >
              {{ ticket.status }}
            </span>

          </div>
        </div>


        <!-- TICKET BODY -->
        <div class="p-5">

          <div class="grid grid-cols-2 gap-4 mb-6">

            <div>
              <p class="text-xs font-bold text-gray-500 uppercase">
                Jenis Tiket
              </p>

              <p class="font-black mt-1">
                {{ ticket.ticket?.nama_tiket || '-' }}
              </p>
            </div>


            <div>
              <p class="text-xs font-bold text-gray-500 uppercase">
                Kode Pesanan
              </p>

              <p class="font-black mt-1 break-all">
                {{ ticket.order?.kode_pesanan || '-' }}
              </p>
            </div>


            <div>
              <p class="text-xs font-bold text-gray-500 uppercase">
                Tanggal
              </p>

              <p class="font-black mt-1">
                {{ ticket.ticket?.event?.tanggal || '-' }}
              </p>
            </div>


            <div>
              <p class="text-xs font-bold text-gray-500 uppercase">
                Waktu
              </p>

              <p class="font-black mt-1">
                {{ ticket.ticket?.event?.waktu || '-' }}
              </p>
            </div>


            <div class="col-span-2">
              <p class="text-xs font-bold text-gray-500 uppercase">
                Lokasi
              </p>

              <p class="font-black mt-1">
                {{ ticket.ticket?.event?.lokasi || '-' }}
              </p>
            </div>

          </div>


          <!-- QR -->
          <div class="border-4 border-black p-5 text-center">

            <p class="font-black mb-4">
              SCAN QR CODE SAAT MASUK
            </p>

            <div
              :id="'qr-' + ticket.id"
              class="flex justify-center"
            >
            </div>

            <p class="text-xs text-gray-500 mt-4 break-all">
              {{ ticket.qr_token }}
            </p>

          </div>

        </div>

      </div>

    </div>

  </div>
</template>


<script setup>
import { ref, nextTick, onMounted } from 'vue'
import QRCode from 'qrcode'
import api from '../utils/api'

const tickets = ref([])
const loading = ref(true)
const error = ref('')


const getTickets = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/my-tickets')

    tickets.value = response.data.data || []

    await nextTick()

    for (const ticket of tickets.value) {
      const element = document.getElementById(`qr-${ticket.id}`)

      if (element && ticket.qr_token) {
        element.innerHTML = ''

        const canvas = document.createElement('canvas')

        await QRCode.toCanvas(
          canvas,
          ticket.qr_token,
          {
            width: 220,
            margin: 2
          }
        )

        element.appendChild(canvas)
      }
    }

  } catch (err) {
    console.error(err)

    if (err.response?.status === 401) {
      error.value = 'Sesi login kamu sudah habis. Silakan login kembali.'
    } else {
      error.value =
        err.response?.data?.message ||
        'Terjadi kesalahan saat mengambil E-Ticket.'
    }

  } finally {
    loading.value = false
  }
}


onMounted(() => {
  getTickets()
})
</script>