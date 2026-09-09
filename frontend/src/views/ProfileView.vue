<template>
  <div class="min-h-screen bg-[#f5f0e8] flex flex-col overflow-hidden">

    <!-- NAVBAR -->
    <Navbar
      :currentView="currentView"
      :favoritesCount="favorites.length"
      @navigate="navigateTo"
      @open-search="$emit('open-search')"
      @filter-category="filterCategoryQuick"
    />

    <!-- CONTENT -->
    <main class="flex-1 flex items-center justify-center px-4 py-5">
      <div class="w-full max-w-2xl">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4">
          <div>
            <p class="text-xs font-black uppercase tracking-widest">
              Account
            </p>

            <h1 class="text-3xl md:text-4xl font-black uppercase">
              My Profile
            </h1>
          </div>

          <!-- BACK TO HOME -->
          <button
            @click="$router.push('/')"
            class="border-4 border-black bg-white px-4 py-2 font-black uppercase text-sm shadow-[4px_4px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
          >
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Back
          </button>
        </div>

        <!-- PROFILE CARD -->
        <div
          class="border-4 border-black bg-white shadow-[7px_7px_0_#000] p-5 md:p-6"
        >

          <!-- USER -->
          <div class="flex items-center gap-5">

            <!-- AVATAR -->
            <div
              class="w-20 h-20 md:w-24 md:h-24 shrink-0 border-4 border-black bg-[#FFD84D] flex items-center justify-center text-4xl md:text-5xl font-black shadow-[4px_4px_0_#000]"
            >
              {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>

            <!-- USER INFO -->
            <div class="min-w-0">

              <p class="text-xs font-black uppercase tracking-widest mb-1">
                Account
              </p>

              <h2
                class="text-2xl md:text-3xl font-black uppercase truncate"
              >
                {{ user?.name || 'User' }}
              </h2>

              <p class="text-sm font-bold truncate">
                {{ user?.email || '-' }}
              </p>

            </div>
          </div>

          <!-- DIVIDER -->
          <div class="border-t-4 border-black my-6"></div>

          <!-- INFORMATION -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            <div
              class="border-2 border-black bg-[#f5f0e8] p-4"
            >
              <p class="text-[10px] font-black uppercase tracking-widest mb-1">
                Name
              </p>

              <p class="font-bold break-words">
                {{ user?.name || '-' }}
              </p>
            </div>

            <div
              class="border-2 border-black bg-[#f5f0e8] p-4"
            >
              <p class="text-[10px] font-black uppercase tracking-widest mb-1">
                Email
              </p>

              <p class="font-bold break-words">
                {{ user?.email || '-' }}
              </p>
            </div>

          </div>

          <!-- ACTIONS -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">

            <!-- MY TICKETS -->
            <button
              @click="$router.push('/my-tickets')"
              class="border-4 border-black bg-[#FFD84D] py-3 px-4 font-black uppercase text-sm shadow-[4px_4px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all"
            >
              <i class="fa-solid fa-ticket mr-2"></i>
              My Tickets
            </button>

            <!-- LOGOUT -->
            <button
              @click="logout"
              :disabled="isLoggingOut"
              class="border-4 border-black bg-[#ff6b6b] py-3 px-4 font-black uppercase text-sm shadow-[4px_4px_0_#000] hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all disabled:opacity-60 disabled:cursor-not-allowed"
            >
              <i
                class="fa-solid"
                :class="isLoggingOut ? 'fa-spinner fa-spin' : 'fa-right-from-bracket'"
              ></i>

              <span class="ml-2">
                {{ isLoggingOut ? 'Logging Out...' : 'Logout' }}
              </span>
            </button>

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
import api from '../utils/api'

export default {

  name: 'ProfileView',

  components: {
    Navbar,
    Footer
  },

  props: {

    currentView: {
      type: String,
      default: 'view-profile'
    },

    favorites: {
      type: Array,
      default: () => []
    }

  },

  data() {
    return {

      user: null,

      isQuickSearchOpen: false,

      isLoggingOut: false

    }
  },

  async mounted() {

    await this.getProfile()

  },

  methods: {

    // ==========================================
    // NAVIGATION
    // ==========================================

    navigateTo(view) {

      this.$emit('navigate', view)

    },


    filterCategoryQuick(category) {

      this.$emit('filter-category', category)

    },


    // ==========================================
    // GET PROFILE
    // ==========================================

    async getProfile() {

      try {

        const response = await api.get('/profile')

        const data = response.data

        this.user =
          data.data?.user ||
          data.data ||
          data.user ||
          null

        // Simpan data user terbaru
        if (this.user) {

          localStorage.setItem(
            'user',
            JSON.stringify(this.user)
          )

        }

      } catch (error) {

        console.error(
          'Gagal mengambil data profile:',
          error
        )

        // ======================================
        // FALLBACK LOCAL STORAGE
        // ======================================

        const savedUser =
          localStorage.getItem('user')

        if (savedUser) {

          try {

            this.user =
              JSON.parse(savedUser)

          } catch (parseError) {

            console.error(
              'Data user localStorage rusak:',
              parseError
            )

            this.user = null

          }

        }

      }

    },


    // ==========================================
    // LOGOUT
    // ==========================================

    async logout() {

      // Cegah tombol diklik berkali-kali
      if (this.isLoggingOut) {
        return
      }

      this.isLoggingOut = true

      try {

        // ======================================
        // LOGOUT DARI BACKEND
        // ======================================

        const token =
          localStorage.getItem('token')

        if (token) {

          await api.post('/logout')

        }

      } catch (error) {

        console.error(
          'Logout API gagal:',
          error
        )

      } finally {

        // ======================================
        // HAPUS DATA LOGIN
        // ======================================

        localStorage.removeItem('token')

        localStorage.removeItem('user')

        // Reset user
        this.user = null

        // Reset status
        this.isLoggingOut = false

        // ======================================
        // KEMBALI KE HOME
        // ======================================

        this.$router.push('/')

      }

    }

  }

}
</script>


<style scoped>

.profile-page {
  min-height: calc(100vh - 140px);
  padding: 110px 20px 80px;
  background: #f5f5f0;
}

.profile-container {
  max-width: 900px;
  margin: auto;
}


/* HEADER */

.profile-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;

  margin-bottom: 35px;
}

.label {
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 2px;
}

.profile-header h1 {
  margin: 7px 0 0;

  font-size: clamp(42px, 7vw, 64px);
  line-height: .9;

  font-weight: 950;
  letter-spacing: -3px;
}

.profile-header p {
  margin-top: 15px;

  font-size: 14px;
  font-weight: 600;
}


/* BACK BUTTON */

.back-button {
  border: 3px solid #111;
  background: white;

  padding: 12px 18px;

  font-size: 12px;
  font-weight: 900;

  cursor: pointer;

  box-shadow: 5px 5px 0 #111;

  transition: .15s ease;
}

.back-button:hover {
  transform: translate(3px, 3px);
  box-shadow: 2px 2px 0 #111;
}


/* CARD */

.profile-card {
  background: white;

  border: 3px solid #111;

  padding: 35px;

  box-shadow: 9px 9px 0 #111;
}


/* USER */

.profile-user {
  display: flex;
  align-items: center;
  gap: 24px;
}

.avatar {
  width: 90px;
  height: 90px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  background: #ffd84d;

  border: 3px solid #111;

  box-shadow: 5px 5px 0 #111;

  font-size: 38px;
  font-weight: 950;
}

.small-label {
  font-size: 10px;
  font-weight: 900;
  letter-spacing: 1.5px;
}

.profile-user h2 {
  margin: 6px 0;

  font-size: 30px;
  font-weight: 950;
}

.profile-user p {
  margin: 0;

  font-size: 14px;
  font-weight: 600;
}


/* DIVIDER */

.divider {
  height: 3px;

  background: #111;

  margin: 30px 0;
}


/* INFORMATION */

.information {
  display: grid;
  grid-template-columns: 1fr 1fr;

  gap: 20px;
}

.info-box {
  border: 2px solid #111;

  background: #f5f5f0;

  padding: 16px;
}

.info-box span {
  display: block;

  margin-bottom: 7px;

  font-size: 10px;
  font-weight: 900;
  letter-spacing: 1px;
}

.info-box strong {
  font-size: 14px;
  font-weight: 800;

  word-break: break-word;
}


/* ACTIONS */

.actions {
  display: flex;
  gap: 15px;
}

.ticket-button,
.logout-button {
  border: 3px solid #111;

  padding: 14px 20px;

  font-size: 12px;
  font-weight: 950;

  cursor: pointer;

  transition: .15s ease;
}

.ticket-button {
  background: #ffd84d;
  box-shadow: 5px 5px 0 #111;
}

.logout-button {
  background: #ff725c;
  box-shadow: 5px 5px 0 #111;
}

.ticket-button:hover,
.logout-button:hover {
  transform: translate(3px, 3px);

  box-shadow: 2px 2px 0 #111;
}


/* MOBILE */

@media (max-width: 600px) {

  .profile-page {
    padding: 90px 15px 60px;
  }

  .profile-header {
    align-items: flex-start;
    flex-direction: column;

    gap: 20px;
  }

  .profile-card {
    padding: 25px;
  }

  .profile-user {
    align-items: flex-start;
  }

  .information {
    grid-template-columns: 1fr;
  }

  .actions {
    flex-direction: column;
  }

  .ticket-button,
  .logout-button {
    width: 100%;
  }

}

</style>