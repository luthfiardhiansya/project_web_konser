  <template>
    <div :class="['min-h-screen flex flex-col justify-between selection:bg-accent selection:text-ink hero-page', { 'hero-navbar-scrolled': isHeroNavbarScrolled }]">

      <!-- HEADER & NAVBAR -->
      <div :class="['navbar-home-layer', { 'navbar-scrolled': isHeroNavbarScrolled }]">
        <Navbar
          :currentView="currentView"
          :favoritesCount="favorites.length"
          @navigate="navigateTo"
          @open-search="isQuickSearchOpen = true"
          @filter-category="filterCategoryQuick"
        />
      </div>

      <!-- QUICK SEARCH OVERLAY -->
      <div v-if="isQuickSearchOpen" class="fixed inset-0 bg-ink/70 z-50 flex items-start justify-center pt-20 px-4">
        <div class="nb-card w-full max-w-2xl p-6 relative">
          <button @click="isQuickSearchOpen = false" class="absolute top-4 right-4 font-black text-xl hover:bg-accent px-2 border border-ink">✕</button>
          <h3 class="font-black text-xl mb-4 uppercase">CARI EVENT MUSIK BANDUNG</h3>
          <div class="flex gap-2 mb-4">
            <input type="text" v-model="quickSearchQuery" @keyup.enter="executeQuickSearch" placeholder="Ketik nama band, venue, atau festival..." class="nb-input flex-1">
            <button @click="executeQuickSearch" class="nb-btn nb-btn-primary px-6">CARI</button>
          </div>
        </div>
      </div>

      <!-- MAIN CONTAINER (ROUTING VIEW SIMULATION) -->
      <main class="flex-grow">
        
        <!-- VIEW 1: HOMEPAGE -->
        <section v-if="currentView === 'view-home'" class="home-page">

          <!-- =====================================================
               HERO FULLSCREEN VIDEO
               VIDEO: frontend/src/assets/videos/concert-hero.mp4
               ===================================================== -->
          <section class="hero-video-section" aria-label="InfoMusikBDG hero">
            <video class="hero-video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
              <source src="/src/assets/videos/concert-hero.mp4" type="video/mp4">
            </video>

            <div class="hero-video-overlay"></div>
            <div class="hero-grid-overlay" aria-hidden="true"></div>

            <div class="hero-content">

              <h1 class="hero-title">
                TEMUKAN EVENT MUSIK DI 
                <span>InfoMusikBDG</span>
              </h1>

            </div>

            <div class="hero-bottom">
              <div>LIVE MUSIC / BANDUNG</div>
              <div class="hero-scroll">SCROLL TO EXPLORE ↓</div>
              <div class="hero-index">  HOME</div>
            </div>
          </section>

          <!-- =====================================================
               HOME CONTENT — GRID BACKGROUND
               ===================================================== -->
          <div class="home-content-grid">

            <!-- QUICK STATS -->
            <section class="home-section home-stats-section">
              <div class="home-container stats-grid">
                <div class="stat-box">
                  <strong>{{ events.length }}</strong>
                  <span>EVENT TERSEDIA</span>
                </div>
                <div class="stat-box">
                  <strong>{{ categories.length || '—' }}</strong>
                  <span>KATEGORI</span>
                </div>
                <div class="stat-box">
                  <strong>BDG</strong>
                  <span>MUSIC CITY</span>
                </div>
                <div class="stat-box">
                  <strong>LIVE</strong>
                  <span>EVENT UPDATE</span>
                </div>
              </div>
            </section>  

            <!-- EVENT TERPOPULER -->
            <section class="home-section">
              <div class="home-container">
                <div class="section-heading-row">
                  <div>
                    <h2>EVENT TERPOPULER</h2>
                    <p>Event yang sedang menarik perhatian di InfoMusikBDG.</p>
                  </div>
                  <button @click="navigateTo('view-events')" class="nb-btn nb-btn-secondary section-link">
                    SEMUA EVENT →
                  </button>
                </div>

                <div v-if="popularEvents.length" class="popular-event-grid">
                  <article
                    v-for="(event, index) in popularEvents"
                    :key="`popular-${event.id}`"
                    class="popular-event-card"
                    :class="{ 'popular-event-card-featured': index === 0 }"
                  >
                    <div class="event-card-image-wrap">
                      <img :src="event.image" :alt="event.title" class="event-card-image">
                      <span class="event-number">0{{ index + 1 }}</span>
                      <button
                        @click="toggleFavorite(event)"
                        :data-favorite-id="event.id"
                        class="event-favorite"
                        aria-label="Tambah ke favorit"
                      >
                        {{ favorites.includes(event.id) ? '♥' : '♡' }}
                      </button>
                    </div>
                    <div class="event-card-body">
                      <span class="event-category">{{ event.category }}</span>
                      <h3>{{ event.title }}</h3>
                      <p>{{ event.date }} · {{ event.time }}</p>
                      <p>{{ event.venue }}</p>
                      <div class="event-card-footer">
                        <strong>Rp {{ formatNumber(event.minPrice) }}</strong>
                        <button @click="openEventDetail(event.id)">DETAIL →</button>
                      </div>
                    </div>
                  </article>
                </div>

                <div v-else class="empty-home-card">
                  EVENT BELUM TERSEDIA
                </div>
              </div>
            </section>

            <!-- KATEGORI -->
            <section class="home-section category-section">
              <div class="home-container">
                <div class="section-heading-row">
                  <div>
                    <div class="section-eyebrow">PILIH SUASANA</div>
                    <h2>JELAJAHI BERDASARKAN GENRE</h2>
                  </div>
                </div>

                <div class="genre-grid">
                  <button @click="filterCategoryQuick('Festival')" class="genre-card">
                    <strong>FESTIVAL</strong>
                    <small>BIG STAGE / CROWD / ALL DAY</small>
                    <span class="genre-count">{{ categoryEventCounts['Festival'] || 0 }} EVENT</span>
                  </button>
                  <button @click="filterCategoryQuick('Gigs')" class="genre-card">
                    <strong>GIGS</strong>
                    <small>SMALL VENUE / LIVE SESSION</small>
                    <span class="genre-count">{{ categoryEventCounts['Gigs'] || 0 }} EVENT</span>
                  </button>
                  <button @click="filterCategoryQuick('Indie')" class="genre-card">
                    <strong>INDIE</strong>
                    <small>LOCAL SOUND / NEW WAVE</small>
                    <span class="genre-count">{{ categoryEventCounts['Indie'] || 0 }} EVENT</span>
                  </button>
                  <button @click="filterCategoryQuick('Rock')" class="genre-card">
                    <strong>ROCK</strong>
                    <small>LOUD / FAST / LIVE</small>
                    <span class="genre-count">{{ categoryEventCounts['Rock'] || 0 }} EVENT</span>
                  </button>
                  <button @click="filterCategoryQuick('Jazz')" class="genre-card">
                    <strong>JAZZ</strong>
                    <small>SOUL / GROOVE / NIGHT</small>
                    <span class="genre-count">{{ categoryEventCounts['Jazz'] || 0 }} EVENT</span>
                  </button>
                  <button @click="quickFilterTag('')" class="genre-card genre-yellow">
                    <strong>SEMUA EVENT</strong>
                    <small>EXPLORE FULL CATALOG →</small>
                    <span class="genre-count">{{ events.length }} EVENT</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- EVENT TERDEKAT -->
            <section class="home-section upcoming-section">
              <div class="home-container">
                <div class="section-heading-row">
                  <div>
                    <div class="section-eyebrow">AGENDA TERKINI</div>
                    <h2>EVENT JADWAL TERDEKAT</h2>
                    <p>Jangan sampai kelewatan jadwal pertunjukan berikutnya.</p>
                  </div>
                  <button @click="navigateTo('view-events')" class="nb-btn nb-btn-secondary section-link">
                    LIHAT SEMUA →
                  </button>
                </div>

                <div v-if="upcomingEvents.length" class="upcoming-list">
                  <article v-for="event in upcomingEvents" :key="`upcoming-${event.id}`" class="upcoming-row">
                    <div class="upcoming-date">
                      <span>{{ event.date ? new Date(event.date).toLocaleDateString('id-ID', { day: '2-digit' }) : '--' }}</span>
                      <small>{{ event.date ? new Date(event.date).toLocaleDateString('id-ID', { month: 'short' }).toUpperCase() : 'DATE' }}</small>
                    </div>
                    <div class="upcoming-main">
                      <span>{{ event.category }}</span>
                      <h3>{{ event.title }}</h3>
                      <p>{{ event.venue }} · {{ event.time }}</p>
                    </div>
                    <div class="upcoming-price">
                      <small>MULAI</small>
                      <strong>Rp {{ formatNumber(event.minPrice) }}</strong>
                    </div>
                    <button @click="openEventDetail(event.id)" class="upcoming-arrow">→</button>
                  </article>
                </div>

                <div v-else class="empty-home-card">BELUM ADA AGENDA.</div>
              </div>
            </section>

            <!-- VENUE / SCENE -->
<section
  id="lokasi-venue"
  class="home-section venue-section"
>
  <div class="home-container venue-layout">

    <div class="venue-content">

      <h2>
        SETIAP VENUE
        <br>
        PUNYA CERITA.
      </h2>

      <p>
        Lokasi Veberapa Vanue Di Bandung
      </p>
    </div>

    <div class="venue-map-wrapper">
      <iframe
        src="https://www.google.com/maps?q=Bandung%2C%20Jawa%20Barat&output=embed"
        width="100%"
        height="450"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </div>

  </div>
</section>

<!-- TENTANG KAMI -->
<section
  id="tentang-kami"
  class="home-section about-section"
>
  <div class="home-container">

    <div class="section-heading-row">
      <div>
        <div class="section-eyebrow">TENTANG KAMI</div>

        <h2>
          SATU TEMPAT UNTUK
          <br>
          MUSIK BANDUNG.
        </h2>

        <p class="about-intro">
          InfoMusikBDG adalah platform informasi dan ticketing
          yang membantu kamu menemukan berbagai event musik
          di Bandung dalam satu tempat.
        </p>
      </div>
    </div>

    <div class="about-grid">

      <div class="about-main-card">
        <span class="about-number">01</span>

        <h3>
          TEMUKAN
          <br>
          EVENT FAVORITMU.
        </h3>

        <p>
          Mulai dari konser, gigs, festival, hingga berbagai
          pertunjukan musik lainnya. Temukan event yang sesuai
          dengan selera musikmu dengan lebih mudah.
        </p>
      </div>

      <div class="about-side-content">

        <div class="about-item">
          <span>02</span>

          <div>
            <h3>EXPLORE</h3>
            <p>
              Jelajahi berbagai event, venue, dan kategori
              musik yang ada di Bandung.
            </p>
          </div>
        </div>

        <div class="about-item">
          <span>03</span>

          <div>
            <h3>EXPERIENCE</h3>
            <p>
              Dapatkan informasi event sebelum menikmati
              pengalaman musik secara langsung.
            </p>
          </div>
        </div>

        <div class="about-item">
          <span>04</span>

          <div>
            <h3>SUPPORT LOCAL</h3>
            <p>
              Ikut mengenal dan mendukung perkembangan
              ekosistem musik lokal Bandung.
            </p>
          </div>
        </div>

      </div>

    </div>

  </div>
</section>

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
            <div
              v-for="event in filteredEvents"
              :key="event.id"
              class="nb-card flex flex-col justify-between nb-card-hover"
              :class="isPastEvent(event) ? 'bg-gray-100 opacity-75 grayscale' : 'bg-white'"
            >
              <div>
                <div class="relative">
                  <img :src="event.image" :alt="event.title" class="w-full h-48 object-cover border-b-2 border-ink">
                  <!-- Badge Event Berakhir -->
                  <div v-if="isPastEvent(event)" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span class="bg-gray-800 text-white text-xs font-black uppercase px-3 py-1.5 border-2 border-white shadow-lg tracking-widest">
                      EVENT BERAKHIR
                    </span>
                  </div>
                </div>
                <div class="p-4 space-y-2">
                  <span
                    class="font-black text-xs px-2 py-0.5 nb-border uppercase"
                    :class="isPastEvent(event) ? 'bg-gray-300 text-gray-600' : 'bg-accent text-ink'"
                  >{{ event.category }}</span>
                  <h3 class="font-black text-xl uppercase leading-tight pt-1" :class="isPastEvent(event) ? 'text-gray-500' : ''">{{ event.title }}</h3>
                  <p class="text-xs font-bold text-muted">📅 {{ event.date }} • {{ event.time }}</p>
                  <p class="text-xs font-semibold text-ink/80">📍 {{ event.venue }}</p>
                  <p v-if="isPastEvent(event)" class="text-xs font-black text-gray-500 uppercase flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Event sudah berakhir
                  </p>
                </div>
              </div>
              <div class="p-4 pt-0 flex justify-between items-center border-t border-ink/10 mt-2">
                <span class="font-black text-sm" :class="isPastEvent(event) ? 'text-gray-500' : ''">Rp {{ formatNumber(event.minPrice) }}</span>
                <button @click="openEventDetail(event.id)" class="nb-btn px-3 py-1 text-xs uppercase" :class="isPastEvent(event) ? 'nb-btn-secondary opacity-60' : 'nb-btn-primary'">Detail</button>
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

        // Navbar berubah dari transparan menjadi solid ketika user scroll.
        isHeroNavbarScrolled: false,

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

favorites: JSON.parse(localStorage.getItem('wishlist') || '[]').map(item => item.id),

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

        // DATA KATEGORI DARI BACKEND
        categories: [],

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
      },

    // ── COMPUTED: popular events (max 3, sort by stok terjual) ──────────
    popularEvents() {
      return [...this.events]
        .filter(e => !this.isPastEvent(e))
        .sort((a, b) => {
          const aStock = a.tickets?.reduce((s, t) => s + Number(t.stock ?? t.stok ?? 0), 0) ?? 999
          const bStock = b.tickets?.reduce((s, t) => s + Number(t.stock ?? t.stok ?? 0), 0) ?? 999
          return aStock - bStock
        })
        .slice(0, 3)
    },

    // ── COMPUTED: upcoming events (5 terdekat dari hari ini) ────────────
    upcomingEvents() {
      const today = new Date(); today.setHours(0, 0, 0, 0)
      return [...this.events]
        .filter(e => {
          if (!e.date) return false
          const d = new Date(e.date); d.setHours(0, 0, 0, 0)
          return d >= today
        })
        .sort((a, b) => new Date(a.date) - new Date(b.date))
        .slice(0, 5)
    },

    // ── COMPUTED: jumlah event per kategori ─────────────────────────────
    categoryEventCounts() {
      const counts = {}
      this.events.forEach(e => {
        const cat = e.category || 'Lainnya'
        counts[cat] = (counts[cat] || 0) + 1
      })
      return counts
    }
  },

  async mounted() {
      // Load user dari localStorage
      this.loadUser()

      // Ambil event dari backend ketika website dibuka
      await this.getEvents()

      // Ambil kategori untuk section genre
      this.getCategories()

      // Cek apakah user sudah login
      if (this.isLoggedIn) {
        await this.getProfile()
        await this.getMyTickets()
        await this.getUserOrders()
      }

      document.addEventListener('click', this.handleDocumentClick)
      window.addEventListener('scroll', this.handleHeroScroll, { passive: true })
      this.handleHeroScroll()
    },

    beforeUnmount() {
      document.removeEventListener('click', this.handleDocumentClick)
      window.removeEventListener('scroll', this.handleHeroScroll)
    },

    methods: {
      // ==========================================
      // CEK APAKAH EVENT SUDAH LEWAT TANGGALNYA
      // ==========================================
      isPastEvent(event) {
        if (!event.date) return false
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        const eventDate = new Date(event.date)
        eventDate.setHours(0, 0, 0, 0)
        return eventDate < today
      },

      animateFavoriteToNavbar(event) {
  const button = document.querySelector(
    `button[data-favorite-id="${event.id}"]`
  )

  const wishlistButton = document.querySelector(
    '[data-wishlist-button]'
  )

  if (!button || !wishlistButton) return

  const start = button.getBoundingClientRect()
  const end   = wishlistButton.getBoundingClientRect()

  // ── 1. Ripple effect pada tombol sumber ──────────────────
  const ripple = document.createElement('div')
  ripple.style.cssText = `
    position: fixed;
    left: ${start.left + start.width / 2}px;
    top: ${start.top + start.height / 2}px;
    width: 8px; height: 8px;
    background: #FFD84D;
    border: 2px solid #111;
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    pointer-events: none;
    z-index: 9998;
    transition: transform 0.35s cubic-bezier(.2,.8,.2,1), opacity 0.35s ease;
  `
  document.body.appendChild(ripple)
  requestAnimationFrame(() => {
    ripple.style.transform = 'translate(-50%, -50%) scale(5)'
    ripple.style.opacity = '0'
  })
  setTimeout(() => ripple.remove(), 380)

  // ── 2. Hati terbang ──────────────────────────────────────
  const heart = document.createElement('div')
  heart.innerHTML = '♥'
  heart.style.cssText = `
    position: fixed;
    left: ${start.left + start.width / 2}px;
    top: ${start.top + start.height / 2}px;
    font-size: 22px;
    font-weight: 900;
    color: #111;
    text-shadow: 2px 2px 0 #FFD84D;
    z-index: 9999;
    pointer-events: none;
    transform: translate(-50%, -50%) scale(1.4);
    transition:
      left 0.55s cubic-bezier(.2,.8,.2,1),
      top 0.55s cubic-bezier(.2,.8,.2,1),
      transform 0.55s cubic-bezier(.2,.8,.2,1),
      opacity 0.25s ease 0.35s;
  `
  document.body.appendChild(heart)

  // Mulai animasi setelah satu frame
  requestAnimationFrame(() => {
    heart.style.left    = `${end.left + end.width / 2}px`
    heart.style.top     = `${end.top  + end.height / 2}px`
    heart.style.transform = 'translate(-50%, -50%) scale(0.6)'
    heart.style.opacity = '0'
  })

  // ── 3. Bounce + flash setelah hati sampai ────────────────
  setTimeout(() => {
    heart.remove()

    // Bounce pada tombol wishlist di navbar
    wishlistButton.classList.add('wishlist-bounce')
    setTimeout(() => wishlistButton.classList.remove('wishlist-bounce'), 500)

    // Flash message
    const eventName = event.nama_event || event.name || event.title || 'Event'
    window.dispatchEvent(new CustomEvent('show-flash', {
      detail: {
        title: 'DITAMBAHKAN KE FAVORIT ♥',
        message: `${eventName} berhasil disimpan ke wishlist kamu!`,
        type: 'success'
      }
    }))
  }, 580)
},
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
      // HERO NAVBAR SCROLL STATE
      // ==========================================

      handleHeroScroll() {
        this.isHeroNavbarScrolled = window.scrollY > 40
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

      // ==========================================
      // GET KATEGORI DARI BACKEND
      // GET /api/categories
      // ==========================================

      async getCategories() {
        try {
          const response = await api.get('/categories')
          this.categories = response.data.data || response.data || []
        } catch (error) {
          console.error('Gagal mengambil kategori:', error)
        }
      },

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

        // Cek stok tiket — flash jika habis
        const stok = Number(ticket.stock ?? ticket.stok ?? 0)
        if (stok <= 0) {
          showFlash(`Tiket "${ticket.name || ticket.nama_tiket}" sudah habis. Silakan pilih tiket lain.`, 'error', 'TIKET HABIS')
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

      toggleFavorite(event) {
  let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]')

  const index = wishlist.findIndex(item => item.id === event.id)

  // =========================
  // HAPUS DARI WISHLIST
  // =========================
  if (index !== -1) {
    wishlist.splice(index, 1)

    this.favorites = wishlist.map(item => item.id)

    localStorage.setItem('wishlist', JSON.stringify(wishlist))

    window.dispatchEvent(new Event('wishlist-updated'))

    return
  }

  // =========================
  // TAMBAH KE WISHLIST
  // =========================

  wishlist.push(event)

  this.favorites = wishlist.map(item => item.id)

  localStorage.setItem('wishlist', JSON.stringify(wishlist))

  window.dispatchEvent(new Event('wishlist-updated'))

  this.animateFavoriteToNavbar(event)
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
  @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap');

  :global(html) {
    scroll-behavior: smooth;
  }

  :global(body) {
    background-color: #F4F1E8;
    color: #111111;
    font-family: 'Space Grotesk', sans-serif;
    -webkit-font-smoothing: antialiased;
  }

  /* =========================================================
     EXISTING NEO-BRUTALISM SYSTEM
     ========================================================= */

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

  .wishlist-bounce {
    animation: wishlistBounce 0.4s ease;
  }

  @keyframes wishlistBounce {
    0% { transform: scale(1); }
    40% { transform: scale(1.25) rotate(-8deg); }
    70% { transform: scale(0.9) rotate(5deg); }
    100% { transform: scale(1) rotate(0); }
  }

  /* =========================================================
     INFO MUSIK BDG — FULLSCREEN VIDEO HERO
     ========================================================= */

  .home-page {
    position: relative;
    overflow: hidden;
    background: #F4F1E8;
  }

  .hero-video-section {
    position: relative;
    width: 100%;
    min-height: 100svh;
    height: 100svh;
    overflow: hidden;
    isolation: isolate;
    background: #111111;
    display: flex;
    align-items: center;
  }

  .hero-video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    z-index: -5;
    filter: saturate(0.9) contrast(1.05);
    transform: scale(1.015);
  }

  .hero-video-overlay {
    position: absolute;
    inset: 0;
    z-index: -4;
    background:
      linear-gradient(
        90deg,
        rgba(0, 0, 0, 0.82) 0%,
        rgba(0, 0, 0, 0.62) 38%,
        rgba(0, 0, 0, 0.28) 72%,
        rgba(0, 0, 0, 0.48) 100%
      ),
      linear-gradient(
        180deg,
        rgba(0, 0, 0, 0.48) 0%,
        transparent 28%,
        rgba(0, 0, 0, 0.45) 100%
      );
  }

  /* Subtle neo-brutalist grid over the video */
  .hero-grid {
    position: absolute;
    inset: 0;
    z-index: -2;
    pointer-events: none;
    overflow: hidden;
  }

  .hero-grid-line {
    position: absolute;
    background: rgba(255, 255, 255, 0.14);
  }

  .hero-grid-line-1 {
    width: 1px;
    height: 100%;
    left: 11%;
  }

  .hero-grid-line-2 {
    width: 1px;
    height: 100%;
    left: 76%;
  }

  .hero-grid-line-3 {
    width: 100%;
    height: 1px;
    top: 28%;
  }

  .hero-grid-line-4 {
    width: 100%;
    height: 1px;
    top: 78%;
  }

  .hero-square {
    position: absolute;
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .hero-square-1 {
    width: 180px;
    height: 180px;
    top: 13%;
    right: 8%;
    transform: rotate(4deg);
  }

  .hero-square-2 {
    width: 70px;
    height: 70px;
    top: 24%;
    right: 24%;
    background: rgba(242, 201, 76, 0.86);
    border-color: #111111;
    box-shadow: 6px 6px 0 #111111;
    transform: rotate(-7deg);
  }

  .hero-square-3 {
    width: 110px;
    height: 110px;
    bottom: 13%;
    right: 10%;
    border-color: rgba(242, 201, 76, 0.8);
  }

  .hero-square-4 {
    width: 42px;
    height: 42px;
    top: 12%;
    left: 8%;
    background: #F4F1E8;
    border-color: #111111;
    box-shadow: 4px 4px 0 #111111;
    transform: rotate(12deg);
  }

  .hero-square-5 {
    width: 22px;
    height: 22px;
    bottom: 18%;
    left: 12%;
    background: #F2C94C;
    border-color: #111111;
  }

  .hero-tech {
    position: absolute;
    color: rgba(255, 255, 255, 0.72);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .hero-tech-1 {
    top: 19%;
    left: 13%;
  }

  .hero-tech-2 {
    top: 31%;
    right: 9%;
    writing-mode: vertical-rl;
  }

  .hero-tech-3 {
    bottom: 22%;
    right: 16%;
  }

  .hero-tech-4 {
    bottom: 11%;
    left: 8%;
  }

  .hero-content {
    position: relative;
    z-index: 3;
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
    padding-top: 70px;
    color: #FFFFFF;
  }

  .hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 8px 12px;
    background: #F2C94C;
    color: #111111;
    border: 2px solid #111111;
    box-shadow: 5px 5px 0 #111111;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 22px;
  }

  .hero-kicker-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #111111;
    animation: heroPulse 1.4s ease-in-out infinite;
  }

  @keyframes heroPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(0.65); opacity: 0.55; }
  }

  .hero-title {
    max-width: 900px;
    margin: 0;
    font-size: clamp(3.1rem, 7.2vw, 7.7rem);
    line-height: 0.86;
    letter-spacing: -0.065em;
    font-weight: 900;
    text-transform: uppercase;
    text-wrap: balance;
    text-shadow: 5px 5px 0 rgba(0, 0, 0, 0.42);
  }

  .hero-title span {
    display: block;
    width: fit-content;
    margin-top: 10px;
    padding: 5px 12px 9px;
    color: #111111;
    background: #F2C94C;
    border: 2px solid #111111;
    box-shadow: 8px 8px 0 #111111;
    text-shadow: none;
  }

  .hero-description {
    max-width: 610px;
    margin-top: 28px;
    font-size: clamp(0.95rem, 1.4vw, 1.15rem);
    line-height: 1.55;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
  }

  .hero-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 30px;
  }

  .hero-main-btn {
    min-height: 52px;
    padding: 0 22px;
    gap: 22px;
    font-size: 13px;
    letter-spacing: 0.05em;
  }

  .hero-main-btn span {
    font-size: 20px;
    line-height: 1;
  }

  .hero-secondary-btn {
    min-height: 52px;
    padding: 0 18px;
    border: 2px solid rgba(255, 255, 255, 0.8);
    color: #FFFFFF;
    background: rgba(0, 0, 0, 0.18);
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: background 0.18s ease, color 0.18s ease;
  }

  .hero-secondary-btn:hover {
    background: #FFFFFF;
    color: #111111;
  }

  .hero-category-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 30px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }

  .hero-category-row > span {
    color: rgba(255, 255, 255, 0.56);
    margin-right: 4px;
  }

  .hero-category-row button {
    padding: 5px 9px;
    border: 1px solid rgba(255, 255, 255, 0.65);
    color: #FFFFFF;
    background: rgba(0, 0, 0, 0.18);
    font-weight: 800;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .hero-category-row button:hover {
    color: #111111;
    background: #F2C94C;
    border-color: #111111;
  }

  .hero-bottom {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 24px;
    z-index: 4;
    width: min(1180px, calc(100% - 40px));
    margin: auto;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    color: #FFFFFF;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
  }

  .hero-live-label {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .hero-live-dot {
    width: 7px;
    height: 7px;
    background: #F2C94C;
    border: 1px solid #111111;
  }

  .hero-scroll {
    display: flex;
    align-items: center;
    gap: 9px;
    opacity: 0.8;
    animation: heroScrollFloat 1.8s ease-in-out infinite;
  }

  .hero-scroll-arrow {
    font-size: 16px;
  }

  @keyframes heroScrollFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(5px); }
  }

  .hero-index {
    justify-self: end;
  }

  .hero-index span {
    opacity: 0.45;
    padding: 0 4px;
  }

  /* =========================================================
     NAVBAR OVER HERO
     Navbar component existing tetap dipakai.
     Selector :deep membuat navbar transparan saat posisi awal.
     ========================================================= */

  .hero-page :deep(nav) {
    position: absolute !important;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    background: transparent !important;
    border-color: transparent !important;
    box-shadow: none !important;
    transition:
      background 0.25s ease,
      border-color 0.25s ease,
      box-shadow 0.25s ease,
      backdrop-filter 0.25s ease;
  }

  .hero-page :deep(nav a),
  .hero-page :deep(nav button) {
    transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
  }

  .hero-page.hero-navbar-scrolled :deep(nav) {
    position: fixed !important;
    top: 12px;
    left: 14px;
    right: 14px;
    background: rgba(244, 241, 232, 0.94) !important;
    border: 2px solid #111111 !important;
    box-shadow: 5px 5px 0 #111111 !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  /* =========================================================
     MOBILE
     ========================================================= */

  @media (max-width: 768px) {
    .hero-video-section {
      min-height: 100svh;
      height: 100svh;
    }

    .hero-video {
      object-position: 58% center;
    }

    .hero-video-overlay {
      background:
        linear-gradient(
          90deg,
          rgba(0, 0, 0, 0.78),
          rgba(0, 0, 0, 0.48)
        ),
        linear-gradient(
          180deg,
          rgba(0, 0, 0, 0.55),
          transparent 34%,
          rgba(0, 0, 0, 0.7)
        );
    }

    .hero-content {
      width: min(100% - 28px, 620px);
      padding-top: 78px;
    }

    .hero-kicker {
      font-size: 9px;
      padding: 6px 9px;
      margin-bottom: 17px;
      box-shadow: 4px 4px 0 #111111;
    }

    .hero-title {
      max-width: 100%;
      font-size: clamp(2.8rem, 14vw, 5rem);
      line-height: 0.88;
    }

    .hero-title span {
      margin-top: 7px;
      padding: 4px 8px 7px;
      box-shadow: 5px 5px 0 #111111;
    }

    .hero-description {
      margin-top: 20px;
      max-width: 500px;
      font-size: 0.88rem;
      line-height: 1.45;
    }

    .hero-actions {
      margin-top: 22px;
      gap: 10px;
    }

    .hero-main-btn,
    .hero-secondary-btn {
      min-height: 46px;
      font-size: 10px;
    }

    .hero-category-row {
      margin-top: 20px;
      gap: 5px;
    }

    .hero-category-row button {
      padding: 4px 7px;
      font-size: 8px;
    }

    .hero-bottom {
      width: calc(100% - 28px);
      bottom: 15px;
      grid-template-columns: 1fr auto;
    }

    .hero-live-label {
      font-size: 8px;
    }

    .hero-index {
      display: none;
    }

    .hero-scroll {
      justify-self: end;
      font-size: 8px;
    }

    .hero-tech-1 {
      top: 18%;
      left: auto;
      right: 7%;
    }

    .hero-tech-2,
    .hero-tech-3,
    .hero-tech-4 {
      display: none;
    }

    .hero-square-1 {
      width: 105px;
      height: 105px;
      right: -20px;
      top: 25%;
    }

    .hero-square-2 {
      width: 45px;
      height: 45px;
      right: 20%;
      top: 31%;
    }

    .hero-square-3 {
      width: 70px;
      height: 70px;
      right: -15px;
      bottom: 20%;
    }

    .hero-square-4 {
      width: 30px;
      height: 30px;
      left: 6%;
      top: 20%;
    }

    .hero-square-5 {
      width: 16px;
      height: 16px;
      left: 7%;
      bottom: 18%;
    }

    .hero-grid-line-2 {
      left: 88%;
    }
  }

  @media (max-width: 420px) {
    .hero-title {
      font-size: clamp(2.5rem, 14vw, 3.8rem);
    }

    .hero-description {
      max-width: 330px;
    }

    .hero-secondary-btn {
      display: none;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .hero-kicker-dot,
    .hero-scroll {
      animation: none;
    }

    .hero-video {
      transform: none;
    }
  }
  

  /* =========================================================
     HOMEPAGE REDESIGN — FINAL
     ========================================================= */
  .hero-page { position: relative; background: #F4F1E8; }

  /* Navbar benar-benar overlay di atas video */
  .navbar-home-layer {
    position: absolute;
    inset: 0 0 auto 0;
    z-index: 1000;
    pointer-events: none;
  }

  .navbar-home-layer > * { pointer-events: auto; }

  /* Target root Navbar apa pun bentuk wrapper-nya */
  .hero-page .navbar-home-layer :deep(*) {
    box-sizing: border-box;
  }

  .hero-page .navbar-home-layer :deep(> *) {
    background: transparent !important;
    border-color: transparent !important;
    box-shadow: none !important;
  }

  .hero-page .navbar-home-layer :deep(a),
  .hero-page .navbar-home-layer :deep(button) {
    transition: background .18s ease, color .18s ease, border-color .18s ease;
  }

  .navbar-home-layer.navbar-scrolled {
    position: fixed;
    top: 12px;
    left: 14px;
    right: 14px;
    inset-bottom: auto;
  }

  .navbar-home-layer.navbar-scrolled :deep(> *) {
    background: rgba(244, 241, 232, .95) !important;
    border: 2px solid #111 !important;
    box-shadow: 5px 5px 0 #111 !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  .hero-video-section {
    position: relative;
    width: 100%;
    height: 100svh;
    min-height: 720px;
    overflow: hidden;
    isolation: isolate;
    display: flex;
    align-items: center;
    background: #111;
  }

  .hero-video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    z-index: -5;
    filter: saturate(.82) contrast(1.08) brightness(.78);
  }

  .hero-video-overlay {
    position: absolute;
    inset: 0;
    z-index: -4;
    background:
      linear-gradient(90deg, rgba(0,0,0,.86) 0%, rgba(0,0,0,.66) 40%, rgba(0,0,0,.22) 78%, rgba(0,0,0,.45) 100%),
      linear-gradient(180deg, rgba(0,0,0,.52) 0%, transparent 32%, rgba(0,0,0,.66) 100%);
  }

  /* GRID seperti contoh Figma: garis tipis, merata, bukan kotak dekorasi */
  .hero-grid-overlay,
  .home-content-grid {
    background-image:
      linear-gradient(rgba(17,17,17,.10) 1px, transparent 1px),
      linear-gradient(90deg, rgba(17,17,17,.10) 1px, transparent 1px);
    background-size: 50px 50px;
  }

  .hero-grid-overlay {
    position: absolute;
    inset: 0;
    z-index: -2;
    pointer-events: none;
    opacity: .42;
    background-image:
      linear-gradient(rgba(255,255,255,.12) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px);
  }

  .hero-content {
    position: relative;
    z-index: 5;
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
    padding-top: 95px;
    color: #fff;
  }

  .hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 7px 11px;
    background: #F2C94C;
    color: #111;
    border: 2px solid #111;
    box-shadow: 5px 5px 0 #111;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 20px;
  }

  .hero-kicker-dot { width: 7px; height: 7px; background: #111; }

  .hero-title {
    max-width: 920px;
    margin: 0;
    font-size: clamp(3.5rem, 7vw, 7rem);
    line-height: .87;
    letter-spacing: -.065em;
    font-weight: 900;
    text-transform: uppercase;
    text-shadow: 4px 4px 0 rgba(0,0,0,.42);
  }

  .hero-title span {
    display: block;
    width: fit-content;
    margin-top: 10px;
    padding: 4px 12px 8px;
    color: #111;
    background: #F2C94C;
    border: 2px solid #111;
    box-shadow: 7px 7px 0 #111;
    text-shadow: none;
  }

  .hero-description {
    max-width: 590px;
    margin-top: 25px;
    color: rgba(255,255,255,.9);
    font-size: clamp(.92rem, 1.3vw, 1.08rem);
    line-height: 1.55;
    font-weight: 500;
  }

  .hero-actions { display: flex; flex-wrap: wrap; gap: 13px; margin-top: 26px; }
  .hero-main-btn { min-height: 50px; padding: 0 21px; gap: 18px; font-size: 12px; }
  .hero-main-btn span { font-size: 19px; }
  .hero-secondary-btn {
    min-height: 50px; padding: 0 17px; color: #fff; background: rgba(0,0,0,.2);
    border: 2px solid rgba(255,255,255,.8); font-size: 11px; font-weight: 900; cursor: pointer;
  }
  .hero-secondary-btn:hover { background: #fff; color: #111; }

  .hero-category-row { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 25px; }
  .hero-category-row > span { color: rgba(255,255,255,.55); font-size: 9px; font-weight: 900; letter-spacing: .13em; }
  .hero-category-row button {
    padding: 5px 8px; color: #fff; background: rgba(0,0,0,.18);
    border: 1px solid rgba(255,255,255,.6); font-size: 9px; font-weight: 900; text-transform: uppercase; cursor: pointer;
  }
  .hero-category-row button:hover { color: #111; background: #F2C94C; border-color: #111; }

  .hero-bottom {
    position: absolute; left: 0; right: 0; bottom: 22px; z-index: 5;
    width: min(1180px, calc(100% - 40px)); margin: auto;
    display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
    color: #fff; font-size: 9px; font-weight: 900; letter-spacing: .15em; text-transform: uppercase;
  }
  .hero-bottom > :last-child { justify-self: end; }
  .hero-scroll { opacity: .8; }

  /* CONTENT GRID */
  .home-content-grid {
    position: relative;
    background-color: #F4F1E8;
    background-image:
      linear-gradient(rgba(17,17,17,.085) 1px, transparent 1px),
      linear-gradient(90deg, rgba(17,17,17,.085) 1px, transparent 1px);
    background-size: 50px 50px;
  }

  .home-section { padding: 78px 0; border-bottom: 2px solid #111; }
  .home-container { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }

  .home-stats-section { padding: 0; border-bottom: 2px solid #111; background: rgba(244,241,232,.9); }
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); }
  .stat-box { min-height: 105px; padding: 20px; border-right: 2px solid #111; display: flex; flex-direction: column; justify-content: center; }
  .stat-box:last-child { border-right: 0; }
  .stat-box strong { font-size: 30px; line-height: 1; font-weight: 900; }
  .stat-box span { margin-top: 7px; font-size: 9px; font-weight: 900; letter-spacing: .12em; }
  .stat-box-accent { background: #F2C94C; }

  .section-heading-row { display: flex; justify-content: space-between; align-items: end; gap: 25px; margin-bottom: 28px; }
  .section-eyebrow { margin-bottom: 7px; color: #555; font-size: 9px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase; }
  .section-heading-row h2 { margin: 0; font-size: clamp(2rem, 4vw, 3.6rem); line-height: .95; letter-spacing: -.045em; font-weight: 900; text-transform: uppercase; }
  .section-heading-row p { margin-top: 9px; color: #555; font-size: 13px; font-weight: 600; }
  .section-link { padding: 10px 13px; white-space: nowrap; font-size: 10px; }

  .popular-event-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
  .popular-event-card { background: #fff; border: 2px solid #111; box-shadow: 5px 5px 0 #111; transition: transform .15s ease, box-shadow .15s ease; }
  .popular-event-card:hover { transform: translate(-3px,-3px); box-shadow: 8px 8px 0 #111; }
  .popular-event-card-featured { grid-column: span 2; }
  .event-card-image-wrap { position: relative; height: 225px; overflow: hidden; border-bottom: 2px solid #111; }
  .popular-event-card-featured .event-card-image-wrap { height: 320px; }
  .event-card-image { width: 100%; height: 100%; object-fit: cover; display: block; filter: saturate(.92); }
  .event-number { position: absolute; top: 10px; left: 10px; padding: 4px 7px; color: #111; background: #F2C94C; border: 2px solid #111; font-size: 10px; font-weight: 900; }
  .event-favorite { position: absolute; top: 10px; right: 10px; width: 38px; height: 38px; background: #fff; border: 2px solid #111; box-shadow: 3px 3px 0 #111; font-size: 21px; line-height: 1; cursor: pointer; }
  .event-card-body { padding: 17px; }
  .event-category { display: inline-block; padding: 3px 6px; background: #F2C94C; border: 1px solid #111; font-size: 8px; font-weight: 900; text-transform: uppercase; }
  .event-card-body h3 { margin: 10px 0 7px; font-size: 21px; line-height: 1; font-weight: 900; text-transform: uppercase; }
  .event-card-body p { margin: 4px 0; color: #555; font-size: 10px; font-weight: 700; }
  .event-card-footer { display: flex; justify-content: space-between; align-items: end; gap: 10px; margin-top: 16px; padding-top: 12px; border-top: 1px solid #111; }
  .event-card-footer strong { font-size: 14px; font-weight: 900; }
  .event-card-footer button { padding: 6px 9px; background: #111; color: #fff; border: 2px solid #111; font-size: 9px; font-weight: 900; cursor: pointer; }

  .genre-grid { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 2px solid #111; border-left: 2px solid #111; }
  .genre-card { min-height: 155px; padding: 18px; text-align: left; background: rgba(255,255,255,.78); border-right: 2px solid #111; border-bottom: 2px solid #111; cursor: pointer; transition: background .15s ease, transform .15s ease; display: flex; flex-direction: column; }
  .genre-card:hover { background: #fff; transform: translate(-2px,-2px); }
  .genre-card strong { display: block; font-size: clamp(1.4rem, 3vw, 2.3rem); line-height: .9; font-weight: 900; }
  .genre-card small { display: block; margin-top: 8px; color: #666; font-size: 8px; font-weight: 900; letter-spacing: .08em; flex: 1; }
  .genre-count { display: inline-block; margin-top: 12px; font-size: 9px; font-weight: 900; letter-spacing: .12em; color: #111; background: #F2C94C; border: 1px solid #111; padding: 2px 8px; width: fit-content; }
  .genre-yellow { background: #F2C94C; }
  .genre-dark { background: #111; color: #fff; }
  .genre-dark small { color: #bbb; }

  .upcoming-list { border-top: 2px solid #111; }
  .upcoming-row { display: grid; grid-template-columns: 85px 1fr 180px 50px; align-items: center; gap: 20px; min-height: 108px; padding: 14px 0; border-bottom: 2px solid #111; }
  .upcoming-date { width: 70px; height: 70px; background: #fff; border: 2px solid #111; box-shadow: 4px 4px 0 #111; display: flex; flex-direction: column; justify-content: center; align-items: center; }
  .upcoming-date span { font-size: 27px; line-height: .85; font-weight: 900; }
  .upcoming-date small { margin-top: 5px; font-size: 8px; font-weight: 900; }
  .upcoming-main > span { color: #666; font-size: 8px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
  .upcoming-main h3 { margin: 4px 0; font-size: 20px; line-height: 1; font-weight: 900; text-transform: uppercase; }
  .upcoming-main p { margin: 0; color: #666; font-size: 10px; font-weight: 700; }
  .upcoming-price { text-align: right; }
  .upcoming-price small { display: block; color: #666; font-size: 8px; font-weight: 900; }
  .upcoming-price strong { display: block; margin-top: 3px; font-size: 13px; font-weight: 900; }
  .upcoming-arrow { width: 42px; height: 42px; justify-self: end; background: #F2C94C; border: 2px solid #111; box-shadow: 3px 3px 0 #111; font-size: 18px; font-weight: 900; cursor: pointer; }

  .venue-section { background: #111; color: #fff; }
  .venue-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
  .venue-section .section-eyebrow { color: #aaa; }
  .venue-layout h2 { margin: 0; font-size: clamp(2.6rem, 5vw, 5rem); line-height: .86; letter-spacing: -.06em; font-weight: 900; }
  .venue-layout p { max-width: 520px; margin: 20px 0 25px; color: #ccc; font-size: 14px; line-height: 1.55; }
  .venue-map-art { position: relative; min-height: 360px; overflow: hidden; border: 2px solid #fff; background: #171717; }
  .venue-map-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.11) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.11) 1px, transparent 1px); background-size: 40px 40px; transform: rotate(-8deg) scale(1.2); }
  .map-label { position: absolute; padding: 5px 8px; color: #111; background: #F2C94C; border: 2px solid #111; box-shadow: 4px 4px 0 #000; font-size: 9px; font-weight: 900; }
  .map-label-1 { top: 22%; left: 18%; }
  .map-label-2 { top: 38%; right: 16%; }
  .map-label-3 { bottom: 20%; left: 28%; }
  .map-label-4 { top: 48%; left: 43%; background: #fff; }
  .map-cross { position: absolute; width: 18px; height: 18px; border: 2px solid #fff; }
  .map-cross-1 { top: 20%; left: 48%; }
  .map-cross-2 { bottom: 30%; right: 27%; }
  .map-cross-3 { bottom: 18%; left: 17%; }

  .why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .why-card { min-height: 190px; padding: 20px; background: #fff; border: 2px solid #111; box-shadow: 5px 5px 0 #111; }
  .why-card span { font-size: 10px; font-weight: 900; }
  .why-card h3 { margin-top: 45px; font-size: 24px; font-weight: 900; }
  .why-card p { margin-top: 7px; color: #555; font-size: 11px; line-height: 1.5; font-weight: 600; }

  .organizer-cta-section { padding: 35px 0; background: #F2C94C; }
  .organizer-cta { display: flex; align-items: center; justify-content: space-between; gap: 25px; }
  .organizer-cta h2 { margin: 0; font-size: clamp(2rem, 4vw, 3.8rem); line-height: .9; font-weight: 900; }
  .organizer-cta p { margin-top: 8px; font-size: 12px; font-weight: 700; }
  .cta-button { min-height: 50px; padding: 0 18px; white-space: nowrap; }

  .empty-home-card { padding: 35px; background: #fff; border: 2px solid #111; box-shadow: 5px 5px 0 #111; text-align: center; font-size: 12px; font-weight: 900; }

  @media (max-width: 900px) {
    .hero-video-section { min-height: 720px; }
    .hero-content { padding-top: 110px; }
    .hero-title { font-size: clamp(3rem, 9vw, 5.5rem); }
    .popular-event-grid { grid-template-columns: repeat(2, 1fr); }
    .popular-event-card-featured { grid-column: span 2; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .stat-box:nth-child(2) { border-right: 0; }
    .stat-box:nth-child(-n+2) { border-bottom: 2px solid #111; }
    .genre-grid { grid-template-columns: repeat(2, 1fr); }
    .venue-layout { grid-template-columns: 1fr; }
  }

  @media (max-width: 640px) {
    .navbar-home-layer.navbar-scrolled { top: 7px; left: 7px; right: 7px; }
    .hero-video-section { height: 100svh; min-height: 650px; }
    .hero-content { width: calc(100% - 28px); padding-top: 115px; }
    .hero-title { font-size: clamp(2.7rem, 13vw, 4.3rem); }
    .hero-title span { box-shadow: 5px 5px 0 #111; }
    .hero-description { max-width: 92%; font-size: .84rem; }
    .hero-bottom { width: calc(100% - 28px); grid-template-columns: 1fr auto; bottom: 14px; font-size: 8px; }
    .hero-index { display: none; }
    .home-container { width: calc(100% - 28px); }
    .home-section { padding: 55px 0; }
    .section-heading-row { display: block; }
    .section-link { margin-top: 15px; }
    .popular-event-grid { grid-template-columns: 1fr; }
    .popular-event-card-featured { grid-column: span 1; }
    .popular-event-card-featured .event-card-image-wrap { height: 230px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .stat-box { min-height: 90px; padding: 13px; }
    .stat-box strong { font-size: 23px; }
    .genre-grid { grid-template-columns: 1fr; }
    .genre-card { min-height: 130px; }
    .upcoming-row { grid-template-columns: 62px 1fr 35px; gap: 12px; }
    .upcoming-date { width: 58px; height: 58px; }
    .upcoming-date span { font-size: 22px; }
    .upcoming-price { display: none; }
    .venue-map-art { min-height: 270px; }
    .why-grid { grid-template-columns: 1fr; }
    .organizer-cta { display: block; }
    .cta-button { margin-top: 18px; }
  }

  /* ================================
   TENTANG KAMI
================================ */

.about-section {
  position: relative;
}

.about-intro {
  max-width: 700px;
  margin-top: 20px;
  font-size: 18px;
  line-height: 1.7;
}

.about-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 30px;
  margin-top: 50px;
}

/* Card utama */

.about-main-card {
  padding: 40px;

  background: #f2c94c;

  border: 3px solid #111;

  box-shadow: 10px 10px 0 #111;
}

.about-number {
  display: block;

  margin-bottom: 35px;

  font-size: 14px;
  font-weight: 900;
}

.about-main-card h3 {
  margin-bottom: 25px;

  font-size: clamp(2.5rem, 5vw, 5rem);
  line-height: 0.9;
  font-weight: 950;
  text-transform: uppercase;
}

.about-main-card p {
  max-width: 600px;

  font-size: 16px;
  line-height: 1.7;
}

/* Item kanan */

.about-side-content {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.about-item {
  display: grid;
  grid-template-columns: 50px 1fr;
  gap: 20px;

  padding: 25px 0;

  border-top: 2px solid #111;
}

.about-item:last-child {
  border-bottom: 2px solid #111;
}

.about-item > span {
  font-size: 14px;
  font-weight: 950;
}

.about-item h3 {
  margin-bottom: 8px;

  font-size: 22px;
  font-weight: 950;
}

.about-item p {
  margin: 0;

  color: #444;

  font-size: 14px;
  line-height: 1.6;
}

/* ================================
   RESPONSIVE
================================ */

@media (max-width: 900px) {
  .about-grid {
    grid-template-columns: 1fr;
  }

  .about-main-card {
    box-shadow: 7px 7px 0 #111;
  }
}

@media (max-width: 600px) {
  .about-intro {
    font-size: 15px;
  }

  .about-main-card {
    padding: 25px;
  }

  .about-main-card h3 {
    font-size: 2.7rem;
  }

  .about-item {
    grid-template-columns: 35px 1fr;
    gap: 12px;
  }
}
</style>
