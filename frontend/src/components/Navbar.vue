<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import api from '../utils/api'

const props = defineProps({
  currentView: {
    type: String,
    default: 'view-home'
  },
  favoritesCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['navigate', 'open-search', 'filter-category'])

const router = useRouter()
const isMobileMenuOpen = ref(false)
const isProfileMenuOpen = ref(false)
const isLoggedIn = ref(!!localStorage.getItem('token'))
const currentUser = ref(null)

const loadUser = () => {
  const token = localStorage.getItem('token')
  const savedUser = localStorage.getItem('user')
  isLoggedIn.value = !!token

  if (!savedUser) {
    currentUser.value = null
    return
  }

  try {
    currentUser.value = JSON.parse(savedUser)
  } catch (error) {
    console.error('User localStorage tidak valid:', error)
    currentUser.value = null
  }
}

const handleDocumentClick = () => {
  isProfileMenuOpen.value = false
}

const onNavClick = (viewName) => {
  isMobileMenuOpen.value = false
  isProfileMenuOpen.value = false
  emit('navigate', viewName)
}

const onFilterCat = (catName) => {
  isMobileMenuOpen.value = false
  emit('filter-category', catName)
}

const logout = async () => {
  try {
    await api.post('/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')

    isLoggedIn.value = false
    currentUser.value = null
    isProfileMenuOpen.value = false
    isMobileMenuOpen.value = false

    router.push('/login')
  }
}

onMounted(() => {
  loadUser()
  document.addEventListener('click', handleDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<template>
  <header class="sticky top-0 z-40 bg-white nb-border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

      <!-- LOGO -->
      <button
        @click="onNavClick('view-home')"
        class="flex items-center space-x-2 group text-left"
      >
          <span class="font-heading font-black text-xl sm:text-2xl tracking-tight text-brandBlack">
                    INFO<span class="bg-brandYellow px-1 border border-brandBlack shadow-[2px_2px_0px_#121212]">MUSIK</span>BDG
                </span>
      </button>

      <!-- DESKTOP NAV LINKS -->
      <nav class="hidden md:flex items-center space-x-6 text-sm font-bold uppercase tracking-tight">
        <button
          @click="onNavClick('view-home')"
          :class="{ 'bg-accent border-ink': currentView === 'view-home' }"
          class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink"
        >
          Beranda
        </button>

        <button
          @click="onNavClick('view-events')"
          :class="{ 'bg-accent border-ink': currentView === 'view-events' }"
          class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink"
        >
          Event
        </button>

        <button
          @click="onFilterCat('Indie')"
          class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink"
        >
          Gigs
        </button>

        <button
          @click="onNavClick('view-community')"
          :class="{ 'bg-accent border-ink': currentView === 'view-community' }"
          class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink"
        >
          Komunitas
        </button>

        <button
          @click="onNavClick('view-organizer-reg')"
          :class="{ 'bg-accent border-ink': currentView === 'view-organizer-reg' }"
          class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink"
        >
          Organizer
        </button>
      </nav>

      <!-- RIGHT NAV ACTIONS -->
      <div class="hidden md:flex items-center space-x-3">

        <!-- SEARCH -->
        <button
          @click="emit('open-search')"
          class="p-2 nb-btn nb-btn-secondary"
          title="Cari Event"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </button>

        <!-- FAVORITES -->
        <button
  @click="$router.push('/wishlist')"
  class="p-2 nb-btn nb-btn-secondary flex items-center gap-1.5"
  title="Favorit"
>
  <svg
    class="w-4 h-4"
    fill="none"
    stroke="currentColor"
    viewBox="0 0 24 24"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2.5"
      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
    />
  </svg>

  <span
    v-if="favoritesCount > 0"
    class="text-[11px] font-black leading-none"
  >
    {{ favoritesCount }}
  </span>
</button>

        <!-- USER AUTH -->
        <div class="flex items-center space-x-2">
          <!-- BELUM LOGIN -->
          <template v-if="!isLoggedIn">
            <button
              @click="$router.push('/login')"
              class="px-3 py-1.5 text-xs uppercase nb-btn nb-btn-secondary"
            >
              Masuk
            </button>
            <button
              @click="$router.push('/register')"
              class="px-3 py-1.5 text-xs uppercase nb-btn nb-btn-primary"
            >
              Daftar
            </button>
          </template>

          <!-- SUDAH LOGIN -->
          <template v-else>
            <div class="relative">
              <button
                type="button"
                @click.stop="isProfileMenuOpen = !isProfileMenuOpen"
                class="flex items-center gap-2 px-3 py-1.5 nb-btn nb-btn-secondary"
              >
                <div class="w-7 h-7 bg-accent border-2 border-ink flex items-center justify-center font-black">
                  <i class="fa-solid fa-user text-xs"></i>
                </div>
                <span class="text-xs uppercase font-black max-w-[120px] truncate">
                  {{ currentUser?.name || 'USER' }}
                </span>
                <i
                  class="fa-solid fa-chevron-down text-[10px] transition-transform"
                  :class="{ 'rotate-180': isProfileMenuOpen }"
                ></i>
              </button>

              <!-- PROFILE DROPDOWN -->
              <div
                v-if="isProfileMenuOpen"
                class="absolute right-0 top-full mt-2 w-56 bg-white border-2 border-ink shadow-[6px_6px_0px_#000] z-[100]"
              >
                <div class="px-4 py-3 border-b-2 border-ink">
                  <p class="font-black text-sm uppercase">
                    {{ currentUser?.name || 'USER' }}
                  </p>
                  <p class="text-xs font-medium truncate mt-1">
                    {{ currentUser?.email || '' }}
                  </p>
                  <span
                    v-if="currentUser?.role"
                    class="inline-block mt-2 px-2 py-1 text-[10px] font-black uppercase bg-accent border border-ink"
                  >
                    {{ currentUser.role }}
                  </span>
                </div>

                <!-- MY PROFILE -->
                <button
                  type="button"
                  @click="$router.push('/profile')"
                  class="w-full px-4 py-3 text-left text-xs font-black uppercase hover:bg-accent border-b border-ink flex items-center gap-3 transition-colors"
                >
                  <i class="fa-solid fa-user w-4"></i>
                  <span>My Profile</span>
              </button>

                <!-- PESANAN SAYA -->
                <button
                  type="button"
                  @click="onNavClick('view-pesanan')"
                  class="w-full px-4 py-3 text-left text-xs font-black uppercase hover:bg-accent border-b border-ink flex items-center gap-3 transition-colors"
                >
                  <i class="fa-solid fa-cart-shopping w-4"></i>
                  <span>Pesanan Saya</span>
                </button>

                <!-- HALAMAN ADMIN -->
                <button
                  v-if="currentUser?.role === 'admin'"
                  type="button"
                  @click="$router.push('/admin'); isProfileMenuOpen = false"
                  class="w-full px-4 py-3 text-left text-xs font-black uppercase hover:bg-yellow-300 border-b border-ink flex items-center gap-3 transition-colors"
                >
                  <i class="fa-solid fa-shield-halved w-4"></i>
                  <span>Halaman Admin</span>
                </button>

                <!-- LOGOUT -->
                <button
                  type="button"
                  @click="logout"
                  class="w-full px-4 py-3 text-left text-xs font-black uppercase hover:bg-red-400 flex items-center gap-3 transition-colors"
                >
                  <i class="fa-solid fa-right-from-bracket w-4"></i>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- MOBILE HAMBURGER BUTTON -->
      <div class="flex md:hidden items-center space-x-2">
        <button
          @click="isMobileMenuOpen = !isMobileMenuOpen"
          class="p-2 nb-btn nb-btn-secondary"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

    </div>

    <!-- MOBILE MENU OVERLAY -->
    <div
      v-if="isMobileMenuOpen"
      class="md:hidden bg-paper nb-border-b px-4 py-4 space-y-3"
    >
      <div class="flex flex-col space-y-2 text-sm font-bold uppercase">
        <button @click="onNavClick('view-home')" class="p-2 text-left hover:bg-accent border border-ink">
          Beranda
        </button>
        <button @click="onNavClick('view-events')" class="p-2 text-left hover:bg-accent border border-ink">
          Semua Event
        </button>
        <button @click="onNavClick('view-my-tickets')" class="p-2 text-left hover:bg-accent border border-ink">
          Tiket Saya
        </button>
        <button @click="onNavClick('view-favorites')" class="p-2 text-left hover:bg-accent border border-ink">
          Event Favorit ({{ favoritesCount }})
        </button>
        <button @click="onNavClick('view-community')" class="p-2 text-left hover:bg-accent border border-ink">
          Komunitas BDG
        </button>
        <button @click="onNavClick('view-organizer-reg')" class="p-2 text-left bg-beige hover:bg-accent border border-ink">
          Dashboard Organizer
        </button>

        <template v-if="isLoggedIn">
          <div class="border-t-2 border-ink pt-3 mt-2">
            <div class="p-3 bg-accent border-2 border-ink">
              <p class="font-black text-sm uppercase">{{ currentUser?.name || 'USER' }}</p>
              <p class="text-xs font-medium mt-1 truncate">{{ currentUser?.email }}</p>
              <span v-if="currentUser?.role" class="inline-block mt-2 px-2 py-1 text-[10px] font-black uppercase bg-paper border border-ink">
                {{ currentUser.role }}
              </span>
            </div>
          </div>
          <button @click="onNavClick('view-profile')" class="p-2 text-left hover:bg-accent border border-ink flex items-center gap-3">
            <i class="fa-solid fa-user w-4"></i> My Profile
          </button>
          <button @click="onNavClick('view-my-tickets')" class="p-2 text-left hover:bg-accent border border-ink flex items-center gap-3">
            <i class="fa-solid fa-ticket w-4"></i> My Ticket
          </button>
          <button @click="onNavClick('view-pesanan')" class="p-2 text-left hover:bg-accent border border-ink flex items-center gap-3">
            <i class="fa-solid fa-cart-shopping w-4"></i> Pesanan Saya
          </button>
          <button v-if="currentUser?.role === 'admin'" @click="$router.push('/admin'); isMobileMenuOpen = false" class="p-2 text-left hover:bg-yellow-300 border border-ink flex items-center gap-3">
            <i class="fa-solid fa-shield-halved w-4"></i> Halaman Admin
          </button>
          <button @click="logout" class="p-2 text-left hover:bg-red-400 border border-ink flex items-center gap-3 text-red-700">
            <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
          </button>
        </template>
      </div>

      <div v-if="!isLoggedIn" class="pt-2 border-t border-ink flex space-x-2">
        <button @click="$router.push('/login'); isMobileMenuOpen = false" class="flex-1 py-2 text-xs uppercase nb-btn nb-btn-secondary">
          Masuk
        </button>
        <button @click="$router.push('/register'); isMobileMenuOpen = false" class="flex-1 py-2 text-xs uppercase nb-btn nb-btn-primary">
          Daftar
        </button>
      </div>
    </div>
  </header>
</template>
