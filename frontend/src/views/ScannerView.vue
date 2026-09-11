<template>
  <div class="h-screen overflow-hidden bg-[#f5f5f0] p-4 md:p-6">
    <div class="mx-auto flex h-full max-w-6xl flex-col">

      <!-- HEADER -->
      <!-- HEADER -->
<div class="mb-4 flex shrink-0 items-start justify-between">

  <!-- JUDUL -->
  <div>
    <h1 class="text-3xl font-black uppercase">
      QR Scanner
    </h1>

    <p class="mt-1 text-sm font-bold">
      Scan QR Ticket untuk memvalidasi tiket.
    </p>
  </div>


  <!-- PROFILE -->
  <div class="relative">

    <!-- PROFILE BUTTON -->
    <button
      type="button"
      @click="profileOpen = !profileOpen"
      class="flex items-center gap-3 border-4 border-black bg-white px-3 py-2 font-black shadow-[4px_4px_0_#000] hover:bg-yellow-300"
    >

      <!-- ICON -->
      <div
        class="flex h-9 w-9 items-center justify-center border-2 border-black bg-yellow-300 text-lg font-black"
      >
        {{ user.name?.charAt(0)?.toUpperCase() || 'S' }}
      </div>

      <!-- NAME -->
      <span class="hidden md:block">
        {{ user.name || 'Scanner' }}
      </span>

      <!-- ARROW -->
      <span>
        {{ profileOpen ? '▲' : '▼' }}
      </span>

    </button>


    <!-- DROPDOWN -->
    <div
      v-if="profileOpen"
      class="absolute right-0 z-50 mt-3 w-48 border-4 border-black bg-white p-2 shadow-[5px_5px_0_#000]"
    >

      <!-- USER INFO -->
      <div
        class="border-b-2 border-black px-3 py-2"
      >
        <p class="text-sm font-black">
          {{ user.name || 'Scanner' }}
        </p>

        <p class="mt-1 break-all text-xs font-bold">
          {{ user.email || '' }}
        </p>
      </div>


      <!-- LOGOUT -->
      <button
        type="button"
        @click="logout"
        class="mt-2 w-full border-2 border-black bg-red-400 px-3 py-2 text-left font-black hover:bg-red-500"
      >
        Logout
      </button>

    </div>

  </div>

</div>

      <!-- CONTENT -->
      <div
        class="grid min-h-0 flex-1 grid-cols-1 gap-5 md:grid-cols-2"
      >

        <!-- ================================= -->
        <!-- KIRI : QR SCANNER -->
        <!-- ================================= -->
        <div
          class="flex min-h-0 flex-col border-4 border-black bg-white p-3 shadow-[6px_6px_0_#000]"
        >

          <!-- CAMERA -->
          <div
            id="qr-reader"
            class="w-full flex-1 overflow-hidden"
          ></div>

          <!-- LOADING -->
          <div
            v-if="loading"
            class="mt-3 shrink-0 border-4 border-black bg-yellow-300 p-3 text-center font-black shadow-[4px_4px_0_#000]"
          >
            MEMPROSES QR...
          </div>

        </div>


        <!-- ================================= -->
        <!-- KANAN : HASIL SCAN -->
        <!-- ================================= -->
        <div
          class="min-h-0 overflow-hidden"
        >

          <!-- BELUM ADA HASIL -->
          <div
            v-if="!message"
            class="flex h-full items-center justify-center border-4 border-dashed border-black bg-white p-6 text-center"
          >
            <div>

              <div class="text-5xl font-black">
                QR
              </div>

              <p class="mt-3 font-bold">
                Arahkan kamera ke QR Ticket.
              </p>

            </div>
          </div>


          <!-- HASIL SCAN -->
          <div
            v-else
            class="h-full overflow-hidden border-4 border-black bg-white p-5 shadow-[6px_6px_0_#000]"
          >

            <!-- =========================== -->
            <!-- SUCCESS -->
            <!-- =========================== -->
            <div
              v-if="success"
              class="mb-5 flex items-center gap-4 border-b-4 border-black pb-4"
            >

              <!-- ICON -->
              <div
                class="flex h-14 w-14 shrink-0 items-center justify-center border-4 border-black bg-green-400 text-3xl font-black"
              >
                ✓
              </div>

              <!-- MESSAGE -->
              <div>

                <p
                  class="text-xl font-black uppercase leading-tight"
                >
                  Tiket valid.
                </p>

                <p class="text-base font-black">
                  Berhasil digunakan.
                </p>

              </div>

            </div>


            <!-- =========================== -->
            <!-- ERROR -->
            <!-- =========================== -->
            <div
              v-else
              class="mb-5 flex items-center gap-4 border-b-4 border-black pb-4"
            >

              <!-- ICON -->
              <div
                class="flex h-14 w-14 shrink-0 items-center justify-center border-4 border-black bg-red-400 text-3xl font-black"
              >
                ✕
              </div>

              <!-- MESSAGE -->
              <div>

                <p
                  class="text-xl font-black uppercase leading-tight"
                >
                  Tiket tidak valid
                </p>

                <p class="text-base font-black">
                  {{ message }}
                </p>

              </div>

            </div>


            <!-- =========================== -->
            <!-- DETAIL TIKET -->
            <!-- =========================== -->
            <div
              v-if="scanResult"
              class="min-h-0"
            >

              <h2
                class="mb-4 text-xl font-black uppercase"
              >
                Detail Tiket
              </h2>


              <div class="space-y-3">


                <!-- EVENT -->
                <div
                  class="grid grid-cols-[130px_1fr] gap-3 border-b-2 border-black pb-2"
                >

                  <span class="font-black">
                    Event
                  </span>

                  <span class="break-words font-bold">
                    {{ scanResult.event || '-' }}
                  </span>

                </div>


                <!-- JENIS TIKET -->
                <div
                  class="grid grid-cols-[130px_1fr] gap-3 border-b-2 border-black pb-2"
                >

                  <span class="font-black">
                    Jenis Tiket
                  </span>

                  <span class="font-bold">
                    {{ scanResult.jenis_tiket || '-' }}
                  </span>

                </div>


                <!-- NAMA PEMESAN -->
                <div
                  class="grid grid-cols-[130px_1fr] gap-3 border-b-2 border-black pb-2"
                >

                  <span class="font-black">
                    Nama Pemesan
                  </span>

                  <span class="break-words font-bold">
                    {{ scanResult.nama_pemesan || '-' }}
                  </span>

                </div>


                <!-- EMAIL -->
                <div
                  class="grid grid-cols-[130px_1fr] gap-3 border-b-2 border-black pb-2"
                >

                  <span class="font-black">
                    Akun / Email
                  </span>

                  <span class="break-all font-bold">
                    {{ scanResult.email_pemesan || '-' }}
                  </span>

                </div>


                <!-- KODE PESANAN -->
                <div
                  class="grid grid-cols-[130px_1fr] gap-3 border-b-2 border-black pb-2"
                >

                  <span class="font-black">
                    Kode Pesanan
                  </span>

                  <span class="break-all font-bold">
                    {{ scanResult.kode_pesanan || '-' }}
                  </span>

                </div>


                <!-- STATUS -->
                <div
                  class="grid grid-cols-[130px_1fr] gap-3"
                >

                  <span class="font-black">
                    Status
                  </span>

                  <span
                    class="font-black uppercase"
                    :class="
                      scanResult.status === 'used'
                        ? 'text-green-700'
                        : 'text-red-700'
                    "
                  >
                    {{ scanResult.status || '-' }}
                  </span>

                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { Html5Qrcode } from 'html5-qrcode'
import api from '../utils/api'

const router = useRouter()

const profileOpen = ref(false)

const user = ref(
  JSON.parse(localStorage.getItem('user') || '{}')
)

/* =========================================
   STATE
========================================= */

const message = ref('')
const success = ref(false)
const loading = ref(false)
const scanResult = ref(null)

let scanner = null
let isProcessing = false


/* =========================================
   SOUND SUCCESS
========================================= */

const playSuccessSound = () => {
  const AudioContext =
    window.AudioContext || window.webkitAudioContext

  if (!AudioContext) return

  const audioContext = new AudioContext()

  const oscillator = audioContext.createOscillator()
  const gainNode = audioContext.createGain()

  oscillator.connect(gainNode)
  gainNode.connect(audioContext.destination)

  oscillator.type = 'sine'

  oscillator.frequency.setValueAtTime(
    880,
    audioContext.currentTime
  )

  gainNode.gain.setValueAtTime(
    0.3,
    audioContext.currentTime
  )

  gainNode.gain.exponentialRampToValueAtTime(
    0.01,
    audioContext.currentTime + 0.25
  )

  oscillator.start()

  oscillator.stop(
    audioContext.currentTime + 0.25
  )
}


/* =========================================
   SOUND ERROR
========================================= */

const playErrorSound = () => {
  const AudioContext =
    window.AudioContext || window.webkitAudioContext

  if (!AudioContext) return

  const audioContext = new AudioContext()

  const oscillator = audioContext.createOscillator()
  const gainNode = audioContext.createGain()

  oscillator.connect(gainNode)
  gainNode.connect(audioContext.destination)

  oscillator.type = 'square'

  oscillator.frequency.setValueAtTime(
    300,
    audioContext.currentTime
  )

  oscillator.frequency.setValueAtTime(
    180,
    audioContext.currentTime + 0.15
  )

  gainNode.gain.setValueAtTime(
    0.25,
    audioContext.currentTime
  )

  gainNode.gain.exponentialRampToValueAtTime(
    0.01,
    audioContext.currentTime + 0.3
  )

  oscillator.start()

  oscillator.stop(
    audioContext.currentTime + 0.3
  )
}

const logout = async () => {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  }

  localStorage.removeItem('token')
  localStorage.removeItem('user')

  router.push('/login')
}

/* =========================================
   START SCANNER
========================================= */

const startScanner = async () => {

  scanner = new Html5Qrcode('qr-reader')

  try {

    await scanner.start(

      {
        facingMode: 'environment'
      },

      {
        fps: 10,

        qrbox: {
          width: 250,
          height: 250
        },

        aspectRatio: 1
      },

      async (decodedText) => {

        /* Jangan proses QR berkali-kali */
        if (isProcessing) return

        isProcessing = true

        loading.value = true

        message.value = ''

        scanResult.value = null


        try {

          /* =================================
             KIRIM QR KE BACKEND
          ================================= */

          const response = await api.post(
            '/scanner/scan',
            {
              qr_token: decodedText
            }
          )


          /* =================================
             BERHASIL
          ================================= */

          success.value = true

          message.value =
            response.data.message

          scanResult.value =
            response.data.data


          /* SOUND */
          playSuccessSound()


        } catch (error) {

          /* =================================
             GAGAL
          ================================= */

          success.value = false

          message.value =
            error.response?.data?.message ||
            'Gagal memproses QR Ticket.'


          scanResult.value =
            error.response?.data?.data || null


          /* SOUND ERROR */
          playErrorSound()

        } finally {

          loading.value = false


          /*
           * Tunggu 2 detik sebelum
           * bisa scan lagi
           */
          setTimeout(() => {

            isProcessing = false

          }, 2000)

        }

      },

      () => {
        /*
         * Error scan biasa diabaikan.
         * Contohnya kamera belum menemukan QR.
         */
      }

    )

  } catch (error) {

    console.error(
      'Scanner error:',
      error
    )

    message.value =
      'Kamera tidak dapat digunakan. Pastikan izin kamera diberikan.'

  }

}


/* =========================================
   STOP SCANNER
========================================= */

const stopScanner = async () => {

  if (!scanner) return

  try {

    await scanner.stop()

    scanner.clear()

  } catch (error) {

    console.error(
      'Stop scanner error:',
      error
    )

  }

}


/* =========================================
   LIFECYCLE
========================================= */

onMounted(() => {

  startScanner()

})


onBeforeUnmount(() => {

  stopScanner()

})

</script>


<style>
/* =========================================
   QR READER
========================================= */

#qr-reader {
  width: 100%;
  height: 100%;
}


/* =========================================
   CAMERA VIDEO
========================================= */

#qr-reader video {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
}


/* =========================================
   SCAN REGION
========================================= */

#qr-reader__scan_region {
  width: 100% !important;
}


/* =========================================
   DASHBOARD
========================================= */

#qr-reader__dashboard {
  width: 100% !important;
}


/* =========================================
   DASHBOARD SECTION
========================================= */

#qr-reader__dashboard_section_csr {
  width: 100% !important;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 767px) {

  /*
   * Mobile boleh scroll
   */
  body {
    overflow: auto;
  }

  /*
   * Tinggi scanner jangan terlalu besar
   */
  #qr-reader {
    height: auto;
    min-height: 350px;
  }

  #qr-reader video {
    height: auto !important;
    min-height: 350px;
  }

}
</style>