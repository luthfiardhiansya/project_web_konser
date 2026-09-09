<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const visible = ref(false)
const type = ref('success')
const title = ref('')
const message = ref('')
let timer = null

const playSound = (status) => {
  const AudioContext =
    window.AudioContext || window.webkitAudioContext

  if (!AudioContext) return

  try {
    const audio = new AudioContext()
    const oscillator = audio.createOscillator()
    const gain = audio.createGain()

    oscillator.connect(gain)
    gain.connect(audio.destination)

    if (status === 'success') {
      // Suara berhasil: dua nada naik
      oscillator.frequency.setValueAtTime(520, audio.currentTime)
      oscillator.frequency.setValueAtTime(
        700,
        audio.currentTime + 0.12
      )
    } else if (status === 'warning') {
      oscillator.frequency.setValueAtTime(440, audio.currentTime)
      oscillator.frequency.setValueAtTime(
        350,
        audio.currentTime + 0.12
      )
    } else {
      // Suara gagal: dua nada turun
      oscillator.frequency.setValueAtTime(500, audio.currentTime)
      oscillator.frequency.setValueAtTime(
        280,
        audio.currentTime + 0.12
      )
    }

    gain.gain.setValueAtTime(0.08, audio.currentTime)
    gain.gain.exponentialRampToValueAtTime(
      0.001,
      audio.currentTime + 0.25
    )

    oscillator.start()
    oscillator.stop(audio.currentTime + 0.25)

    setTimeout(() => {
      audio.close()
    }, 300)
  } catch (e) {
    console.log('Audio error:', e)
  }
}

const handleAdminFlash = (event) => {
  clearTimeout(timer)

  type.value = event.detail.type || 'success'
  message.value = event.detail.message || ''
  title.value =
    event.detail.title ||
    (type.value === 'success'
      ? 'Berhasil'
      : type.value === 'warning'
      ? 'Peringatan'
      : type.value === 'info'
      ? 'Informasi'
      : 'Gagal')

  visible.value = true

  playSound(type.value)

  timer = setTimeout(() => {
    visible.value = false
  }, 3000)
}

onMounted(() => {
  window.addEventListener('admin-flash', handleAdminFlash)
})

onUnmounted(() => {
  window.removeEventListener('admin-flash', handleAdminFlash)
  clearTimeout(timer)
})
</script>

<template>
  <Transition name="admin-flash">
    <div
      v-if="visible"
      class="admin-flash"
      :class="type"
    >
      <div class="flash-icon">
        <i
          v-if="type === 'success'"
          class="fa-solid fa-check"
        ></i>
        <i
          v-else-if="type === 'warning'"
          class="fa-solid fa-triangle-exclamation"
        ></i>
        <i
          v-else-if="type === 'info'"
          class="fa-solid fa-info"
        ></i>
        <i
          v-else
          class="fa-solid fa-xmark"
        ></i>
      </div>

      <div class="flash-content">
        <strong>
          {{ title }}
        </strong>

        <span>{{ message }}</span>
      </div>

      <button @click="visible = false">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </Transition>
</template>

<style scoped>
.admin-flash {
  position: fixed;
  top: 24px;
  right: 24px;

  z-index: 9999;

  width: 340px;

  display: flex;
  align-items: center;

  gap: 12px;

  padding: 14px 16px;

  background: white;

  border: 1px solid #e5e7eb;
  border-left: 4px solid;

  border-radius: 8px;

  box-shadow:
    0 8px 25px rgba(0, 0, 0, 0.08);

  font-family: Arial, sans-serif;
}

.admin-flash.success {
  border-left-color: #16a34a;
}

.admin-flash.error {
  border-left-color: #dc2626;
}

.admin-flash.warning {
  border-left-color: #eab308;
}

.admin-flash.info {
  border-left-color: #0284c7;
}

.flash-icon {
  width: 34px;
  height: 34px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;
}

.success .flash-icon {
  background: #dcfce7;
  color: #16a34a;
}

.error .flash-icon {
  background: #fee2e2;
  color: #dc2626;
}

.warning .flash-icon {
  background: #fef9c3;
  color: #ca8a04;
}

.info .flash-icon {
  background: #e0f2fe;
  color: #0284c7;
}

.flash-content {
  flex: 1;

  display: flex;
  flex-direction: column;

  gap: 2px;
}

.flash-content strong {
  font-size: 0.9rem;
  color: #111827;
}

.flash-content span {
  font-size: 0.82rem;
  color: #6b7280;
}

.admin-flash button {
  border: none;
  background: transparent;

  color: #9ca3af;

  cursor: pointer;

  padding: 4px;
}

.admin-flash button:hover {
  color: #111827;
}

/* ANIMATION */

.admin-flash-enter-active,
.admin-flash-leave-active {
  transition: all 0.3s ease;
}

.admin-flash-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.admin-flash-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* MOBILE */

@media (max-width: 600px) {
  .admin-flash {
    left: 16px;
    right: 16px;

    width: auto;
  }
}
</style>