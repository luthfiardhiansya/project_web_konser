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

      <!-- GOOGLE -->
      <div class="google-register">

  <div class="divider">
    <span>atau</span>
  </div>

  <button
    type="button"
    class="google-button"
    @click="loginWithGoogle"
  >
    <svg
      class="google-icon"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <path
        fill="#4285F4"
        d="M21.35 12.27c0-.79-.07-1.55-.23-2.27H12v4.3h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.7 2.91-4.21 2.91-7.42Z"
      />
      <path
        fill="#34A853"
        d="M12 21.99c2.63 0 4.84-.87 6.45-2.35l-3.14-2.45c-.87.58-1.98.92-3.31.92-2.54 0-4.69-1.72-5.46-4.03H3.3v2.53A9.75 9.75 0 0 0 12 21.99Z"
      />
      <path
        fill="#FBBC05"
        d="M6.54 14.08A5.86 5.86 0 0 1 6.23 12c0-.72.12-1.42.31-2.08V7.39H3.3A9.97 9.97 0 0 0 2.25 12c0 1.66.4 3.23 1.05 4.61l3.24-2.53Z"
      />
      <path
        fill="#EA4335"
        d="M12 5.89c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 2.91 14.63 2 12 2a9.75 9.75 0 0 0-8.7 5.39l3.24 2.53C7.31 7.61 9.46 5.89 12 5.89Z"
      />
    </svg>

    <span>Login dengan Google</span>
  </button>

</div>

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
          if (data.user.role === 'scanner') {
          this.$router.push('/scanner')
        } else {
          this.$router.push('/')
        }
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

.google-register {
  margin-top: 18px;
}

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 18px 0;
  color: #999;
  font-size: 12px;
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

  padding: 12px;

  background: white;
  color: #111;

  border: 1px solid #ccc;
  border-radius: 6px;

  font-size: 14px;
  font-weight: 600;

  cursor: pointer;

  transition:
    background 0.15s ease,
    border-color 0.15s ease;
}

.google-button:hover {
  background: #f7f7f7;
  border-color: #999;
}

.google-icon {
  width: 19px;
  height: 19px;
  flex-shrink: 0;
}

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