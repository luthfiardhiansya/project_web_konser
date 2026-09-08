  <template>
    <div class="min-h-screen flex flex-col justify-between selection:bg-accent selection:text-ink">

      <!-- HEADER & NAVBAR -->
      <Navbar
        :currentView="currentView"
        :favoritesCount="favorites.length"
        @navigate="navigateTo"
        @open-search="isQuickSearchOpen = true"
        @filter-category="filterCategoryQuick"
      />

      <!-- QUICK SEARCH OVERLAY -->
      <div v-if="isQuickSearchOpen" class="fixed inset-0 bg-ink/70 z-50 flex items-start justify-center pt-20 px-4">
        <div class="bg-paper nb-card w-full max-w-2xl p-6 relative">
          <button @click="isQuickSearchOpen = false" class="absolute top-4 right-4 font-black text-xl hover:bg-accent px-2 border border-ink">✕</button>
          <h3 class="font-black text-xl mb-4 uppercase">CARI EVENT MUSIK BANDUNG</h3>
          <div class="flex gap-2 mb-4">
            <input type="text" v-model="quickSearchQuery" @keyup.enter="executeQuickSearch" placeholder="Ketik nama band, venue, atau festival..." class="nb-input flex-1">
            <button @click="executeQuickSearch" class="nb-btn nb-btn-primary px-6">CARI</button>
          </div>
          <div class="text-xs font-bold uppercase mb-2">PENCARIAN POPULER:</div>
          <div class="flex flex-wrap gap-2 text-xs font-bold">
            <button @click="quickFilterTag('Gudang Selatan')" class="nb-badge bg-white hover:bg-accent">Gudang Selatan</button>
            <button @click="quickFilterTag('Indie')" class="nb-badge bg-white hover:bg-accent">Indie Night</button>
            <button @click="quickFilterTag('Laswi Heritage')" class="nb-badge bg-white hover:bg-accent">Laswi Heritage</button>
            <button @click="quickFilterTag('Jazz')" class="nb-badge bg-white hover:bg-accent">Jazz</button>
          </div>
        </div>
      </div>

      <!-- MAIN CONTAINER (ROUTING VIEW SIMULATION) -->
      <main class="flex-grow">
        
        <!-- VIEW 1: HOMEPAGE -->
        <section v-if="currentView === 'view-home'" class="space-y-12 pb-16">
          <!-- HERO SECTION -->
          <div class="bg-paper border-b-2 border-ink py-10 lg:py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
              
              <div class="lg:col-span-7 space-y-6">
                <div class="inline-block bg-accent px-3 py-1 font-bold text-xs uppercase nb-border">
                  🔥 PLATFORM MUSIK INDEPENDEN BANDUNG
                </div>
                
                <h1 class="text-4xl sm:text-6xl font-black uppercase tracking-tight leading-none text-ink">
                  JANGAN SAMPAI <br>
                  <span class="bg-accent px-2 border-2 border-ink inline-block my-1 shadow-[4px_4px_0px_0px_#111111]">KELEWAT</span> <br>
                  EVENT MUSIK BDG.
                </h1>
                
                <p class="text-base sm:text-lg font-medium text-ink max-w-xl">
                  Temukan konser, gigs, festival, dan acara musik lokal terbaik di Bandung. Dukung musisi lokal dan dapatkan tiket langsung tanpa ribet.
                </p>

                <!-- HERO SEARCH BAR -->
                <div class="nb-card p-2 bg-white flex flex-col sm:flex-row gap-2 max-w-xl">
                  <input type="text" v-model="filters.keyword" placeholder="Cari event, artis, atau venue di Bandung..." class="nb-input flex-1 border-none">
                  <button @click="navigateTo('view-events')" class="nb-btn nb-btn-primary px-6 py-3 text-sm font-bold uppercase whitespace-nowrap">
                    🔍 Cari Event
                  </button>
                </div>

                <!-- HERO QUICK TAGS -->
                <div class="flex flex-wrap items-center gap-2 pt-2 text-xs font-bold uppercase">
                  <span class="text-muted">Kategori:</span>
                  <button @click="filterCategoryQuick('Festival')" class="nb-badge bg-white hover:bg-accent">Festival</button>
                  <button @click="filterCategoryQuick('Gigs')" class="nb-badge bg-white hover:bg-accent">Gigs</button>
                  <button @click="filterCategoryQuick('Indie')" class="nb-badge bg-white hover:bg-accent">Indie</button>
                  <button @click="filterCategoryQuick('Rock')" class="nb-badge bg-white hover:bg-accent">Rock</button>
                  <button @click="filterCategoryQuick('Jazz')" class="nb-badge bg-white hover:bg-accent">Jazz</button>
                </div>
              </div>

              <!-- HERO RIGHT VISUAL -->
              <div class="lg:col-span-5 relative">
                <div class="relative z-10 nb-card bg-white p-3 rotate-1 transform hover:rotate-0 transition-transform">
                  <img src="/src/assets/deftones.webp" alt="Bandung Live Music Scene" class="w-full h-64 sm:h-80 object-cover nb-border">
                  <div class="mt-3 flex justify-between items-center">
                    <div>
                      <div class="font-black text-base uppercase">Deftones In Bandung!!</div>
                      <div class="text-xs font-semibold text-muted">📍 Sekeawi Sukamenak, KAB.Bandung</div>
                    </div>
                    <button @click="openEventDetail('event-1')" class="nb-btn nb-btn-primary text-xs px-3 py-1.5 uppercase">Beli Tiket</button>
                  </div>
                </div>

              </div>

            </div>
          </div>

          <!-- UPCOMING EVENTS GRID -->
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end border-b-2 border-ink pb-4 gap-2">
              <div>
                <div class="text-xs font-bold uppercase tracking-widest text-muted">AGENDA TERKINI</div>
                <h2 class="text-3xl font-black uppercase tracking-tight">EVENT TERDEKAT DI BANDUNG</h2>
              </div>
              <button @click="navigateTo('view-events')" class="nb-btn nb-btn-secondary px-4 py-2 text-xs uppercase">
                Lihat Semua Event ({{ events.length }}) →
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="event in events.slice(0, 3)" :key="event.id" class="nb-card bg-white flex flex-col justify-between nb-card-hover">
                <div>
                  <div class="relative">
                    <img :src="event.image" :alt="event.title" class="w-full h-48 object-cover border-b-2 border-ink">
                    <span class="absolute top-2 left-2 bg-accent text-ink font-black text-xs px-2 py-1 nb-border">
                      {{ event.category }}
                    </span>
                    <button @click="toggleFavorite(event.id)" class="absolute top-2 right-2 p-1.5 bg-white nb-border hover:bg-accent">
                      <svg class="w-4 h-4" :fill="favorites.includes(event.id) ? '#111111' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                      </svg>
                    </button>
                  </div>
                  <div class="p-4 space-y-2">
                    <div class="text-xs font-bold text-muted uppercase">📅 {{ event.date }} • {{ event.time }}</div>
                    <h3 class="font-black text-xl uppercase leading-tight">{{ event.title }}</h3>
                    <div class="text-xs font-semibold text-ink/80">📍 {{ event.venue }}, {{ event.location }}</div>
                  </div>
                </div>
                <div class="p-4 pt-0 flex items-center justify-between border-t border-ink/20 mt-4">
                  <div>
                    <div class="text-[10px] font-bold text-muted uppercase">Mulai Dari</div>
                    <div class="font-black text-sm">Rp {{ formatNumber(event.minPrice) }}</div>
                  </div>
                  <button @click="openEventDetail(event.id)" class="nb-btn nb-btn-primary px-3 py-1.5 text-xs uppercase">
                    Detail Event
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- VIEW 2: EVENT CATALOG -->
        <section v-if="currentView === 'view-events'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
          <div class="border-b-2 border-ink pb-4">
            <div class="text-xs font-bold uppercase tracking-widest text-muted">KATALOG EVENT</div>
            <h1 class="text-4xl font-black uppercase tracking-tight">JELAJAHI EVENT MUSIK BANDUNG</h1>
          </div>

          <!-- FILTERS BAR -->
          <div class="nb-card bg-white p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label class="block text-xs font-black uppercase mb-1">Kategori Music</label>
                <select v-model="filters.category" class="nb-input w-full">
                  <option value="ALL">Semua Kategori</option>
                  <option value="Festival">Festival Musik</option>
                  <option value="Gigs">Gigs & Show</option>
                  <option value="Indie">Indie Pop / Rock</option>
                  <option value="Jazz">Jazz / Soul</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-black uppercase mb-1">Wilayah Bandung</label>
                <select v-model="filters.location" class="nb-input w-full">
                  <option value="ALL">Semua Wilayah</option>
                  <option value="Bandung Tengah">Bandung Tengah</option>
                  <option value="Bandung Utara">Bandung Utara</option>
                  <option value="Bandung Selatan">Bandung Selatan</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-black uppercase mb-1">Harga Tiket</label>
                <select v-model="filters.price" class="nb-input w-full">
                  <option value="ALL">Semua Harga</option>
                  <option value="UNDER100">Di bawah Rp100.000</option>
                  <option value="100-200">Rp100.000 - Rp200.000</option>
                  <option value="ABOVE200">Di atas Rp200.000</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-black uppercase mb-1">Urutkan</label>
                <select v-model="filters.sort" class="nb-input w-full">
                  <option value="DATE_ASC">Tanggal Terdekat</option>
                  <option value="PRICE_ASC">Harga Terendah</option>
                  <option value="PRICE_DESC">Harga Tertinggi</option>
                </select>
              </div>
            </div>
            <div class="flex gap-2">
              <input type="text" v-model="filters.keyword" placeholder="Cari nama event, artis, venue..." class="nb-input flex-1">
              <button @click="resetFilters" class="nb-btn nb-btn-secondary px-4 text-xs uppercase">Reset</button>
            </div>
          </div>

          <!-- LISTING RESULTS -->
          <div class="text-xs font-bold uppercase text-muted mb-4">
            Menampilkan {{ filteredEvents.length }} Event Musik
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="event in filteredEvents" :key="event.id" class="nb-card bg-white flex flex-col justify-between nb-card-hover">
              <div>
                <img :src="event.image" :alt="event.title" class="w-full h-48 object-cover border-b-2 border-ink">
                <div class="p-4 space-y-2">
                  <span class="bg-accent text-ink font-black text-xs px-2 py-0.5 nb-border uppercase">{{ event.category }}</span>
                  <h3 class="font-black text-xl uppercase leading-tight pt-1">{{ event.title }}</h3>
                  <p class="text-xs font-bold text-muted">📅 {{ event.date }} • {{ event.time }}</p>
                  <p class="text-xs font-semibold text-ink/80">📍 {{ event.venue }}</p>
                </div>
              </div>
              <div class="p-4 pt-0 flex justify-between items-center border-t border-ink/10 mt-2">
                <span class="font-black text-sm">Rp {{ formatNumber(event.minPrice) }}</span>
                <button @click="openEventDetail(event.id)" class="nb-btn nb-btn-primary px-3 py-1 text-xs uppercase">Detail</button>
              </div>
            </div>
          </div>
        </section>

        <!-- VIEW 3: EVENT DETAIL -->
        <section v-if="currentView === 'view-detail' && selectedEvent" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
          <button @click="navigateTo('view-events')" class="font-bold text-xs uppercase flex items-center gap-1 underline">
            ← Kembali ke Katalog Event
          </button>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8 space-y-6">
              <img :src="selectedEvent.image" :alt="selectedEvent.title" class="w-full h-80 object-cover nb-card">
              <div>
                <span class="bg-accent px-2 py-1 text-xs font-black uppercase nb-border">{{ selectedEvent.category }}</span>
                <h1 class="text-3xl font-black uppercase tracking-tight mt-2">{{ selectedEvent.title }}</h1>
                <p class="text-sm font-bold text-muted">Diselenggarakan oleh {{ selectedEvent.organizer }}</p>
              </div>
              
              <div class="nb-card bg-white p-4 grid grid-cols-2 gap-4">
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Waktu</div>
                  <div class="font-black text-sm">{{ selectedEvent.date }} • {{ selectedEvent.time }}</div>
                </div>
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Lokasi / Venue</div>
                  <div class="font-black text-sm">{{ selectedEvent.venue }}</div>
                </div>
              </div>

              <div class="space-y-2">
                <h3 class="font-black text-xl uppercase">Deskripsi Event</h3>
                <p class="text-sm leading-relaxed font-medium text-ink/90">{{ selectedEvent.description }}</p>
              </div>
            </div>

            <!-- TICKET TIER SELECTION -->
            <div class="lg:col-span-4 space-y-4">
              <div class="nb-card bg-white p-6 space-y-4">
                <h3 class="font-black text-xl uppercase border-b-2 border-ink pb-2">PILIH TIKET</h3>
                <div v-for="ticket in selectedEvent.tickets" :key="ticket.id" class="p-3 border-2 border-ink space-y-2" :class="{ 'bg-accent/20': selectedTicketTier?.id === ticket.id }">
                  <div class="flex justify-between font-black text-sm">
                    <span>{{ ticket.name }}</span>
                    <span>Rp {{ formatNumber(ticket.price) }}</span>
                  </div>
                  <p class="text-xs font-medium text-muted">{{ ticket.desc }}</p>
                  <button @click="openCheckoutModal(selectedEvent, ticket)" class="nb-btn nb-btn-primary w-full py-1.5 text-xs uppercase mt-2">
                    Pilih Tiket Ini
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- VIEW 4: LOGIN PAGE -->
        <section v-if="currentView === 'view-login'" class="py-12 px-4 max-w-md mx-auto">
          <div class="nb-card bg-white p-8 space-y-6">
            <div class="text-center space-y-1">
              <div class="inline-block bg-accent px-2 py-0.5 text-xs font-black uppercase border border-ink">INFO MUSIK BDG</div>
              <h1 class="text-2xl font-black uppercase tracking-tight">MASUK KE AKUN</h1>
            </div>
            <form @submit.prevent="handleLogin" class="space-y-4">
              <div>
                <label class="block text-xs font-black uppercase mb-1">Email</label>
                <input type="email" v-model="loginForm.email" required class="nb-input w-full">
              </div>
              <div>
                <label class="block text-xs font-black uppercase mb-1">Password</label>
                <input type="password" v-model="loginForm.password" required class="nb-input w-full">
              </div>
              <button type="submit" class="nb-btn nb-btn-primary w-full py-3 text-sm font-black uppercase">
                MASUK SEKARANG
              </button>
            </form>
          </div>
        </section>

        <!-- VIEW 5: MY TICKETS -->
        <section v-if="currentView === 'view-my-tickets'" class="max-w-5xl mx-auto px-4 py-8 space-y-6">
          <h1 class="text-3xl font-black uppercase tracking-tight border-b-2 border-ink pb-4">TIKET SAYA</h1>
          <div v-if="myTickets.length === 0" class="nb-card bg-white p-8 text-center space-y-4">
            <p class="font-bold text-muted uppercase">Belum ada tiket yang dibeli.</p>
            <button @click="navigateTo('view-events')" class="nb-btn nb-btn-primary px-4 py-2 text-xs uppercase">Cari Event Sekarang</button>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="t in myTickets" :key="t.ticketCode" class="nb-card bg-white p-4 space-y-3">
              <div class="flex justify-between items-start">
                <span class="bg-accent px-2 py-0.5 text-[10px] font-black border border-ink uppercase">E-TICKET</span>
                <span class="text-xs font-mono font-bold">{{ t.ticketCode }}</span>
              </div>
              <h3 class="font-black text-lg uppercase">{{ t.eventTitle }}</h3>
              <div class="text-xs font-semibold">📍 {{ t.venue }} • {{ t.date }}</div>
              <div class="text-xs font-bold text-muted">Kategori: {{ t.ticketTier }} ({{ t.qty }} Tiket)</div>
            </div>
          </div>
        </section>

        <!-- VIEW 6: MY PROFILE -->
        <section v-if="currentView === 'view-profile'" class="max-w-3xl mx-auto px-4 py-8 space-y-6">
          <h1 class="text-3xl font-black uppercase tracking-tight border-b-2 border-ink pb-4">PROFIL SAYA</h1>
          <div class="nb-card bg-white p-6 space-y-4">
            <div class="flex items-center gap-4 border-b-2 border-ink pb-4">
              <div class="w-16 h-16 bg-accent border-2 border-ink flex items-center justify-center text-2xl font-black">
                <i class="fa-solid fa-user"></i>
              </div>
              <div>
                <h2 class="text-xl font-black uppercase">{{ currentUser?.name || 'USER' }}</h2>
                <p class="text-sm font-medium text-muted">{{ currentUser?.email }}</p>
                <span v-if="currentUser?.role" class="inline-block mt-1 px-2 py-0.5 text-xs font-black uppercase bg-accent border border-ink">
                  Role: {{ currentUser.role }}
                </span>
              </div>
            </div>

            <div class="space-y-3 pt-2">
              <div>
                <label class="block text-xs font-black uppercase mb-1">Nama Lengkap</label>
                <input type="text" :value="currentUser?.name" readonly class="nb-input w-full bg-paper/50">
              </div>
              <div>
                <label class="block text-xs font-black uppercase mb-1">Email</label>
                <input type="email" :value="currentUser?.email" readonly class="nb-input w-full bg-paper/50">
              </div>
              <div>
                <label class="block text-xs font-black uppercase mb-1">Role Akun</label>
                <input type="text" :value="currentUser?.role || 'user'" readonly class="nb-input w-full bg-paper/50 uppercase font-bold">
              </div>
            </div>
          </div>
        </section>

        <!-- VIEW 7: ADMIN DASHBOARD -->
        <section v-if="currentView === 'view-admin'" class="max-w-5xl mx-auto px-4 py-8 space-y-6">
          <div v-if="currentUser?.role === 'admin'" class="space-y-6">
            <h1 class="text-3xl font-black uppercase tracking-tight border-b-2 border-ink pb-4 flex items-center gap-2">
              <i class="fa-solid fa-shield-halved"></i> HALAMAN ADMIN
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="nb-card bg-accent p-4 border-2 border-ink space-y-1">
                <div class="text-xs font-black uppercase">Total Event</div>
                <div class="text-3xl font-black">{{ events.length }}</div>
              </div>
              <div class="nb-card bg-white p-4 border-2 border-ink space-y-1">
                <div class="text-xs font-black uppercase">Role Status</div>
                <div class="text-3xl font-black uppercase text-green-600">ADMIN</div>
              </div>
              <div class="nb-card bg-white p-4 border-2 border-ink space-y-1">
                <div class="text-xs font-black uppercase">Tiket Terjual</div>
                <div class="text-3xl font-black">{{ myTickets.length }}</div>
              </div>
            </div>
            <div class="nb-card bg-white p-6 space-y-4">
              <h3 class="font-black text-lg uppercase border-b-2 border-ink pb-2">Manajemen Platform</h3>
              <p class="text-sm font-medium">Selamat datang di Panel Kontrol Admin Info Musik BDG.</p>
            </div>
          </div>
          <div v-else class="nb-card bg-red-100 p-8 text-center space-y-4 border-2 border-red-500">
            <h2 class="text-2xl font-black text-red-600 uppercase">AKSES DITOLAK</h2>
            <p class="font-bold text-sm">Halaman ini hanya dapat diakses oleh user dengan role Admin.</p>
            <button @click="navigateTo('view-home')" class="nb-btn nb-btn-primary px-4 py-2 text-xs uppercase">Kembali ke Beranda</button>
          </div>
        </section>

        <!-- VIEW 8: PESANAN SAYA -->
        <section v-if="currentView === 'view-pesanan'" class="max-w-6xl mx-auto px-4 py-8 space-y-6">
          <div class="flex justify-between items-center border-b-2 border-ink pb-4">
            <div>
              <div class="text-xs font-bold uppercase tracking-widest text-muted">RIWAYAT TRANSAKSI</div>
              <h1 class="text-3xl font-black uppercase tracking-tight">PESANAN SAYA</h1>
            </div>
            <button @click="getUserOrders" class="nb-btn nb-btn-secondary px-3 py-1 text-xs uppercase flex items-center gap-1">
              <i class="fa-solid fa-rotate"></i> Refresh
            </button>
          </div>

          <div v-if="userOrdersLoading" class="text-center py-12">
            <div class="inline-block w-8 h-8 border-4 border-ink border-t-accent rounded-full animate-spin"></div>
            <p class="font-black text-sm uppercase mt-2">Memuat Data Pesanan...</p>
          </div>

          <div v-else-if="userOrders.length === 0" class="nb-card bg-white p-8 text-center space-y-4">
            <p class="font-bold text-muted uppercase">Belum ada pesanan tiket.</p>
            <button @click="navigateTo('view-events')" class="nb-btn nb-btn-primary px-4 py-2 text-xs uppercase">Beli Tiket Sekarang</button>
          </div>

          <div v-else class="space-y-4">
            <div v-for="order in userOrders" :key="order.id" class="nb-card bg-white p-6 space-y-4">
              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-ink/20 pb-3">
                <div>
                  <span class="text-xs font-bold text-muted uppercase">Kode Pesanan:</span>
                  <span class="font-mono font-black ml-2 text-sm">{{ order.kode_pesanan }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-muted uppercase">Tanggal Pesan:</span>
                  <span class="text-xs font-bold">{{ formatDate(order.created_at) }}</span>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Total Pembayaran:</div>
                  <div class="text-2xl font-black text-ink">Rp {{ formatNumber(order.total_harga) }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <span
                    class="px-3 py-1 text-xs font-black uppercase border border-ink"
                    :class="{
                      'bg-yellow-300': order.status === 'pending',
                      'bg-green-300': order.status === 'dibayar',
                      'bg-red-300': order.status === 'dibatalkan'
                    }"
                  >
                    {{ order.status }}
                  </span>

                  <button @click="openUserOrderDetail(order)" class="nb-btn nb-btn-secondary px-3 py-1.5 text-xs uppercase">
                    Detail Pesanan
                  </button>

                  <button
                    v-if="order.status === 'pending'"
                    @click="payWithMidtrans(order)"
                    class="nb-btn nb-btn-primary px-4 py-1.5 text-xs uppercase"
                  >
                    Bayar Sekarang (Midtrans)
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- MODAL DETAIL PESANAN -->
          <div v-if="showUserOrderDetailModal && selectedUserOrderDetail" class="fixed inset-0 bg-ink/70 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="nb-card bg-paper w-full max-w-2xl p-6 relative space-y-6 my-8">
              <button @click="closeUserOrderDetail" class="absolute top-4 right-4 font-black text-xl hover:bg-accent px-2 border border-ink">✕</button>

              <div class="border-b-2 border-ink pb-3">
                <span class="bg-accent px-2 py-0.5 text-xs font-black border border-ink uppercase">Rincian Transaksi</span>
                <h2 class="text-2xl font-black uppercase mt-1">DETAIL PESANAN #{{ selectedUserOrderDetail.kode_pesanan }}</h2>
              </div>

              <!-- INFORMASI PEMESAN -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white p-4 nb-border">
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Nama Pemesan</div>
                  <div class="font-black text-sm uppercase">{{ selectedUserOrderDetail.user?.name || currentUser?.name }}</div>
                </div>
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Email Pemesan</div>
                  <div class="font-semibold text-sm">{{ selectedUserOrderDetail.user?.email || currentUser?.email }}</div>
                </div>
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Tanggal Pesanan</div>
                  <div class="font-bold text-sm">{{ formatDate(selectedUserOrderDetail.created_at) }}</div>
                </div>
                <div>
                  <div class="text-xs font-bold uppercase text-muted">Status Pembayaran</div>
                  <span
                    class="inline-block px-2 py-0.5 text-xs font-black uppercase border border-ink mt-0.5"
                    :class="{
                      'bg-yellow-300': selectedUserOrderDetail.status === 'pending',
                      'bg-green-300': selectedUserOrderDetail.status === 'dibayar',
                      'bg-red-300': selectedUserOrderDetail.status === 'dibatalkan'
                    }"
                  >
                    {{ selectedUserOrderDetail.status }}
                  </span>
                </div>
                <div v-if="selectedUserOrderDetail.payment?.dibayar_pada">
                  <div class="text-xs font-bold uppercase text-muted">Tanggal Bayar</div>
                  <div class="font-bold text-sm text-green-700">{{ formatDate(selectedUserOrderDetail.payment.dibayar_pada) }}</div>
                </div>
              </div>

              <!-- ITEM PESANAN -->
              <div class="space-y-2">
                <h3 class="font-black text-sm uppercase">Item Tiket Dipesan:</h3>
                <div class="overflow-x-auto">
                  <table class="w-full text-left border-collapse border-2 border-ink bg-white text-xs">
                    <thead class="bg-accent border-b-2 border-ink">
                      <tr>
                        <th class="p-2 border-r border-ink uppercase">Event & Tiket</th>
                        <th class="p-2 border-r border-ink uppercase text-center">Jumlah</th>
                        <th class="p-2 border-r border-ink uppercase text-right">Harga Satuan</th>
                        <th class="p-2 uppercase text-right">Sub Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in selectedUserOrderDetail.order_details" :key="item.id" class="border-b border-ink/20">
                        <td class="p-2 border-r border-ink">
                          <div class="font-black uppercase">{{ item.ticket?.event?.nama_event || 'Event' }}</div>
                          <div class="text-muted font-bold">{{ item.ticket?.nama_tiket || 'Tiket' }}</div>
                        </td>
                        <td class="p-2 border-r border-ink text-center font-bold">{{ item.jumlah }}</td>
                        <td class="p-2 border-r border-ink text-right font-medium">Rp {{ formatNumber(item.harga_satuan) }}</td>
                        <td class="p-2 text-right font-black">Rp {{ formatNumber(item.subtotal) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- TOTAL HARGA -->
              <div class="flex justify-between items-center pt-2 border-t-2 border-ink">
                <span class="font-black text-lg uppercase">TOTAL HARGA:</span>
                <span class="font-black text-2xl text-ink">Rp {{ formatNumber(selectedUserOrderDetail.total_harga) }}</span>
              </div>

              <!-- ACTION BUTTON IN MODAL -->
              <div class="flex justify-end gap-2 pt-2">
                <button @click="closeUserOrderDetail" class="nb-btn nb-btn-secondary px-4 py-2 text-xs uppercase">Tutup</button>
                <button
                  v-if="selectedUserOrderDetail.status === 'pending'"
                  @click="payWithMidtrans(selectedUserOrderDetail); closeUserOrderDetail()"
                  class="nb-btn nb-btn-primary px-4 py-2 text-xs uppercase"
                >
                  Bayar Sekarang (Midtrans)
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- MODAL FORM PEMESANAN TIKET (HOME) -->
        <div v-if="showCheckoutModal && checkoutTicketData" class="fixed inset-0 bg-ink/70 z-50 flex items-center justify-center p-4 overflow-y-auto">
          <div class="nb-card bg-paper w-full max-w-lg p-6 relative space-y-6 my-8">
            <button @click="showCheckoutModal = false" class="absolute top-4 right-4 font-black text-xl hover:bg-accent px-2 border border-ink">✕</button>

            <div class="border-b-2 border-ink pb-3">
              <span class="bg-accent px-2 py-0.5 text-xs font-black border border-ink uppercase">Form Pemesanan Tiket</span>
              <h2 class="text-2xl font-black uppercase mt-1">{{ checkoutEventData?.title || checkoutEventData?.nama_event }}</h2>
              <p class="text-xs font-bold text-muted mt-0.5">Tier Tiket: <span class="text-ink font-black">{{ checkoutTicketData.name || checkoutTicketData.nama_tiket }}</span></p>
            </div>

            <form @submit.prevent="processCheckoutPayment" class="space-y-4">
              <div>
                <label class="block text-xs font-black uppercase mb-1">Nama Pemesan</label>
                <input type="text" v-model="checkoutForm.name" required class="nb-input w-full" placeholder="Nama lengkap pemesan">
              </div>

              <div>
                <label class="block text-xs font-black uppercase mb-1">Email Pemesan</label>
                <input type="email" v-model="checkoutForm.email" required class="nb-input w-full" placeholder="email@domain.com">
              </div>

              <div>
                <label class="block text-xs font-black uppercase mb-1">Jumlah Tiket</label>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    @click="checkoutForm.jumlah = Math.max(1, checkoutForm.jumlah - 1)"
                    class="nb-btn nb-btn-secondary px-3 py-1 text-lg font-black"
                  >-</button>
                  <input
                    type="number"
                    v-model.number="checkoutForm.jumlah"
                    min="1"
                    :max="checkoutTicketData.stock || checkoutTicketData.stok || 100"
                    required
                    class="nb-input text-center font-black text-lg w-24"
                  >
                  <button
                    type="button"
                    @click="checkoutForm.jumlah = Math.min((checkoutTicketData.stock || checkoutTicketData.stok || 100), checkoutForm.jumlah + 1)"
                    class="nb-btn nb-btn-secondary px-3 py-1 text-lg font-black"
                  >+</button>
                  <span v-if="checkoutTicketData.stock || checkoutTicketData.stok" class="text-xs font-bold text-muted ml-2">
                    (Sisa Stok: {{ checkoutTicketData.stock || checkoutTicketData.stok }})
                  </span>
                </div>
              </div>

              <div class="nb-card bg-white p-4 space-y-2 border-2 border-ink">
                <div class="flex justify-between text-xs font-bold text-muted uppercase">
                  <span>Harga Satuan</span>
                  <span>Rp {{ formatNumber(checkoutTicketData.price || checkoutTicketData.harga) }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-muted uppercase">
                  <span>Jumlah Tiket</span>
                  <span>{{ checkoutForm.jumlah }} Tiket</span>
                </div>
                <div class="flex justify-between items-center text-base font-black border-t-2 border-ink pt-2 text-ink">
                  <span>SUB TOTAL</span>
                  <span class="text-xl">Rp {{ formatNumber(checkoutSubtotal) }}</span>
                </div>
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showCheckoutModal = false" class="nb-btn nb-btn-secondary px-4 py-2 text-xs uppercase">Batal</button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="nb-btn nb-btn-primary px-6 py-2.5 text-xs font-black uppercase flex items-center gap-2"
                >
                  <i class="fa-solid fa-credit-card"></i>
                  <span>{{ loading ? 'Memproses...' : 'Lanjut Bayar' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>

      </main>

      <!-- FOOTER -->
      <Footer @navigate="navigateTo" />
    </div>
  </template>

  <script>
  import api from '../utils/api'
  import Navbar from '../components/Navbar.vue'
  import Footer from '../components/Footer.vue'
  import { showFlash } from '../utils/flash'

  export default {
    name: 'HomeView',

    components: {
      Navbar,
      Footer
    },

    data() {
      let savedUser = null
      try {
        const user = localStorage.getItem('user')
        if (user) {
          savedUser = JSON.parse(user)
        }
      } catch (error) {
        console.error('Gagal membaca user dari localStorage:', error)
      }

      return {
        currentView: 'view-home',

        isMobileMenuOpen: false,
        isQuickSearchOpen: false,
        isProfileMenuOpen: false,
        quickSearchQuery: '',

        currentDate: '12 Sep 2026',

        isLoggedIn: !!localStorage.getItem('token'),

        currentUser: savedUser || {
          name: 'Pengunjung BDG',
          email: 'pengunjung@bdg.id',
          role: 'user'
        },

        favorites: [],

        loading: false,
        error: '',

        filters: {
          category: 'ALL',
          location: 'ALL',
          price: 'ALL',
          sort: 'DATE_ASC',
          keyword: ''
        },

        // DATA EVENT DARI BACKEND
        events: [],

        selectedEvent: null,
        selectedTicketTier: null,

        // TIKET DARI BACKEND
        myTickets: [],

        // PESANAN DARI BACKEND
        userOrders: [],
        userOrdersLoading: false,
        selectedUserOrderDetail: null,
        showUserOrderDetailModal: false,

        // MODAL CHECKOUT
        showCheckoutModal: false,
        checkoutEventData: null,
        checkoutTicketData: null,
        checkoutForm: {
          name: '',
          email: '',
          jumlah: 1
        },

        loginForm: {
          email: '',
          password: ''
        }
      }
    },

    computed: {
      checkoutSubtotal() {
        if (!this.checkoutTicketData) return 0
        const unitPrice = Number(this.checkoutTicketData.price || this.checkoutTicketData.harga) || 0
        const qty = Number(this.checkoutForm.jumlah) || 1
        return unitPrice * qty
      },

      filteredEvents() {
        let result = this.events.filter(event => {
          const keyword = this.filters.keyword.toLowerCase()

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
            matchPrice = Number(event.minPrice) < 100000
          }

          if (this.filters.price === '100-200') {
            matchPrice =
              Number(event.minPrice) >= 100000 &&
              Number(event.minPrice) <= 200000
          }

          if (this.filters.price === 'ABOVE200') {
            matchPrice = Number(event.minPrice) > 200000
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
              Number(a.minPrice) - Number(b.minPrice)
          )
        }

        if (this.filters.sort === 'PRICE_DESC') {
          result.sort(
            (a, b) =>
              Number(b.minPrice) - Number(a.minPrice)
          )
        }

        return result
      }
    },

    async mounted() {
      // Load user dari localStorage
      this.loadUser()

      // Ambil event dari backend ketika website dibuka
      await this.getEvents()

      // Cek apakah user sudah login
      if (this.isLoggedIn) {
        await this.getProfile()
        await this.getMyTickets()
        await this.getUserOrders()
      }

      document.addEventListener('click', this.handleDocumentClick)
    },

    beforeUnmount() {
      document.removeEventListener('click', this.handleDocumentClick)
    },

    methods: {
      // ==========================================
      // LOAD USER
      // ==========================================

      loadUser() {
        const token = localStorage.getItem('token')
        const savedUser = localStorage.getItem('user')

        this.isLoggedIn = !!token

        if (!savedUser) {
          this.currentUser = null
          return
        }

        try {
          this.currentUser = JSON.parse(savedUser)
        } catch (error) {
          console.error('User localStorage tidak valid:', error)
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
      // GET EVENT
      // GET /api/events
      // ==========================================

async getEvents() {
  this.loading = true
  this.error = ''

  try {
    const response = await api.get('/events')

    console.log('Response events:', response.data)

    const data = response.data.data || []

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
        event.category?.nama_kategori || 'Event',

      organizer:
        event.organizer?.name ||
        event.organizer?.nama ||
        'Info Musik BDG',

      minPrice:
        event.tickets?.length
          ? Math.min(
              ...event.tickets.map(ticket =>
                Number(ticket.harga)
              )
            )
          : 0,

      tickets: (event.tickets || []).map(ticket => ({
        id: ticket.id,
        name: ticket.nama_tiket,
        price: Number(ticket.harga),
        stock: ticket.stok,
        desc: `Tersedia ${ticket.stok} tiket`
      }))
    }))

    console.log('Event setelah mapping:', this.events)

  } catch (error) {
    console.error('Gagal mengambil event:', error)

    this.error =
      error.response?.data?.message ||
      'Gagal mengambil data event.'

    this.events = []
  } finally {
    this.loading = false
  }
},


      // ==========================================
      // GET DETAIL EVENT
      // GET /api/events/{id}
      // ==========================================

      openEventDetail(eventId) {
        if (eventId) {
          this.$router.push(`/event/${eventId}`)
        }
      },


      // ==========================================
      // GET PROFILE
      // GET /api/profile
      // ==========================================

      async getProfile() {
        try {
          const response = await api.get('/profile')

          console.log(
            'Response profile:',
            response.data
          )

          const user =
            response.data.data ||
            response.data.user ||
            response.data

          if (user) {
            this.currentUser = {
              id: user.id,
              name: user.name || user.nama || 'Pengunjung BDG',
              email: user.email || '',
              role: user.role || 'user'
            }

            localStorage.setItem('user', JSON.stringify(this.currentUser))
            this.isLoggedIn = true
          }

        } catch (error) {
          console.error(
            'Gagal mengambil profile:',
            error
          )

          if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            this.isLoggedIn = false
            this.currentUser = null
          }
        }
      },


      // ==========================================
      // LOGIN
      // POST /api/login
      // ==========================================

      async handleLogin() {
  this.loading = true

  try {
    const response = await api.post('/login', {
      email: this.loginForm.email,
      password: this.loginForm.password
    })

    console.log('Response login:', response.data)

    // Token dari AuthController Laravel
    const token = response.data.data?.token

    if (!token) {
      showFlash('Token tidak ditemukan.', 'error', 'LOGIN GAGAL!')
      return
    }

    // Simpan token
    localStorage.setItem('token', token)

    // Simpan status login
    this.isLoggedIn = true

    // Ambil user dari response Laravel
    const user = response.data.data?.user

    if (user) {
      this.currentUser = {
        id: user.id,
        name: user.name || 'Pengunjung BDG',
        email: user.email || '',
        role: user.role || 'user'
      }

      localStorage.setItem('user', JSON.stringify(this.currentUser))
    }

    // Bersihkan password
    this.loginForm.password = ''

    showFlash(`Selamat datang kembali, ${this.currentUser.name}!`, 'success', 'LOGIN BERHASIL!')

    // Pindah ke halaman utama
    this.navigateTo('view-home')

    // Ambil tiket user
    await this.getMyTickets()

  } catch (error) {
    console.error('Login gagal:', error)

    showFlash(
      error.response?.data?.message || 'Email atau password salah.',
      'error',
      'LOGIN GAGAL!'
    )
  } finally {
    this.loading = false
  }
},


      // ==========================================
      // LOGOUT
      // POST /api/logout
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
          // Hapus token & user walaupun request logout gagal
          localStorage.removeItem('token')
          localStorage.removeItem('user')

          this.isLoggedIn = false
          this.currentUser = null
          this.myTickets = []
          this.isProfileMenuOpen = false
          this.isMobileMenuOpen = false

          this.navigateTo('view-home')
        }
      },


      // ==========================================
      // GET TIKET SAYA
      // GET /api/my-tickets
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
      // PESANAN USER & MIDTRANS
      // ==========================================

      async getUserOrders() {
        if (!this.isLoggedIn) return
        this.userOrdersLoading = true
        try {
          const userId = this.currentUser?.id
          const response = await api.get('/orders', {
            params: userId ? { user_id: userId } : {}
          })
          this.userOrders = response.data.data || []
        } catch (error) {
          console.error('Gagal mengambil data pesanan:', error)
          this.userOrders = []
        } finally {
          this.userOrdersLoading = false
        }
      },

      openUserOrderDetail(order) {
        this.selectedUserOrderDetail = order
        this.showUserOrderDetailModal = true
      },

      closeUserOrderDetail() {
        this.showUserOrderDetailModal = false
        this.selectedUserOrderDetail = null
      },

      async payWithMidtrans(order) {
        try {
          const response = await api.post('/payments/snap-token', {
            order_id: order.id
          })

          const snapToken = response.data.snap_token

          if (!snapToken) {
            showFlash('Gagal mendapatkan token pembayaran Midtrans.', 'error', 'PEMBAYARAN GAGAL')
            return
          }

          if (window.snap) {
            window.snap.pay(snapToken, {
              onSuccess: async (result) => {
                console.log('Payment success:', result)
                await api.post('/payments/finish', {
                  order_id: order.id,
                  status: 'berhasil',
                  metode_pembayaran: result.payment_type || 'Midtrans'
                })
                showFlash('Pembayaran Anda telah berhasil diproses!', 'success', 'PEMBAYARAN BERHASIL')
                await this.getUserOrders()
                await this.getMyTickets()
              },
              onPending: async (result) => {
                console.log('Payment pending:', result)
                showFlash('Pembayaran pending, silakan selesaikan pembayaran Anda.', 'warning', 'PEMBAYARAN PENDING')
                await this.getUserOrders()
              },
              onError: async (result) => {
                console.error('Payment error:', result)
                showFlash('Pembayaran gagal atau dibatalkan.', 'error', 'PEMBAYARAN GAGAL')
                await this.getUserOrders()
              },
              onClose: async () => {
                console.log('Snap modal closed')
                await this.getUserOrders()
              }
            })
          } else {
            showFlash('SDK Midtrans belum dimuat.', 'error', 'MIDTRANS ERROR')
          }
        } catch (error) {
          console.error('Gagal memproses Midtrans:', error)
          showFlash(error.response?.data?.message || 'Gagal memproses pembayaran Midtrans.', 'error', 'PEMBAYARAN GAGAL')
        }
      },

      formatDate(dateStr) {
        if (!dateStr) return '-'
        try {
          const d = new Date(dateStr)
          return d.toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          })
        } catch (e) {
          return dateStr
        }
      },

      openCheckoutModal(event, ticket) {
        if (!this.isLoggedIn) {
          showFlash('Silakan login terlebih dahulu untuk membeli tiket.', 'warning', 'LOGIN DIPERLUKAN')
          this.navigateTo('view-login')
          return
        }

        this.checkoutEventData = event
        this.checkoutTicketData = ticket
        this.checkoutForm = {
          name: this.currentUser?.name || '',
          email: this.currentUser?.email || '',
          jumlah: 1
        }
        this.showCheckoutModal = true
      },

      async processCheckoutPayment() {
        if (!this.checkoutForm.name || !this.checkoutForm.email) {
          showFlash('Nama dan email pemesan wajib diisi.', 'warning', 'DATA BELUM LENGKAP')
          return
        }

        const stokAvailable = this.checkoutTicketData?.stock || this.checkoutTicketData?.stok
        if (stokAvailable && this.checkoutForm.jumlah > stokAvailable) {
          showFlash(`Stok tiket tidak mencukupi. Sisa stok: ${stokAvailable}`, 'warning', 'STOK TIDAK CUKUP')
          return
        }

        this.loading = true

        try {
          const savedUser = localStorage.getItem('user')
          let userId = this.currentUser?.id
          if (!userId && savedUser) {
            try {
              userId = JSON.parse(savedUser).id
            } catch (e) {}
          }

          const response = await api.post('/orders', {
            user_id: userId,
            ticket_id: this.checkoutTicketData.id,
            jumlah: this.checkoutForm.jumlah
          })

          console.log('Response order:', response.data)
          const newOrder = response.data.data

          this.showCheckoutModal = false
          showFlash(`Berhasil memesan ${this.checkoutForm.jumlah} tiket ${this.checkoutTicketData.name || this.checkoutTicketData.nama_tiket}!`, 'success', 'PESANAN BERHASIL')

          await this.getUserOrders()
          await this.getMyTickets()

          this.navigateTo('view-pesanan')

          if (newOrder) {
            this.payWithMidtrans(newOrder)
          }

        } catch (error) {
          console.error('Gagal memesan tiket:', error)
          showFlash(
            error.response?.data?.message || 'Gagal melakukan pemesanan tiket.',
            'error',
            'PEMESANAN GAGAL'
          )
        } finally {
          this.loading = false
        }
      },

      // ==========================================
      // BELI / PESAN TIKET
      // POST /api/orders
      // ==========================================

      async startCheckout(event, ticket) {
        this.openCheckoutModal(event, ticket)
      },


      // ==========================================
      // NAVIGASI
      // ==========================================

      navigateTo(viewName) {
        this.currentView = viewName

        this.isMobileMenuOpen = false
        this.isProfileMenuOpen = false

        if (viewName === 'view-pesanan') {
          this.getUserOrders()
        }

        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        })
      },


      // ==========================================
      // FORMAT HARGA
      // ==========================================

      formatNumber(val) {
        return new Intl.NumberFormat('id-ID').format(
          Number(val) || 0
        )
      },


      // ==========================================
      // FAVORIT
      // ==========================================

      toggleFavorite(eventId) {
        const idx =
          this.favorites.indexOf(eventId)

        if (idx > -1) {
          this.favorites.splice(idx, 1)
        } else {
          this.favorites.push(eventId)
        }
      },


      // ==========================================
      // FILTER KATEGORI
      // ==========================================

      filterCategoryQuick(catName) {
        this.filters.category = catName

        this.navigateTo('view-events')
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
      // SEARCH
      // ==========================================

      executeQuickSearch() {
        this.filters.keyword =
          this.quickSearchQuery

        this.isQuickSearchOpen = false

        this.navigateTo('view-events')
      },


      quickFilterTag(tag) {
        this.filters.keyword = tag

        this.isQuickSearchOpen = false

        this.navigateTo('view-events')
      },


      // ==========================================
      // E-TICKET
      // ==========================================

      showETicketModal(ticket) {
        showFlash(
          `Kode: ${ticket.ticketCode || ticket.kode_tiket} • Atas Nama: ${this.currentUser.name}`,
          'success',
          'E-TICKET VALID'
        )
      }
    }
  }
  </script>

  <style>
  /* Font & Utility Styling Import (Sama persis dengan Tailwind Config HTML Anda) */
  @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap');

  body {
    background-color: #F4F1E8;
    color: #111111;
    font-family: 'Space Grotesk', sans-serif;
    -webkit-font-smoothing: antialiased;
  }

  /* Neo-Brutalism Classes */
  .nb-border { border: 2px solid #111111; }
  .nb-border-b { border-bottom: 2px solid #111111; }
  .nb-border-t { border-top: 2px solid #111111; }

  .nb-card {
    background-color: #F4F1E8;
    border: 2px solid #111111;
    box-shadow: 4px 4px 0px 0px #111111;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
  }

  .nb-card-hover:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px 0px #111111;
  }

  .nb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    border: 2px solid #111111;
    box-shadow: 3px 3px 0px 0px #111111;
    transition: all 0.1s ease;
    cursor: pointer;
    user-select: none;
  }
  .nb-btn:active {
    transform: translate(2px, 2px);
    box-shadow: 1px 1px 0px 0px #111111;
  }

  .nb-btn-primary { background-color: #F2C94C; color: #111111; }
  .nb-btn-primary:hover { background-color: #e5be42; }

  .nb-btn-secondary { background-color: #FFFFFF; color: #111111; }
  .nb-btn-secondary:hover { background-color: #D9D5CA; }

  .nb-btn-dark { background-color: #111111; color: #F4F1E8; }
  .nb-btn-dark:hover { background-color: #222222; }

  .nb-input {
    border: 2px solid #111111;
    background-color: #FFFFFF;
    color: #111111;
    padding: 0.6rem 0.8rem;
    outline: none;
    font-weight: 500;
  }
  .nb-input:focus {
    background-color: #FFFDF7;
    box-shadow: 3px 3px 0px 0px #111111;
  }

  .nb-badge {
    border: 1px solid #111111;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  </style>