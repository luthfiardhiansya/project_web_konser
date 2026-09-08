<script setup>
import AdminLayout from '../../components/AdminLayout.vue'
</script>

<template>
  <AdminLayout>

    <div class="dashboard">

      <div class="page-header">
        <div>
          <h1>Dashboard</h1>
          <p>
            Ringkasan pengelolaan InfoMusikBDG
          </p>
        </div>
      </div>


      <div class="stats-grid">

        <div class="stat-card">
          <div class="stat-icon purple">
            <i class="fa-solid fa-calendar-days"></i>
          </div>

          <div>
            <span>Total Event</span>
            <strong>0</strong>
          </div>
        </div>


        <div class="stat-card">
          <div class="stat-icon blue">
            <i class="fa-solid fa-ticket"></i>
          </div>

          <div>
            <span>Total Tiket</span>
            <strong>0</strong>
          </div>
        </div>


        <div class="stat-card">
          <div class="stat-icon green">
            <i class="fa-solid fa-cart-shopping"></i>
          </div>

          <div>
            <span>Total Pesanan</span>
            <strong>0</strong>
          </div>
        </div>


        <div class="stat-card">
          <div class="stat-icon orange">
            <i class="fa-solid fa-users"></i>
          </div>

          <div>
            <span>Total Pengguna</span>
            <strong>0</strong>
          </div>
        </div>

      </div>


      <div class="dashboard-card">

        <h2>Selamat Datang di Admin Panel</h2>

        <p>
          Gunakan menu di sebelah kiri untuk mengelola
          event, kategori, tiket, pesanan, pengguna,
          dan pembayaran.
        </p>

      </div>

    </div>

  </AdminLayout>
</template>


<style scoped>

.dashboard {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  margin: 0;

  font-size: 1.6rem;

  font-weight: 700;

  color: #111827;
}

.page-header p {
  margin-top: 0.35rem;

  color: #6b7280;

  font-size: 0.9rem;
}


/* STATISTICS */

.stats-grid {
  display: grid;

  grid-template-columns: repeat(4, 1fr);

  gap: 1rem;

  margin-bottom: 1.5rem;
}

.stat-card {
  background-color: white;

  border: 1px solid #e5e7eb;

  border-radius: 8px;

  padding: 1.25rem;

  display: flex;
  align-items: center;

  gap: 1rem;
}

.stat-card span {
  display: block;

  color: #6b7280;

  font-size: 0.8rem;

  margin-bottom: 0.3rem;
}

.stat-card strong {
  display: block;

  font-size: 1.5rem;

  color: #111827;
}


/* ICON */

.stat-icon {
  width: 42px;
  height: 42px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 8px;
}

.stat-icon.purple {
  background: #f3e8ff;
  color: #7e22ce;
}

.stat-icon.blue {
  background: #e0f2fe;
  color: #0284c7;
}

.stat-icon.green {
  background: #dcfce7;
  color: #16a34a;
}

.stat-icon.orange {
  background: #ffedd5;
  color: #ea580c;
}


/* CARD */

.dashboard-card {
  background-color: white;

  border: 1px solid #e5e7eb;

  border-radius: 8px;

  padding: 1.5rem;
}

.dashboard-card h2 {
  margin: 0 0 0.5rem;

  font-size: 1.1rem;

  color: #111827;
}

.dashboard-card p {
  margin: 0;

  color: #6b7280;

  font-size: 0.9rem;

  line-height: 1.6;
}


/* RESPONSIVE */

@media (max-width: 1000px) {

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

}

@media (max-width: 600px) {

  .admin-content {
    padding: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

}

</style>