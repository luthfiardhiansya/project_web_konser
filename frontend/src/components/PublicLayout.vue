<script>
import api from '../utils/api'

export default {
  name: 'PublicLayout',

  data() {
    let savedUser = null

    try {
      const user = localStorage.getItem('user')

      if (user) {
        savedUser = JSON.parse(user)
      }
    } catch (error) {
      console.error('Gagal membaca user dari localStorage:', error)
      savedUser = null
    }

    return {
      currentView: 'view-home',

      isMobileMenuOpen: false,
      isQuickSearchOpen: false,
      isProfileMenuOpen: false,

      quickSearchQuery: '',

      currentDate: '12 Sep 2026',

      // ==============================
      // LOGIN STATE
      // ==============================

      isLoggedIn: !!localStorage.getItem('token'),

      currentUser: savedUser,

      // ==============================
      // FAVORITE
      // ==============================

      favorites: [],

      // ==============================
      // LOADING
      // ==============================

      loading: false,
      error: '',

      // ==============================
      // FILTER
      // ==============================

      filters: {
        category: 'ALL',
        location: 'ALL',
        price: 'ALL',
        sort: 'DATE_ASC',
        keyword: ''
      },

      // ==============================
      // EVENTS
      // ==============================

      events: [],

      selectedEvent: null,
      selectedTicketTier: null,

      // ==============================
      // TICKETS
      // ==============================

      myTickets: [],

      // ==============================
      // LOGIN FORM
      // ==============================

      loginForm: {
        email: '',
        password: ''
      }
    }
  },

  computed: {
    filteredEvents() {
      let result = this.events.filter(event => {
        const keyword =
          this.filters.keyword.toLowerCase()

        const matchCat =
          this.filters.category === 'ALL' ||
          event.category === this.filters.category

        const matchLoc =
          this.filters.location === 'ALL' ||
          event.location === this.filters.location

        const matchKeyword =
          !keyword ||
          event.title?.toLowerCase().includes(keyword) ||
          event.venue?.toLowerCase().includes(keyword) ||
          event.organizer?.toLowerCase().includes(keyword)

        let matchPrice = true

        if (this.filters.price === 'UNDER100') {
          matchPrice =
            Number(event.minPrice) < 100000
        }

        if (this.filters.price === '100-200') {
          matchPrice =
            Number(event.minPrice) >= 100000 &&
            Number(event.minPrice) <= 200000
        }

        if (this.filters.price === 'ABOVE200') {
          matchPrice =
            Number(event.minPrice) > 200000
        }

        return (
          matchCat &&
          matchLoc &&
          matchKeyword &&
          matchPrice
        )
      })

      if (this.filters.sort === 'PRICE_ASC') {
        result.sort(
          (a, b) =>
            Number(a.minPrice) -
            Number(b.minPrice)
        )
      }

      if (this.filters.sort === 'PRICE_DESC') {
        result.sort(
          (a, b) =>
            Number(b.minPrice) -
            Number(a.minPrice)
        )
      }

      return result
    }
  },

  async mounted() {
    // ==========================================
    // LOAD USER DARI LOCALSTORAGE
    // ==========================================

    this.loadUser()

    // ==========================================
    // AMBIL EVENT
    // ==========================================

    await this.getEvents()

    // ==========================================
    // CEK LOGIN
    // ==========================================

    if (this.isLoggedIn) {
      await this.getProfile()
      await this.getMyTickets()
    }

    // ==========================================
    // KLIK DI LUAR DROPDOWN
    // ==========================================

    document.addEventListener(
      'click',
      this.handleDocumentClick
    )
  },

  beforeUnmount() {
    document.removeEventListener(
      'click',
      this.handleDocumentClick
    )
  },

  methods: {

    // ==========================================
    // LOAD USER
    // ==========================================

    loadUser() {
      const token =
        localStorage.getItem('token')

      const savedUser =
        localStorage.getItem('user')

      this.isLoggedIn = !!token

      if (!savedUser) {
        this.currentUser = null
        return
      }

      try {
        this.currentUser =
          JSON.parse(savedUser)
      } catch (error) {
        console.error(
          'User localStorage tidak valid:',
          error
        )

        this.currentUser = null
      }
    },

    // ==========================================
    // CLOSE DROPDOWN
    // ==========================================

    handleDocumentClick() {
      this.isProfileMenuOpen = false
    },

    // ==========================================
    // SHOW FLASH
    // ==========================================

    showFlash(
      title,
      message,
      type = 'success'
    ) {
      window.dispatchEvent(
        new CustomEvent('show-flash', {
          detail: {
            title,
            message,
            type
          }
        })
      )
    },

    // ==========================================
    // GET EVENTS
    // ==========================================

    async getEvents() {
      this.loading = true
      this.error = ''

      try {
        const response =
          await api.get('/events')

        console.log(
          'Response events:',
          response.data
        )

        const data =
          response.data.data || []

        this.events = data.map(event => ({
          id: event.id,

          title: event.nama_event,

          description: event.deskripsi,

          date: event.tanggal,

          time: event.waktu,

          venue: event.lokasi,

          location: event.lokasi,

          address: event.alamat,

          image:
            event.poster ||
            'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=800&q=80',

          category:
            event.category?.nama_kategori ||
            'Event',

          organizer:
            event.organizer?.name ||
            event.organizer?.nama ||
            'Info Musik BDG',

          minPrice:
            event.tickets?.length
              ? Math.min(
                  ...event.tickets.map(
                    ticket =>
                      Number(ticket.harga)
                  )
                )
              : 0,

          tickets:
            (event.tickets || []).map(ticket => ({
              id: ticket.id,

              name: ticket.nama_tiket,

              price: Number(ticket.harga),

              stock: ticket.stok,

              desc:
                `Tersedia ${ticket.stok} tiket`
            }))
        }))

        console.log(
          'Event setelah mapping:',
          this.events
        )

      } catch (error) {
        console.error(
          'Gagal mengambil event:',
          error
        )

        this.error =
          error.response?.data?.message ||
          'Gagal mengambil data event.'

        this.events = []

      } finally {
        this.loading = false
      }
    },

    // ==========================================
    // DETAIL EVENT
    // ==========================================

    async openEventDetail(eventId) {
      this.loading = true

      try {
        const response =
          await api.get(
            `/events/${eventId}`
          )

        console.log(
          'Response detail event:',
          response.data
        )

        this.selectedEvent =
          response.data.data ||
          response.data

        this.selectedTicketTier = null

        this.currentView =
          'view-detail'

        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        })

      } catch (error) {
        console.error(
          'Gagal mengambil detail event:',
          error
        )

        this.showFlash(
          'GAGAL MEMUAT DETAIL!',
          error.response?.data?.message ||
          'Gagal mengambil detail event.',
          'error'
        )

      } finally {
        this.loading = false
      }
    },

    // ==========================================
    // GET PROFILE
    // ==========================================

    async getProfile() {
      try {
        const response =
          await api.get('/profile')

        console.log(
          'Response profile:',
          response.data
        )

        const user =
          response.data.data ||
          response.data.user ||
          response.data

        if (user) {

          // PENTING:
          // JANGAN HAPUS ROLE

          this.currentUser = {
            id: user.id,

            name:
              user.name ||
              user.nama ||
              'Pengunjung BDG',

            email:
              user.email || '',

            role:
              user.role || null
          }

          // Simpan lagi user terbaru
          localStorage.setItem(
            'user',
            JSON.stringify(
              this.currentUser
            )
          )

          this.isLoggedIn = true
        }

      } catch (error) {
        console.error(
          'Gagal mengambil profile:',
          error
        )

        if (
          error.response?.status === 401
        ) {
          localStorage.removeItem('token')
          localStorage.removeItem('user')

          this.isLoggedIn = false
          this.currentUser = null
        }
      }
    },

    // ==========================================
    // LOGIN
    // ==========================================

    async handleLogin() {
      this.loading = true

      try {
        const response =
          await api.post('/login', {
            email:
              this.loginForm.email,

            password:
              this.loginForm.password
          })

        console.log(
          'Response login:',
          response.data
        )

        const token =
          response.data.data?.token

        if (!token) {
          this.showFlash(
            'LOGIN GAGAL!',
            'Login berhasil tetapi token tidak ditemukan.',
            'error'
          )

          return
        }

        // Simpan token
        localStorage.setItem(
          'token',
          token
        )

        // Ambil user
        const user =
          response.data.data?.user

        if (user) {

          this.currentUser = {
            id: user.id,

            name:
              user.name ||
              'Pengunjung BDG',

            email:
              user.email || '',

            role:
              user.role || null
          }

          // SIMPAN USER
          localStorage.setItem(
            'user',
            JSON.stringify(
              this.currentUser
            )
          )
        }

        this.isLoggedIn = true

        this.loginForm.password = ''

        this.showFlash(
          'LOGIN BERHASIL!',
          `Selamat datang, ${
            this.currentUser?.name ||
            'User'
          }`,
          'success'
        )

        this.navigateTo(
          'view-home'
        )

        await this.getMyTickets()

      } catch (error) {
        console.error(
          'Login gagal:',
          error
        )

        this.showFlash(
          'LOGIN GAGAL!',
          error.response?.data?.message ||
          'Email atau password salah.',
          'error'
        )

      } finally {
        this.loading = false
      }
    },

    // ==========================================
    // LOGOUT
    // ==========================================

    async logout() {
      try {
        await api.post('/logout')

      } catch (error) {
        console.error(
          'Logout backend:',
          error
        )

      } finally {

        // Hapus token
        localStorage.removeItem(
          'token'
        )

        // Hapus user
        localStorage.removeItem(
          'user'
        )

        // Reset state
        this.isLoggedIn = false

        this.currentUser = null

        this.myTickets = []

        this.isProfileMenuOpen = false

        this.isMobileMenuOpen = false

        this.showFlash(
          'LOGOUT BERHASIL!',
          'Sampai jumpa lagi.',
          'success'
        )

        this.navigateTo(
          'view-home'
        )
      }
    },

    // ==========================================
    // GET TIKET
    // ==========================================

    async getMyTickets() {
      try {
        const response =
          await api.get('/my-tickets')

        console.log(
          'Response tiket:',
          response.data
        )

        this.myTickets =
          response.data.data ||
          response.data ||
          []

      } catch (error) {
        console.error(
          'Gagal mengambil tiket:',
          error
        )

        this.myTickets = []
      }
    },

    // ==========================================
    // CHECKOUT
    // ==========================================

    async startCheckout(
      event,
      ticket
    ) {

      if (!this.isLoggedIn) {
        this.showFlash(
          'LOGIN DIPERLUKAN!',
          'Silakan login terlebih dahulu untuk membeli tiket.',
          'warning'
        )

        this.navigateTo(
          'view-login'
        )

        return
      }

      this.loading = true

      try {
        const response =
          await api.post(
            '/orders',
            {
              event_id: event.id,

              ticket_id: ticket.id,

              jumlah: 1
            }
          )

        console.log(
          'Response order:',
          response.data
        )

        this.showFlash(
          'PEMESANAN BERHASIL!',
          `Berhasil memesan tiket ${ticket.name} untuk ${event.title}!`,
          'success'
        )

        await this.getMyTickets()

        this.navigateTo(
          'view-my-tickets'
        )

      } catch (error) {
        console.error(
          'Gagal memesan tiket:',
          error
        )

        this.showFlash(
          'PEMESANAN GAGAL!',
          error.response?.data?.message ||
          'Gagal melakukan pemesanan tiket.',
          'error'
        )

      } finally {
        this.loading = false
      }
    },

    // ==========================================
    // NAVIGASI
    // ==========================================

    navigateTo(viewName) {
      this.currentView = viewName

      this.isMobileMenuOpen = false

      this.isProfileMenuOpen = false

      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      })
    },

    // ==========================================
    // FORMAT NUMBER
    // ==========================================

    formatNumber(val) {
      return new Intl.NumberFormat(
        'id-ID'
      ).format(
        Number(val) || 0
      )
    },

    // ==========================================
    // FAVORITE
    // ==========================================

    toggleFavorite(eventId) {
      const idx =
        this.favorites.indexOf(
          eventId
        )

      if (idx > -1) {
        this.favorites.splice(
          idx,
          1
        )
      } else {
        this.favorites.push(
          eventId
        )
      }
    },

    // ==========================================
    // FILTER CATEGORY
    // ==========================================

    filterCategoryQuick(catName) {
      this.filters.category =
        catName

      this.navigateTo(
        'view-events'
      )
    },

    // ==========================================
    // RESET FILTER
    // ==========================================

    resetFilters() {
      this.filters = {
        category: 'ALL',
        location: 'ALL',
        price: 'ALL',
        sort: 'DATE_ASC',
        keyword: ''
      }
    },

    // ==========================================
    // QUICK SEARCH
    // ==========================================

    executeQuickSearch() {
      this.filters.keyword =
        this.quickSearchQuery

      this.isQuickSearchOpen = false

      this.navigateTo(
        'view-events'
      )
    },

    quickFilterTag(tag) {
      this.filters.keyword =
        tag

      this.isQuickSearchOpen = false

      this.navigateTo(
        'view-events'
      )
    },

    // ==========================================
    // E-TICKET
    // ==========================================

    showETicketModal(ticket) {
      this.showFlash(
        'E-TICKET VALID',
        `Kode: ${ticket.ticketCode || ticket.kode_tiket} • Event: ${ticket.eventTitle || ticket.event?.title} • Atas Nama: ${this.currentUser?.name || 'User'}`,
        'success'
      )
    }
  }
}
</script>