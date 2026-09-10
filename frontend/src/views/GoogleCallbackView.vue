<template>
  <div class="min-h-screen flex items-center justify-center bg-[#f5f5f0]">
    <div class="text-center">
      <h1 class="text-2xl font-black mb-2">
        Memproses Login Google...
      </h1>

      <p class="text-sm">
        Tunggu sebentar, kamu akan diarahkan.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    const params = new URLSearchParams(window.location.search)

    const token = params.get('token')
    const userParam = params.get('user')

    // Cek token
    if (!token) {
      alert('Token Google tidak ditemukan.')
      window.location.href = '/login'
      return
    }

    // Simpan token
    localStorage.setItem('token', token)

    // Simpan data user
    if (userParam) {
      try {
        const user = JSON.parse(userParam)

        localStorage.setItem('user', JSON.stringify(user))
      } catch (error) {
        console.error('Gagal membaca data user:', error)
      }
    }

    // Arahkan ke halaman utama
    window.location.href = '/'
  }
}
</script>