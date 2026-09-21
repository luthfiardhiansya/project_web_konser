<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
  /**
   * 'admin'   — user biasa coba akses halaman admin
   * 'scanner' — user biasa / admin coba akses halaman scanner
   * 'user'    — scanner coba akses halaman user
   */
  type: {
    type: String,
    default: 'admin'
  }
})

const router = useRouter()

const config = {
  admin: {
    icon: '🛡️',
    title: 'Akses Admin Ditolak',
    desc: 'Anda tidak memiliki hak akses halaman ini.',
    sub: 'Halaman ini hanya dapat diakses oleh Administrator.',
    btnLabel: 'Kembali ke Beranda',
    btnAction: () => router.push('/'),
    color: '#7E22CE',
    bg: '#f3e8ff',
  },
  scanner: {
    icon: '📷',
    title: 'Akses Scanner Ditolak',
    desc: 'Anda tidak memiliki hak akses halaman ini.',
    sub: 'Halaman ini hanya dapat diakses oleh petugas Scanner.',
    btnLabel: 'Kembali ke Beranda',
    btnAction: () => router.push('/'),
    color: '#0369a1',
    bg: '#e0f2fe',
  },
  user: {
    icon: '🚫',
    title: 'Akses Ditolak',
    desc: 'Anda tidak memiliki hak akses halaman ini.',
    sub: 'Akun scanner hanya dapat mengakses halaman Scanner.',
    btnLabel: 'Pergi ke Scanner',
    btnAction: () => router.push('/scanner'),
    color: '#b45309',
    bg: '#fef3c7',
  },
}

const c = config[props.type] || config.admin
</script>

<template>
  <div class="access-denied-wrap">
    <div class="access-denied-card">

      <!-- Decoration dots -->
      <div class="deco-dot deco-dot-1"></div>
      <div class="deco-dot deco-dot-2"></div>

      <!-- Icon -->
      <div class="ad-icon" :style="{ background: c.bg, color: c.color }">
        {{ c.icon }}
      </div>

      <!-- Text -->
      <h1 class="ad-title">{{ c.title }}</h1>
      <p class="ad-desc">{{ c.desc }}</p>
      <p class="ad-sub">{{ c.sub }}</p>

      <!-- Divider -->
      <div class="ad-divider"></div>

      <!-- Button -->
      <button @click="c.btnAction" class="ad-btn" :style="{ background: c.color }">
        {{ c.btnLabel }}
      </button>

    </div>
  </div>
</template>

<style scoped>
.access-denied-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f0;
  padding: 1.5rem;
}

.access-denied-card {
  position: relative;
  background: #fff;
  border: 4px solid #111;
  box-shadow: 10px 10px 0 #111;
  padding: 3rem 2.5rem;
  max-width: 480px;
  width: 100%;
  text-align: center;
  overflow: hidden;
}

/* corner dots */
.deco-dot {
  position: absolute;
  width: 10px;
  height: 10px;
  background: #111;
  border-radius: 50%;
}
.deco-dot-1 { top: 14px; left: 14px; }
.deco-dot-2 { top: 14px; right: 14px; }

.ad-icon {
  width: 90px;
  height: 90px;
  border: 4px solid #111;
  box-shadow: 5px 5px 0 #111;
  border-radius: 0;
  font-size: 2.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.ad-title {
  font-size: 1.6rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: -0.5px;
  color: #111;
  margin: 0 0 0.6rem;
}

.ad-desc {
  font-size: 1rem;
  font-weight: 700;
  color: #333;
  margin: 0 0 0.35rem;
}

.ad-sub {
  font-size: 0.82rem;
  font-weight: 600;
  color: #666;
  margin: 0;
}

.ad-divider {
  height: 3px;
  background: #111;
  margin: 1.75rem 0;
}

.ad-btn {
  display: inline-block;
  border: 3px solid #111;
  color: #fff;
  padding: 0.75rem 2rem;
  font-size: 0.82rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 1px;
  cursor: pointer;
  box-shadow: 5px 5px 0 #111;
  transition: transform 0.12s, box-shadow 0.12s;
}

.ad-btn:hover {
  transform: translate(3px, 3px);
  box-shadow: 2px 2px 0 #111;
}

@media (max-width: 520px) {
  .access-denied-card {
    padding: 2.5rem 1.5rem;
  }
  .ad-title { font-size: 1.3rem; }
}
</style>
