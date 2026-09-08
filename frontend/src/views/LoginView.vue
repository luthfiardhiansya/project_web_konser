<template>

  <div class="login-page">

    <!-- HOME -->
    <button
      class="home-button"
      @click="$router.push('/')"
    >
      ← HOME
    </button>


    <div class="login-card">

      <!-- HEADER -->
      <div class="login-header">

        <div class="badge">
          INFOMUSIKBDG
        </div>

        <h1>
          WELCOME
          <span>BACK.</span>
        </h1>

        <p>
          Login untuk melanjutkan ke akunmu.
        </p>

      </div>


      <!-- FORM -->
      <form
        @submit.prevent="login"
        class="login-form"
      >

        <!-- EMAIL -->
        <div class="form-group">

          <label>
            EMAIL
          </label>

          <input
            v-model="email"
            type="email"
            placeholder="nama@email.com"
            autocomplete="email"
          />

        </div>


        <!-- PASSWORD -->
        <div class="form-group">

          <label>
            PASSWORD
          </label>

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
              {{ showPassword ? 'HIDE' : 'SHOW' }}
            </button>

          </div>

        </div>


        <!-- LOGIN -->
        <button
          type="submit"
          class="login-button"
          :disabled="loading"
        >

          {{ loading ? 'LOGIN...' : 'LOGIN →' }}

        </button>

      </form>


      <!-- REGISTER -->
      <div class="register-link">

        <span>
          Belum punya akun?
        </span>

        <button
          @click="$router.push('/register')"
        >
          REGISTER
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

    /*
    |--------------------------------------------------------------------------
    | FLASH
    |--------------------------------------------------------------------------
    */

    showFlash(
      title,
      message,
      type = 'success'
    ) {

      window.dispatchEvent(

        new CustomEvent(
          'show-flash',
          {
            detail: {

              title,

              message,

              type

            }
          }
        )

      )

    },


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    async login() {

      /*
      | VALIDASI
      */

      if (
        !this.email ||
        !this.password
      ) {

        this.showFlash(

          'DATA BELUM LENGKAP!',

          'Email dan password wajib diisi.',

          'warning'

        )

        return

      }


      this.loading = true


      try {

        /*
        | REQUEST LOGIN
        */

        const response =
          await api.post(
            '/login',
            {

              email: this.email,

              password: this.password

            }
          )


        /*
        | AMBIL DATA
        */

        const data =
          response.data.data


        /*
        | SIMPAN TOKEN
        */

        localStorage.setItem(
          'token',
          data.token
        )


        /*
        | SIMPAN USER
        */

        localStorage.setItem(
          'user',
          JSON.stringify(
            data.user
          )
        )


        /*
        | FLASH SUCCESS
        */

        this.showFlash(

          'LOGIN BERHASIL!',

          `Selamat datang kembali, ${data.user.name}.`,

          'success'

        )


        /*
        | PINDAH HOME
        */

        setTimeout(() => {

          this.$router.push('/')

        }, 500)


      } catch (error) {

        /*
        |--------------------------------------------------------------------------
        | EMAIL / PASSWORD SALAH
        |--------------------------------------------------------------------------
        */

        if (
          error.response?.status === 401
        ) {

          this.showFlash(

            'LOGIN GAGAL!',

            'Email atau password salah.',

            'error'

          )


        /*
        |--------------------------------------------------------------------------
        | VALIDATION ERROR
        |--------------------------------------------------------------------------
        */

        } else if (
          error.response?.status === 422
        ) {

          this.showFlash(

            'DATA TIDAK VALID!',

            'Periksa kembali email dan password kamu.',

            'warning'

          )


        /*
        |--------------------------------------------------------------------------
        | SERVER ERROR
        |--------------------------------------------------------------------------
        */

        } else {

          this.showFlash(

            'SERVER ERROR!',

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

  padding: 30px;

  background: #FAF7F0;

}


.login-card {

  width: 100%;

  max-width: 480px;

  padding: 40px;

  background: white;

  border: 4px solid #121212;

  box-shadow:
    10px 10px 0 #121212;

}


.home-button {

  position: fixed;

  top: 25px;
  left: 25px;

  padding: 10px 16px;

  background: #FFDE59;

  border: 3px solid #121212;

  box-shadow:
    4px 4px 0 #121212;

  font-weight: 900;

  cursor: pointer;

}


.home-button:active {

  transform:
    translate(4px, 4px);

  box-shadow:
    0 0 0 #121212;

}


.badge {

  display: inline-block;

  padding: 6px 10px;

  background: #A855F7;

  border: 3px solid #121212;

  font-size: 11px;

  font-weight: 900;

}


.login-header h1 {

  margin: 18px 0 5px;

  font-family:
    'Space Grotesk',
    sans-serif;

  font-size: 48px;

  line-height: .9;

  font-weight: 900;

}


.login-header h1 span {

  color: #7E22CE;

}


.login-header p {

  margin-bottom: 30px;

  font-size: 14px;

  font-weight: 600;

}


.form-group {

  margin-bottom: 20px;

}


.form-group label {

  display: block;

  margin-bottom: 7px;

  font-size: 11px;

  font-weight: 900;

}


.form-group input {

  width: 100%;

  padding: 14px;

  background: #FAF7F0;

  border: 3px solid #121212;

  outline: none;

  font-size: 14px;

}


.form-group input:focus {

  box-shadow:
    5px 5px 0 #7E22CE;

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

  transform:
    translateY(-50%);

  padding: 5px 7px;

  background: #FFDE59;

  border: 2px solid #121212;

  font-size: 9px;

  font-weight: 900;

  cursor: pointer;

}


.login-button {

  width: 100%;

  margin-top: 8px;

  padding: 15px;

  background: #7E22CE;

  color: white;

  border: 3px solid #121212;

  box-shadow:
    6px 6px 0 #121212;

  font-weight: 900;

  cursor: pointer;

}


.login-button:hover {

  background: #A855F7;

}


.login-button:active {

  transform:
    translate(6px, 6px);

  box-shadow:
    0 0 0 #121212;

}


.login-button:disabled {

  opacity: .6;

  cursor: not-allowed;

}


.register-link {

  display: flex;

  justify-content: center;

  gap: 7px;

  margin-top: 25px;

  font-size: 12px;

  font-weight: 600;

}


.register-link button {

  padding: 0;

  background: transparent;

  border: none;

  color: #7E22CE;

  font-weight: 900;

  cursor: pointer;

}


@media (max-width: 600px) {

  .login-page {

    padding: 20px;

  }


  .login-card {

    padding: 25px;

  }


  .login-header h1 {

    font-size: 38px;

  }


  .home-button {

    position: absolute;

    top: 15px;

    left: 15px;

  }

}

</style>