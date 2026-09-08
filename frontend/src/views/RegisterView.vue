<template>

  <div class="register-page">

    <!-- HOME -->
    <button
      class="home-button"
      @click="$router.push('/')"
    >
      ← HOME
    </button>


    <div class="register-card">

      <!-- HEADER -->
      <div class="register-header">

        <div class="badge">
          INFOMUSIKBDG
        </div>

        <h1>
          JOIN
          <span>US.</span>
        </h1>

        <p>
          Buat akun baru untuk menikmati
          semua fitur InfoMusikBDG.
        </p>

      </div>


      <!-- FORM -->
      <form
        @submit.prevent="register"
        class="register-form"
      >

        <!-- NAME -->
        <div class="form-group">

          <label>
            NAMA
          </label>

          <input
            v-model="name"
            type="text"
            placeholder="Nama kamu"
            autocomplete="name"
          />

        </div>


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
              :type="
                showPassword
                  ? 'text'
                  : 'password'
              "
              placeholder="Minimal 6 karakter"
              autocomplete="new-password"
            />

            <button
              type="button"
              class="password-button"
              @click="
                showPassword =
                  !showPassword
              "
            >
              {{
                showPassword
                  ? 'HIDE'
                  : 'SHOW'
              }}
            </button>

          </div>

        </div>


        <!-- CONFIRM -->
        <div class="form-group">

          <label>
            KONFIRMASI PASSWORD
          </label>

          <div class="password-wrapper">

            <input
              v-model="password_confirmation"
              :type="
                showConfirmPassword
                  ? 'text'
                  : 'password'
              "
              placeholder="Ulangi password"
              autocomplete="new-password"
            />

            <button
              type="button"
              class="password-button"
              @click="
                showConfirmPassword =
                  !showConfirmPassword
              "
            >
              {{
                showConfirmPassword
                  ? 'HIDE'
                  : 'SHOW'
              }}
            </button>

          </div>

        </div>


        <!-- REGISTER BUTTON -->
        <button
          type="submit"
          class="register-button"
          :disabled="loading"
        >

          {{
            loading
              ? 'CREATING...'
              : 'CREATE ACCOUNT →'
          }}

        </button>

      </form>


      <!-- LOGIN -->
      <div class="login-link">

        <span>
          Sudah punya akun?
        </span>

        <button
          @click="$router.push('/login')"
        >
          LOGIN
        </button>

      </div>

    </div>

  </div>

</template>


<script>

import api from '../utils/api'


export default {

  name: 'RegisterView',


  data() {

    return {

      name: '',

      email: '',

      password: '',

      password_confirmation: '',

      showPassword: false,

      showConfirmPassword: false,

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
    | REGISTER
    |--------------------------------------------------------------------------
    */

    async register() {

      /*
      | CEK KOSONG
      */

      if (
        !this.name ||
        !this.email ||
        !this.password ||
        !this.password_confirmation
      ) {

        this.showFlash(

          'DATA BELUM LENGKAP!',

          'Semua kolom wajib diisi.',

          'warning'

        )

        return

      }


      /*
      | CEK PASSWORD
      */

      if (
        this.password !==
        this.password_confirmation
      ) {

        this.showFlash(

          'PASSWORD TIDAK SAMA!',

          'Pastikan password dan konfirmasi password sama.',

          'error'

        )

        return

      }


      /*
      | CEK PANJANG PASSWORD
      */

      if (
        this.password.length < 6
      ) {

        this.showFlash(

          'PASSWORD TERLALU PENDEK!',

          'Password harus minimal 6 karakter.',

          'warning'

        )

        return

      }


      this.loading = true


      try {

        /*
        | REQUEST REGISTER
        */

        const response =
          await api.post(
            '/register',
            {

              name:
                this.name,

              email:
                this.email,

              password:
                this.password,

              password_confirmation:
                this.password_confirmation

            }
          )


        /*
        | AMBIL DATA
        */

        const data =
          response.data.data


        /*
        | BACKEND KAMU LANGSUNG
        | MEMBERIKAN TOKEN
        */

        if (data?.token) {

          localStorage.setItem(
            'token',
            data.token
          )

        }


        /*
        | SIMPAN USER
        */

        if (data?.user) {

          localStorage.setItem(
            'user',
            JSON.stringify(
              data.user
            )
          )

        }


        /*
        | FLASH SUCCESS
        */

        this.showFlash(

          'REGISTER BERHASIL!',

          'Akun berhasil dibuat. Selamat datang di InfoMusikBDG!',

          'success'

        )


        /*
        | PINDAH HOME
        */

        setTimeout(() => {

          this.$router.push('/')

        }, 700)


      } catch (error) {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION ERROR
        |--------------------------------------------------------------------------
        */

        if (
          error.response?.status === 422
        ) {

          const errors =
            error.response.data.errors


          /*
          | EMAIL SUDAH TERDAFTAR
          */

          if (
            errors?.email
          ) {

            this.showFlash(

              'EMAIL SUDAH TERDAFTAR!',

              errors.email[0],

              'error'

            )

          } else {

            this.showFlash(

              'DATA TIDAK VALID!',

              'Periksa kembali data pendaftaran kamu.',

              'error'

            )

          }


        /*
        |--------------------------------------------------------------------------
        | SERVER ERROR
        |--------------------------------------------------------------------------
        */

        } else {

          this.showFlash(

            'REGISTER GAGAL!',

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

.register-page {

  min-height: 100vh;

  display: flex;

  align-items: center;

  justify-content: center;

  padding: 30px;

  background: #FAF7F0;

}


.register-card {

  width: 100%;

  max-width: 500px;

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


.register-header h1 {

  margin: 18px 0 5px;

  font-family:
    'Space Grotesk',
    sans-serif;

  font-size: 48px;

  line-height: .9;

  font-weight: 900;

}


.register-header h1 span {

  color: #7E22CE;

}


.register-header p {

  margin-bottom: 28px;

  font-size: 13px;

  font-weight: 600;

  line-height: 1.5;

}


.form-group {

  margin-bottom: 17px;

}


.form-group label {

  display: block;

  margin-bottom: 7px;

  font-size: 11px;

  font-weight: 900;

}


.form-group input {

  width: 100%;

  padding: 13px;

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


.register-button {

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


.register-button:hover {

  background: #A855F7;

}


.register-button:active {

  transform:
    translate(6px, 6px);

  box-shadow:
    0 0 0 #121212;

}


.register-button:disabled {

  opacity: .6;

  cursor: not-allowed;

}


.login-link {

  display: flex;

  justify-content: center;

  gap: 7px;

  margin-top: 25px;

  font-size: 12px;

  font-weight: 600;

}


.login-link button {

  padding: 0;

  background: transparent;

  border: none;

  color: #7E22CE;

  font-weight: 900;

  cursor: pointer;

}


@media (max-width: 600px) {

  .register-page {

    padding: 20px;

  }


  .register-card {

    padding: 25px;

  }


  .register-header h1 {

    font-size: 38px;

  }


  .home-button {

    position: absolute;

    top: 15px;

    left: 15px;

  }

}

</style>