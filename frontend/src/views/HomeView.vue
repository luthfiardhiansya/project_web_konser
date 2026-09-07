<template>
  <div class="min-h-screen flex flex-col justify-between selection:bg-accent selection:text-ink">
    <!-- TOP ANNOUNCEMENT BAR -->
    <div class="bg-ink text-paper py-1.5 px-4 text-xs font-bold uppercase tracking-wider flex justify-between items-center nb-border-b">
      <span>📍 BANDUNG MUSIC HUB — Konser, Gigs & Festival Terkini</span>
      <div class="hidden sm:flex items-center space-x-4 text-[11px]">
        <button @click="navigateTo('view-organizer-reg')" class="hover:underline text-accent">Daftar Jadi Organizer</button>
        <span>|</span>
        <span>Hari ini: <span>{{ currentDate }}</span></span>
      </div>
    </div>

    <!-- HEADER & NAVBAR -->
    <header class="sticky top-0 z-40 bg-paper nb-border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- LOGO -->
        <button @click="navigateTo('view-home')" class="flex items-center space-x-2 group text-left">
          <div class="bg-ink text-paper font-black px-2 py-1 text-xl tracking-tighter nb-border group-hover:bg-accent group-hover:text-ink transition-colors">
            INFO
          </div>
          <div class="font-black text-xl tracking-tighter leading-tight">
            MUSIK<br><span class="text-xs bg-accent px-1 border border-ink tracking-widest uppercase">BDG</span>
          </div>
        </button>

        <!-- DESKTOP NAV LINKS -->
        <nav class="hidden md:flex items-center space-x-6 text-sm font-bold uppercase tracking-tight">
          <button @click="navigateTo('view-home')" :class="{ 'bg-accent border-ink': currentView === 'view-home' }" class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink">Beranda</button>
          <button @click="navigateTo('view-events')" :class="{ 'bg-accent border-ink': currentView === 'view-events' }" class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink">Event</button>
          <button @click="filterCategoryQuick('Indie')" class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink">Gigs</button>
          <button @click="navigateTo('view-community')" :class="{ 'bg-accent border-ink': currentView === 'view-community' }" class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink">Komunitas</button>
          <button @click="navigateTo('view-organizer-reg')" :class="{ 'bg-accent border-ink': currentView === 'view-organizer-reg' }" class="hover:bg-accent px-2 py-1 transition-colors border-b-2 border-transparent hover:border-ink">Organizer</button>
        </nav>

        <!-- RIGHT NAV ACTIONS -->
        <div class="hidden md:flex items-center space-x-3">
          <!-- Search Quick Modal Button -->
          <button @click="isQuickSearchOpen = true" class="p-2 nb-btn nb-btn-secondary" title="Cari Event">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </button>

          <!-- Favorites Button -->
          <button @click="navigateTo('view-favorites')" class="p-2 nb-btn nb-btn-secondary relative" title="Favorit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span v-if="favorites.length > 0" class="absolute -top-1 -right-1 bg-accent text-ink text-[10px] font-black w-4 h-4 flex items-center justify-center nb-border">
              {{ favorites.length }}
            </span>
          </button>

          <!-- User Auth States -->
          <div class="flex items-center space-x-2">
            <template v-if="!isLoggedIn">
              <button @click="navigateTo('view-login')" class="px-3 py-1.5 text-xs uppercase nb-btn nb-btn-secondary">Masuk</button>
              <button @click="navigateTo('view-signup')" class="px-3 py-1.5 text-xs uppercase nb-btn nb-btn-primary">Daftar</button>
            </template>
            <template v-else>
              <button @click="navigateTo('view-my-tickets')" class="px-3 py-1.5 text-xs uppercase nb-btn nb-btn-secondary">Tiket Saya</button>
              <button @click="logout" class="px-3 py-1.5 text-xs uppercase nb-btn nb-btn-dark">Keluar</button>
            </template>
          </div>
        </div>

        <!-- MOBILE HAMBURGER BUTTON -->
        <div class="flex md:hidden items-center space-x-2">
          <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 nb-btn nb-btn-secondary">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
        </div>

      </div>

      <!-- MOBILE MENU PANEL -->
      <div v-if="isMobileMenuOpen" class="md:hidden bg-paper nb-border-b px-4 py-4 space-y-3">
        <div class="flex flex-col space-y-2 text-sm font-bold uppercase">
          <button @click="navigateTo('view-home'); isMobileMenuOpen = false" class="p-2 text-left hover:bg-accent border border-ink">Beranda</button>
          <button @click="navigateTo('view-events'); isMobileMenuOpen = false" class="p-2 text-left hover:bg-accent border border-ink">Semua Event</button>
          <button @click="navigateTo('view-my-tickets'); isMobileMenuOpen = false" class="p-2 text-left hover:bg-accent border border-ink">Tiket Saya</button>
          <button @click="navigateTo('view-favorites'); isMobileMenuOpen = false" class="p-2 text-left hover:bg-accent border border-ink">Event Favorit ({{ favorites.length }})</button>
          <button @click="navigateTo('view-community'); isMobileMenuOpen = false" class="p-2 text-left hover:bg-accent border border-ink">Komunitas BDG</button>
          <button @click="navigateTo('view-organizer-reg'); isMobileMenuOpen = false" class="p-2 text-left bg-beige hover:bg-accent border border-ink">Dashboard Organizer</button>
        </div>
        <div class="pt-2 border-t border-ink flex space-x-2">
          <template v-if="!isLoggedIn">
            <button @click="navigateTo('view-login'); isMobileMenuOpen = false" class="flex-1 py-2 text-xs uppercase nb-btn nb-btn-secondary">Masuk</button>
            <button @click="navigateTo('view-signup'); isMobileMenuOpen = false" class="flex-1 py-2 text-xs uppercase nb-btn nb-btn-primary">Daftar</button>
          </template>
          <template v-else>
            <button @click="logout(); isMobileMenuOpen = false" class="w-full py-2 text-xs uppercase nb-btn nb-btn-dark">Keluar ({{ currentUser.name }})</button>
          </template>
        </div>
      </div>
    </header>

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
                <img src="https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=800&q=80" alt="Bandung Live Music Scene" class="w-full h-64 sm:h-80 object-cover nb-border">
                <div class="mt-3 flex justify-between items-center">
                  <div>
                    <div class="font-black text-base uppercase">BANDUNG INDIE NIGHT 2026</div>
                    <div class="text-xs font-semibold text-muted">📍 Gudang Selatan, Bandung</div>
                  </div>
                  <button @click="openEventDetail('event-1')" class="nb-btn nb-btn-primary text-xs px-3 py-1.5 uppercase">Beli Tiket</button>
                </div>
              </div>

              <div class="absolute -bottom-6 -left-6 z-20 hidden sm:block nb-card bg-accent p-3 max-w-xs -rotate-3">
                <div class="text-xs font-black uppercase">🎙️ UPCOMING VENUE Spotlight</div>
                <div class="text-sm font-bold">LASWI HERITAGE BANDUNG</div>
                <p class="text-[11px] text-ink/80 font-medium">3 event besar bulan ini. Siapkan energimu!</p>
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
                <button @click="startCheckout(selectedEvent, ticket)" class="nb-btn nb-btn-primary w-full py-1.5 text-xs uppercase mt-2">
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
            <button @click="showETicketModal(t)" class="nb-btn nb-btn-dark w-full py-2 text-xs uppercase">Tampilkan QR Code / E-Ticket</button>
          </div>
        </div>
      </section>

    </main>

    <!-- FOOTER -->
    <footer class="bg-ink text-paper nb-border-t py-8 px-4 sm:px-6 lg:px-8 mt-12">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
        <div>
          <div class="font-black text-xl tracking-tighter">INFO MUSIK BDG</div>
          <p class="text-xs text-paper/70 font-medium">Temukan Musikmu di Bandung. Platform ticketing & direktori gigs lokal.</p>
        </div>
        <div class="text-xs font-bold uppercase space-x-4">
          <button @click="navigateTo('view-home')" class="hover:underline">Beranda</button>
          <button @click="navigateTo('view-events')" class="hover:underline">Event</button>
          <button @click="navigateTo('view-organizer-reg')" class="hover:underline">Organizer</button>
        </div>
        <div class="text-[11px] text-paper/50 font-mono">
          © 2026 INFO MUSIK BDG. ALL RIGHTS RESERVED.
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      currentView: 'view-home',
      isMobileMenuOpen: false,
      isQuickSearchOpen: false,
      quickSearchQuery: '',
      currentDate: '12 Sep 2026',
      isLoggedIn: false,
      currentUser: { name: 'Pengunjung BDG', email: 'pengunjung@bdg.id' },
      favorites: [],
      
      // Filters
      filters: {
        category: 'ALL',
        location: 'ALL',
        price: 'ALL',
        sort: 'DATE_ASC',
        keyword: ''
      },

      // Mock Datasets
      events: [
        {
          id: 'event-1',
          title: 'BANDUNG INDIE NIGHT 2026',
          category: 'Indie',
          organizer: 'Kolektif Musikal BDG',
          date: '12 Sep 2026',
          time: '19:00 WIB',
          venue: 'Gudang Selatan',
          location: 'Bandung Tengah',
          minPrice: 75000,
          image: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=800&q=80',
          description: 'Malam perayaan rilisan fisik dan pertunjukan musik indie pop dan rock lokal terbaik di Gudang Selatan.',
          tickets: [
            { id: 't1', name: 'EARLY BIRD', price: 75000, desc: 'Akses Reguler, Kuota Terbatas' },
            { id: 't2', name: 'PRESALE 1', price: 100000, desc: 'Akses Reguler Show' }
          ]
        },
        {
          id: 'event-2',
          title: 'LASWI SOUND FESTIVAL',
          category: 'Festival',
          organizer: 'Laswi Event Pro',
          date: '20 Sep 2026',
          time: '15:00 WIB',
          venue: 'Laswi Heritage',
          location: 'Bandung Selatan',
          minPrice: 150000,
          image: 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=800&q=80',
          description: 'Festival musik outdoor lintas genre terbesar bulan ini dengan 3 panggung utama.',
          tickets: [
            { id: 't3', name: 'REGULAR DAY PASS', price: 150000, desc: 'Akses Semua Stage' },
            { id: 't4', name: 'VIP EXPRESS PASS', price: 300000, desc: 'Akses Fastlane & VIP Lounge' }
          ]
        },
        {
          id: 'event-3',
          title: 'JAZZ DI CIUMBULEUIT',
          category: 'Jazz',
          organizer: 'Dago Jazz Forum',
          date: '28 Sep 2026',
          time: '18:30 WIB',
          venue: 'Ciumbuleuit Open Stage',
          location: 'Bandung Utara',
          minPrice: 120000,
          image: 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?auto=format&fit=crop&w=800&q=80',
          description: 'Pertunjukan musik jazz akustik dengan latar pemandangan malam bukit Bandung Utara.',
          tickets: [
            { id: 't5', name: 'PASS SEAT A', price: 120000, desc: 'Tempat Duduk Terdepan' }
          ]
        }
      ],

      // App Active State
      selectedEvent: null,
      selectedTicketTier: null,
      myTickets: [],
      loginForm: { email: 'pengunjung@bdg.id', password: '' }
    }
  },
  computed: {
    filteredEvents() {
      return this.events.filter(e => {
        const matchCat = this.filters.category === 'ALL' || e.category === this.filters.category;
        const matchLoc = this.filters.location === 'ALL' || e.location === this.filters.location;
        const matchKey = !this.filters.keyword || 
                         e.title.toLowerCase().includes(this.filters.keyword.toLowerCase()) || 
                         e.venue.toLowerCase().includes(this.filters.keyword.toLowerCase());
        return matchCat && matchLoc && matchKey;
      });
    }
  },
  methods: {
    navigateTo(viewName) {
      this.currentView = viewName;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    formatNumber(val) {
      return new Intl.NumberFormat('id-ID').format(val);
    },
    toggleFavorite(eventId) {
      const idx = this.favorites.indexOf(eventId);
      if (idx > -1) {
        this.favorites.splice(idx, 1);
      } else {
        this.favorites.push(eventId);
      }
    },
    openEventDetail(eventId) {
      this.selectedEvent = this.events.find(e => e.id === eventId);
      this.currentView = 'view-detail';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    filterCategoryQuick(catName) {
      this.filters.category = catName;
      this.navigateTo('view-events');
    },
    resetFilters() {
      this.filters = { category: 'ALL', location: 'ALL', price: 'ALL', sort: 'DATE_ASC', keyword: '' };
    },
    executeQuickSearch() {
      this.filters.keyword = this.quickSearchQuery;
      this.isQuickSearchOpen = false;
      this.navigateTo('view-events');
    },
    quickFilterTag(tag) {
      this.filters.keyword = tag;
      this.isQuickSearchOpen = false;
      this.navigateTo('view-events');
    },
    startCheckout(event, ticket) {
      if (!this.isLoggedIn) {
        alert('Silakan login terlebih dahulu untuk membeli tiket.');
        this.navigateTo('view-login');
        return;
      }
      // Simple instant mock ticket checkout
      const newTicket = {
        ticketCode: 'IMBDG-2026-' + Math.floor(100000 + Math.random() * 900000),
        eventTitle: event.title,
        venue: event.venue,
        date: event.date,
        ticketTier: ticket.name,
        qty: 1
      };
      this.myTickets.push(newTicket);
      alert(`Berhasil memesan tiket ${ticket.name} untuk ${event.title}!`);
      this.navigateTo('view-my-tickets');
    },
    handleLogin() {
      this.isLoggedIn = true;
      this.currentUser.name = 'Budi Nugraha';
      this.navigateTo('view-home');
    },
    logout() {
      this.isLoggedIn = false;
      this.navigateTo('view-home');
    },
    showETicketModal(ticket) {
      alert(`E-TICKET VALID\nKode: ${ticket.ticketCode}\nEvent: ${ticket.eventTitle}\nAtas Nama: ${this.currentUser.name}`);
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