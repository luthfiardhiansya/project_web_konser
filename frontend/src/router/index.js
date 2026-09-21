import {
  createRouter,
  createWebHistory
} from 'vue-router'

import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import EventDetailView from '../views/EventDetailView.vue'
import DashboardView from '../views/admin/DashboardView.vue'
import KategoriView from '../views/admin/KategoriView.vue'
import EventView from '../views/admin/EventView.vue'
import TiketView from '../views/admin/TiketView.vue'
import PesananView from '../views/admin/PesananView.vue'
import PenggunaView from '../views/admin/PenggunaView.vue'
import PembayaranView from '../views/admin/PembayaranView.vue'
import ScanQRView from '../views/admin/ScanQRView.vue'
import LaporanStatistikView from '../views/admin/LaporanStatistikView.vue'
import ProfileView from '../views/ProfileView.vue'
import WishlistView from '../views/WishlistView.vue'
import MyTicketsView from '../views/MyTicketsView.vue'
import ScannerView from '../views/ScannerView.vue'
import UserPesananView from '../views/UserPesananView.vue'
import GoogleCallbackView from '../views/GoogleCallbackView.vue'

// Halaman akses ditolak
import AccessDeniedAdminView from '../views/AccessDeniedAdminView.vue'
import AccessDeniedScannerView from '../views/AccessDeniedScannerView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/event/:id',
      name: 'event-detail',
      component: EventDetailView
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView
    },

    // ─── Admin ──────────────────────────────────────────────
    {
      path: '/admin',
      name: 'admin',
      component: DashboardView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/kategori',
      name: 'admin-kategori',
      component: KategoriView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/events',
      name: 'admin-events',
      component: EventView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/tiket',
      name: 'admin-tiket',
      component: TiketView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/pesanan',
      name: 'admin-pesanan',
      component: PesananView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/pengguna',
      name: 'admin-pengguna',
      component: PenggunaView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/pembayaran',
      name: 'admin-pembayaran',
      component: PembayaranView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/scan-qr',
      name: 'admin-scan-qr',
      component: ScanQRView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
      path: '/admin/laporan',
      name: 'admin-laporan',
      component: LaporanStatistikView,
      meta: { requiresAuth: true, requiresAdmin: true }
    },

    // ─── Scanner ─────────────────────────────────────────────
    {
      path: '/scanner',
      name: 'scanner',
      component: ScannerView,
      meta: { requiresAuth: true, requiresScanner: true }
    },

    // ─── User ────────────────────────────────────────────────
    {
      path: '/profile',
      name: 'profile',
      component: ProfileView,
      meta: { requiresAuth: true }
    },
    {
      path: '/wishlist',
      name: 'wishlist',
      component: WishlistView,
      meta: { requiresAuth: true }
    },
    {
      path: '/my-tickets',
      name: 'my-tickets',
      component: MyTicketsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/pesanan',
      name: 'pesanan',
      component: UserPesananView,
      meta: { requiresAuth: true }
    },

    // ─── Akses Ditolak ───────────────────────────────────────
    {
      // User biasa / scanner mencoba akses /admin/*
      path: '/akses-ditolak/admin',
      name: 'access-denied-admin',
      component: AccessDeniedAdminView
    },
    {
      // User biasa / admin mencoba akses /scanner
      path: '/akses-ditolak/scanner',
      name: 'access-denied-scanner',
      component: AccessDeniedScannerView
    },

    // ─── Google OAuth ────────────────────────────────────────
    {
      path: '/auth/google/callback',
      name: 'google-callback',
      component: GoogleCallbackView
    }
  ]
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  let user = null

  try {
    user = JSON.parse(localStorage.getItem('user'))
  } catch {
    user = null
  }

  // ── Belum login ───────────────────────────────────────────
  if (to.meta.requiresAuth && !token) {
    next('/login')
    return
  }

  // ── Akun Scanner ──────────────────────────────────────────
  // Scanner hanya boleh di /scanner — redirect ke sana dari mana saja
  if (user?.role === 'scanner') {
    const scannerAllowed = ['/scanner', '/login', '/auth/google/callback']
    if (!scannerAllowed.includes(to.path)) {
      next('/scanner')
      return
    }
  }

  // ── Butuh role admin ──────────────────────────────────────
  if (to.meta.requiresAdmin && user?.role !== 'admin') {
    next('/akses-ditolak/admin')
    return
  }

  // ── Butuh role scanner ────────────────────────────────────
  if (to.meta.requiresScanner && user?.role !== 'scanner') {
    next('/akses-ditolak/scanner')
    return
  }

  next()
})

export default router
