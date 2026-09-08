<template>
  <aside class="sidebar">
    <div class="logo">
      <h2>InfoMusikBDG</h2>
      <span>ADMIN PANEL</span>
    </div>

    <nav>
      <RouterLink to="/admin">
        Dashboard
      </RouterLink>

      <RouterLink to="/admin/categories">
        Kategori
      </RouterLink>

      <RouterLink to="/admin/events">
        Event
      </RouterLink>

      <RouterLink to="/admin/tickets">
        Tiket
      </RouterLink>

      <RouterLink to="/admin/orders">
        Pesanan
      </RouterLink>

      <RouterLink to="/admin/payments">
        Pembayaran
      </RouterLink>
    </nav>

    <button class="logout" @click="logout">
      Logout
    </button>
  </aside>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')

  router.push('/login')
}
</script>

<style scoped>
.sidebar {
  width: 250px;
  min-height: 100vh;
  background: #ffffff;
  border-right: 3px solid #111;
  padding: 25px 18px;
  display: flex;
  flex-direction: column;
}

.logo {
  margin-bottom: 35px;
}

.logo h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 900;
}

.logo span {
  font-size: 11px;
  font-weight: 700;
}

nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

nav a {
  text-decoration: none;
  color: #111;
  padding: 13px 15px;
  border: 2px solid #111;
  font-weight: 700;
  background: #fff;
}

nav a:hover,
nav a.router-link-active {
  background: #111;
  color: #fff;
}

.logout {
  margin-top: auto;
  padding: 13px;
  background: #fff;
  border: 2px solid #111;
  font-weight: 800;
  cursor: pointer;
}

.logout:hover {
  background: #111;
  color: #fff;
}
</style>