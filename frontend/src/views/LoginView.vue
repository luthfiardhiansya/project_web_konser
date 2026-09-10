<template>
  <div class="login-page">

    <button
      class="home-button"
      @click="$router.push('/')"
    >
      ← Home
    </button>

    <div class="login-card">

      <div class="login-header">
        <h1>Login</h1>
        <p>Masuk ke akun InfoMusikBDG kamu.</p>
      </div>

      <form
        @submit.prevent="login"
        class="login-form"
      >

        <!-- EMAIL -->
        <div class="form-group">
          <label>Email</label>

          <input
            v-model="email"
            type="email"
            placeholder="nama@email.com"
            autocomplete="email"
          />
        </div>

        <!-- PASSWORD -->
        <div class="form-group">
          <label>Password</label>

          <div class="password-wrapper">

            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Masukkan password"
              autocomplete="current-password"
            />

            <button
              type="button"
              class="password-button"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? 'Sembunyikan' : 'Lihat' }}
            </button>

          </div>
        </div>

        <!-- LOGIN -->
        <button
          type="submit"
          class="login-button"
          :disabled="loading"
        >
          {{ loading ? 'Memproses...' : 'Login' }}
        </button>

      </form>

      <!-- PEMISAH -->
      <div class="divider">
        <span>atau</span>
      </div>

      <!-- GOOGLE -->
      <button
        type="button"
        class="google-button"
        @click="loginWithGoogle"
      >
        <span class="google-icon">G</span>
        Login dengan Google
      </button>

      <!-- REGISTER -->
      <div class="register-link">
        <span>Belum punya akun?</span>

        <button
          type="button"
          @click="$router.push('/register')"
        >
          Daftar
        </button>
      </div>

    </div>
  </div>
</template>


<script>

import api from '../utils/api'

export default {

  name: 'LoginView',

  data() {
    return {
      email: '',
      password: '',
      showPassword: false,
      loading: false
    }
  },

  methods: {

    showFlash(title, message, type = 'success') {

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


    /*
    | LOGIN GOOGLE
    */

    loginWithGoogle() {

      window.location.href =
        'http://localhost:8000/api/auth/google'

    },


    /*
    | LOGIN EMAIL
    */

    async login() {

      if (!this.email || !this.password) {

        this.showFlash(
          'Data belum lengkap',
          'Email dan password wajib diisi.',
          'warning'
        )

        return

      }

      this.loading = true

      try {

        const response = await api.post(
          '/login',
          {
            email: this.email,
            password: this.password
          }
        )

        const data = response.data.data

        localStorage.setItem(
          'token',
          data.token
        )

        localStorage.setItem(
          'user',
          JSON.stringify(data.user)
        )

        this.showFlash(
          'Login berhasil',
          `Selamat datang kembali, ${data.user.name}.`,
          'success'
        )

        setTimeout(() => {
          this.$router.push('/')
        }, 500)

      } catch (error) {

        if (error.response?.status === 401) {

          this.showFlash(
            'Login gagal',
            'Email atau password salah.',
            'error'
          )

        } else if (error.response?.status === 422) {

          this.showFlash(
            'Data tidak valid',
            'Periksa kembali email dan password.',
            'warning'
          )

        } else {

          this.showFlash(
            'Server error',
            'Tidak dapat terhubung ke server.',
            'error'
          )

        }

      } finally {

        this.loading = false

      }

    }

  }

}

</script>


<style scoped>

.login-page {
  min-height: 100vh;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 20px;

  background: #f5f5f5;
}


.login-card {
  width: 100%;
  max-width: 400px;

  padding: 32px;

  background: white;

  border: 1px solid #ddd;
  border-radius: 8px;

  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}


.home-button {
  position: fixed;

  top: 20px;
  left: 20px;

  padding: 8px 12px;

  background: white;

  border: 1px solid #ccc;
  border-radius: 6px;

  font-size: 14px;

  cursor: pointer;
}


.home-button:hover {
  background: #f0f0f0;
}


.login-header {
  text-align: center;

  margin-bottom: 28px;
}


.login-header h1 {
  margin: 0 0 8px;

  font-size: 30px;
  font-weight: 700;

  color: #111;
}


.login-header p {
  margin: 0;

  font-size: 14px;

  color: #666;
}


.form-group {
  margin-bottom: 18px;
}


.form-group label {
  display: block;

  margin-bottom: 7px;

  font-size: 14px;
  font-weight: 600;

  color: #333;
}


.form-group input {
  width: 100%;

  box-sizing: border-box;

  padding: 12px;

  background: white;

  border: 1px solid #ccc;
  border-radius: 6px;

  outline: none;

  font-size: 14px;
}


.form-group input:focus {
  border-color: #333;
}


.password-wrapper {
  position: relative;
}


.password-wrapper input {
  padding-right: 75px;
}


.password-button {
  position: absolute;

  right: 8px;
  top: 50%;

  transform: translateY(-50%);

  padding: 5px 7px;

  background: transparent;

  border: none;

  color: #555;

  font-size: 12px;

  cursor: pointer;
}


.login-button {
  width: 100%;

  padding: 12px;

  background: #111;

  color: white;

  border: none;
  border-radius: 6px;

  font-size: 14px;
  font-weight: 600;

  cursor: pointer;
}


.login-button:hover {
  background: #333;
}


.login-button:disabled {
  opacity: 0.6;

  cursor: not-allowed;
}


.divider {
  display: flex;
  align-items: center;

  gap: 12px;

  margin: 22px 0;

  color: #999;

  font-size: 13px;
}


.divider::before,
.divider::after {
  content: '';

  flex: 1;

  height: 1px;

  background: #ddd;
}


.google-button {
  width: 100%;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 10px;

  padding: 11px;

  background: white;

  border: 1px solid #ccc;
  border-radius: 6px;

  font-size: 14px;
  font-weight: 500;

  cursor: pointer;
}


.google-button:hover {
  background: #f7f7f7;
}


.google-icon {
  font-size: 17px;
  font-weight: 700;

  color: #4285f4;
}


.register-link {
  display: flex;

  justify-content: center;

  gap: 6px;

  margin-top: 22px;

  font-size: 13px;

  color: #666;
}


.register-link button {
  padding: 0;

  background: transparent;

  border: none;

  color: #111;

  font-weight: 600;

  cursor: pointer;
}


.register-link button:hover {
  text-decoration: underline;
}


@media (max-width: 600px) {

  .login-card {
    padding: 24px;
  }

  .home-button {
    position: absolute;

    top: 15px;
    left: 15px;
  }

}

</style>