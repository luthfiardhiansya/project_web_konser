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
    }
  ]
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  let user = null

  try {
    user = JSON.parse(localStorage.getItem('user'))
  } catch (error) {
    user = null
  }

  if (to.meta.requiresAuth && !token) {
    next('/login')
    return
  }

  if (to.meta.requiresAdmin && user?.role !== 'admin') {
    next('/')
    return
  }

  next()
})

export default router