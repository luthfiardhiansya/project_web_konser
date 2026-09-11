<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { ref, computed } from 'vue'
import AdminFlash from './AdminFlash.vue'

const router = useRouter()
const sidebarOpen = ref(false)

const user = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('user')) || {}
  } catch {
    return {}
  }
})

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')

  router.push('/login')
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}
</script>

<template>
  <div class="admin-layout">
    <AdminFlash />

    <!-- Overlay Mobile -->
    <div
      v-if="sidebarOpen"
      class="sidebar-overlay"
      @click="closeSidebar"
    ></div>

    <!-- SIDEBAR -->
    <aside
      class="sidebar"
      :class="{ 'sidebar-open': sidebarOpen }"
    >

      <!-- BRAND -->
      <div class="sidebar-brand">
        <div class="brand-icon">
          <i class="fa-solid fa-music"></i>
        </div>

        <span class="brand-name">
          InfoMusikBDG
        </span>
      </div>


      <!-- MENU -->
      <nav class="sidebar-nav">

        <RouterLink
          to="/admin"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-house"></i>
          <span>Dashboard</span>
        </RouterLink>


        <RouterLink
          to="/admin/kategori"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-layer-group"></i>
          <span>Kategori</span>
        </RouterLink>


        <RouterLink
          to="/admin/events"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-calendar-days"></i>
          <span>Event</span>
        </RouterLink>


        <RouterLink
          to="/admin/tiket"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-ticket"></i>
          <span>Tiket</span>
        </RouterLink>


        <RouterLink
          to="/admin/pesanan"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-cart-shopping"></i>
          <span>Pesanan</span>
        </RouterLink>


        <RouterLink
          to="/admin/pengguna"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-users"></i>
          <span>Pengguna</span>
        </RouterLink>


        <RouterLink
          to="/admin/pembayaran"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-money-check-dollar"></i>
          <span>Pembayaran</span>
        </RouterLink>


        <RouterLink
          to="/admin/scan-qr"
          class="nav-item"
          @click="closeSidebar"
        >
          <i class="fa-solid fa-qrcode"></i>
          <span>Scan QR</span>
        </RouterLink>

      </nav>


      <!-- SIDEBAR FOOTER -->
      <div class="sidebar-footer">

        <RouterLink
          to="/"
          class="footer-button outline-button"
        >
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
          <span>Lihat Website</span>
        </RouterLink>


        <button
          @click="logout"
          class="footer-button danger-button"
        >
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Logout</span>
        </button>

      </div>

    </aside>


    <!-- MAIN -->
    <div class="admin-main-wrapper">

      <!-- HEADER -->
      <header class="admin-header">

        <div class="header-left">

          <!-- Mobile Button -->
          <button
            class="mobile-toggle-btn"
            @click="toggleSidebar"
          >
            <i class="fa-solid fa-bars"></i>
          </button>


          <div class="header-title">

            <span class="application-name">
              InfoMusikBDG
            </span>

            <span class="header-badge">
              Admin
            </span>

          </div>

        </div>


        <!-- USER -->
        <div class="header-actions">

          <div class="user-badge">

            <div class="user-avatar">
              {{ (user.name || 'A').charAt(0).toUpperCase() }}
            </div>

            <div class="user-info">

              <span class="user-name">
                {{ user.name || 'Administrator' }}
              </span>

              <span class="user-role">
                Administrator
              </span>

            </div>

          </div>

        </div>

      </header>


      <!-- CONTENT -->
      <main class="admin-content">
        <slot />
      </main>

    </div>

  </div>
</template>


<style scoped>

.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f6f7f9;
}


/* =========================
   SIDEBAR
========================= */

.sidebar {
  width: 250px;
  background-color: #ffffff;
  border-right: 1px solid #e5e7eb;

  display: flex;
  flex-direction: column;

  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;

  z-index: 90;

  transition: transform 0.25s ease;
}


/* BRAND */

.sidebar-brand {
  height: 64px;
  padding: 0 1.25rem;

  display: flex;
  align-items: center;
  gap: 0.75rem;

  border-bottom: 1px solid #e5e7eb;
}

.brand-icon {
  width: 34px;
  height: 34px;

  display: flex;
  align-items: center;
  justify-content: center;

  background-color: #7e22ce;
  color: white;

  border-radius: 8px;
}

.brand-name {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
}


/* NAVIGATION */

.sidebar-nav {
  flex: 1;

  padding: 1rem 0.75rem;

  display: flex;
  flex-direction: column;

  gap: 0.25rem;

  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;

  gap: 0.75rem;

  padding: 0.7rem 0.875rem;

  border-radius: 7px;

  color: #6b7280;

  font-size: 0.9rem;
  font-weight: 500;

  text-decoration: none;

  transition: 0.2s;
}

.nav-item i {
  width: 18px;
  text-align: center;
}

.nav-item:hover {
  background-color: #f3f4f6;
  color: #111827;
}

.nav-item.router-link-exact-active {
  background-color: #f3e8ff;
  color: #7e22ce;
  font-weight: 600;
}


/* FOOTER */

.sidebar-footer {
  padding: 1rem;

  border-top: 1px solid #e5e7eb;

  display: flex;
  flex-direction: column;

  gap: 0.5rem;
}

.footer-button {
  width: 100%;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 0.5rem;

  padding: 0.65rem;

  border-radius: 7px;

  font-size: 0.875rem;
  font-weight: 600;

  cursor: pointer;

  text-decoration: none;

  transition: 0.2s;
}

.outline-button {
  background-color: white;

  color: #374151;

  border: 1px solid #d1d5db;
}

.outline-button:hover {
  background-color: #f9fafb;
}

.danger-button {
  background-color: white;

  color: #dc2626;

  border: 1px solid #fecaca;
}

.danger-button:hover {
  background-color: #fef2f2;
}


/* =========================
   MAIN
========================= */

.admin-main-wrapper {
  flex: 1;

  margin-left: 250px;

  display: flex;
  flex-direction: column;

  min-width: 0;
}


/* HEADER */

.admin-header {
  height: 64px;

  background-color: white;

  border-bottom: 1px solid #e5e7eb;

  padding: 0 1.5rem;

  display: flex;
  align-items: center;
  justify-content: space-between;

  position: sticky;
  top: 0;

  z-index: 80;
}

.header-left {
  display: flex;
  align-items: center;

  gap: 0.75rem;
}

.header-title {
  display: flex;
  align-items: center;

  gap: 0.6rem;
}

.application-name {
  font-size: 0.95rem;
  font-weight: 600;

  color: #111827;
}

.header-badge {
  font-size: 0.7rem;

  font-weight: 600;

  text-transform: uppercase;

  background-color: #f3e8ff;

  color: #7e22ce;

  padding: 0.2rem 0.5rem;

  border-radius: 4px;
}


/* USER */

.header-actions {
  display: flex;
  align-items: center;
}

.user-badge {
  display: flex;
  align-items: center;

  gap: 0.6rem;
}

.user-avatar {
  width: 34px;
  height: 34px;

  border-radius: 50%;

  background-color: #7e22ce;

  color: white;

  display: flex;
  align-items: center;
  justify-content: center;

  font-weight: 600;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.875rem;

  font-weight: 600;

  color: #111827;
}

.user-role {
  font-size: 0.7rem;

  color: #9ca3af;
}


/* CONTENT */

.admin-content {
  padding: 1.5rem;

  flex: 1;
}


/* MOBILE */

.mobile-toggle-btn {
  display: none;

  background: none;

  border: none;

  color: #374151;

  cursor: pointer;

  padding: 0.4rem;

  font-size: 1.1rem;
}

.sidebar-overlay {
  display: none;

  position: fixed;

  inset: 0;

  background-color: rgba(0, 0, 0, 0.4);

  z-index: 85;
}


@media (max-width: 900px) {

  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar-open {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .admin-main-wrapper {
    margin-left: 0;
  }

  .mobile-toggle-btn {
    display: block;
  }

  .user-info {
    display: none;
  }

}

</style>