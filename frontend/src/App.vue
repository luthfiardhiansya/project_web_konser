<template>

  <FlashMessage
    :show="flash.show"
    :title="flash.title"
    :message="flash.message"
    :type="flash.type"
    @close="closeFlash"
  />

  <router-view />

</template>


<script>

import FlashMessage from './components/FlashMessage.vue'


export default {

  name: 'App',


  components: {
    FlashMessage
  },


  data() {

    return {

      flash: {

        show: false,

        title: '',

        message: '',

        type: 'success'

      },

      flashTimer: null

    }

  },


  mounted() {

    window.addEventListener(
      'show-flash',
      this.handleFlash
    )

  },


  beforeUnmount() {

    window.removeEventListener(
      'show-flash',
      this.handleFlash
    )


    if (this.flashTimer) {

      clearTimeout(
        this.flashTimer
      )

    }

  },


  methods: {

    handleFlash(event) {

      if (this.flashTimer) {

        clearTimeout(
          this.flashTimer
        )

      }


      this.flash.title =
        event.detail.title ||
        'NOTIFICATION'


      this.flash.message =
        event.detail.message ||
        ''


      this.flash.type =
        event.detail.type ||
        'success'


      this.flash.show = false


      this.$nextTick(() => {

        this.flash.show = true

      })


      this.flashTimer =
        setTimeout(() => {

          this.flash.show = false

        }, 3000)

    },


    closeFlash() {

      this.flash.show = false


      if (this.flashTimer) {

        clearTimeout(
          this.flashTimer
        )

      }

    }

  }

}

</script>