<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../utils/api'
import { showFlash } from '../utils/flash'

const route = useRoute()

const loading = ref(false)
const error = ref('')

const orderId = route.params.orderId

const bayar = async () => {
  loading.value = true
  error.value = ''

  try {
    // Minta Snap Token dari Laravel
    const response = await api.post('/payments/snap-token', {
      order_id: orderId
    })

    const snapToken = response.data.snap_token

    if (!snapToken) {
      throw new Error('Snap Token tidak ditemukan.')
    }

    // Buka popup Midtrans
    window.snap.pay(snapToken, {
      onSuccess: function (result) {
        console.log('Pembayaran berhasil:', result)
        showFlash('Pembayaran berhasil!', 'success', 'PEMBAYARAN BERHASIL')
      },

      onPending: function (result) {
        console.log('Pembayaran pending:', result)
        showFlash('Pembayaran masih pending.', 'warning', 'PEMBAYARAN PENDING')
      },

      onError: function (result) {
        console.log('Pembayaran gagal:', result)
        showFlash('Pembayaran gagal.', 'error', 'PEMBAYARAN GAGAL')
      },

      onClose: function () {
        console.log('Popup pembayaran ditutup.')
      }
    })
  } catch (err) {
    console.error(err)

    error.value =
      err.response?.data?.message ||
      err.message ||
      'Gagal membuat pembayaran.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  console.log('Order ID:', orderId)
})
</script>

<template>
  <div class="min-h-screen bg-[#FAF7F0] flex items-center justify-center p-6">
    <div class="w-full max-w-md">
      <div class="bg-white border-4 border-black shadow-[8px_8px_0px_#000] p-6">

        <h1 class="text-3xl font-extrabold mb-2">
          Pembayaran
        </h1>

        <p class="mb-6">
          Pesanan #{{ orderId }}
        </p>

        <button
          @click="bayar"
          :disabled="loading"
          class="w-full bg-[#FFDE59] border-4 border-black px-5 py-3 font-bold shadow-[4px_4px_0px_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition disabled:opacity-50"
        >
          {{ loading ? 'Memproses...' : 'Bayar Sekarang' }}
        </button>

        <p
          v-if="error"
          class="mt-4 p-3 bg-[#FF5757] border-2 border-black font-semibold"
        >
          {{ error }}
        </p>

      </div>
    </div>
  </div>
</template>