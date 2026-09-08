<template>

  <transition name="flash">

    <div
      v-if="show"
      class="flash-message"
      :class="`flash-${type}`"
    >

      <!-- DECORATION -->
      <div class="flash-star">✦</div>
      <div class="flash-dot"></div>


      <!-- ICON -->
      <div class="flash-icon">

        <span>
          {{ icon }}
        </span>

      </div>


      <!-- CONTENT -->
      <div class="flash-content">

        <div class="flash-label">
          {{ label }}
        </div>

        <strong>
          {{ title }}
        </strong>

        <p>
          {{ message }}
        </p>

      </div>


      <!-- CLOSE -->
      <button
        class="flash-close"
        @click="close"
      >
        ×
      </button>


      <!-- PROGRESS -->
      <div class="flash-progress">

        <div class="flash-progress-bar"></div>

      </div>

    </div>

  </transition>

</template>


<script setup>

import {
  computed,
  watch
} from 'vue'


const props = defineProps({

  show: {
    type: Boolean,
    default: false
  },

  title: {
    type: String,
    default: 'NOTIFICATION'
  },

  message: {
    type: String,
    default: ''
  },

  type: {
    type: String,
    default: 'success'
  }

})


const emit = defineEmits([
  'close'
])


/*
|--------------------------------------------------------------------------
| LABEL
|--------------------------------------------------------------------------
*/

const label = computed(() => {

  switch (props.type) {

    case 'error':
      return 'ERROR'

    case 'warning':
      return 'WARNING'

    case 'info':
      return 'INFO'

    default:
      return 'SUCCESS'

  }

})


/*
|--------------------------------------------------------------------------
| ICON
|--------------------------------------------------------------------------
*/

const icon = computed(() => {

  switch (props.type) {

    case 'error':
      return '×'

    case 'warning':
      return '!'

    case 'info':
      return 'i'

    default:
      return '✓'

  }

})


/*
|--------------------------------------------------------------------------
| SOUND
|--------------------------------------------------------------------------
*/

const playSound = async () => {

  try {

    const AudioContext =
      window.AudioContext ||
      window.webkitAudioContext


    if (!AudioContext) {
      return
    }


    const audioContext =
      new AudioContext()


    /*
    |--------------------------------------------------------------------------
    | ERROR SOUND
    |--------------------------------------------------------------------------
    */

    if (props.type === 'error') {

      const oscillator =
        audioContext.createOscillator()

      const gainNode =
        audioContext.createGain()


      oscillator.connect(gainNode)

      gainNode.connect(
        audioContext.destination
      )


      oscillator.type = 'sawtooth'


      oscillator.frequency.setValueAtTime(
        240,
        audioContext.currentTime
      )


      oscillator.frequency.exponentialRampToValueAtTime(
        130,
        audioContext.currentTime + 0.16
      )


      gainNode.gain.setValueAtTime(
        0.0001,
        audioContext.currentTime
      )


      gainNode.gain.exponentialRampToValueAtTime(
        0.10,
        audioContext.currentTime + 0.01
      )


      gainNode.gain.exponentialRampToValueAtTime(
        0.0001,
        audioContext.currentTime + 0.22
      )


      oscillator.start()

      oscillator.stop(
        audioContext.currentTime + 0.22
      )


      oscillator.onended = () => {

        audioContext.close()

      }


      return

    }


    /*
    |--------------------------------------------------------------------------
    | WARNING SOUND
    |--------------------------------------------------------------------------
    */

    if (props.type === 'warning') {

      const oscillator =
        audioContext.createOscillator()

      const gainNode =
        audioContext.createGain()


      oscillator.connect(gainNode)

      gainNode.connect(
        audioContext.destination
      )


      oscillator.type = 'triangle'


      oscillator.frequency.setValueAtTime(
        420,
        audioContext.currentTime
      )


      oscillator.frequency.setValueAtTime(
        320,
        audioContext.currentTime + 0.12
      )


      gainNode.gain.setValueAtTime(
        0.0001,
        audioContext.currentTime
      )


      gainNode.gain.exponentialRampToValueAtTime(
        0.08,
        audioContext.currentTime + 0.01
      )


      gainNode.gain.exponentialRampToValueAtTime(
        0.0001,
        audioContext.currentTime + 0.23
      )


      oscillator.start()

      oscillator.stop(
        audioContext.currentTime + 0.23
      )


      oscillator.onended = () => {

        audioContext.close()

      }


      return

    }


    /*
    |--------------------------------------------------------------------------
    | INFO SOUND
    |--------------------------------------------------------------------------
    */

    if (props.type === 'info') {

      const oscillator =
        audioContext.createOscillator()

      const gainNode =
        audioContext.createGain()


      oscillator.connect(gainNode)

      gainNode.connect(
        audioContext.destination
      )


      oscillator.type = 'sine'


      oscillator.frequency.setValueAtTime(
        440,
        audioContext.currentTime
      )


      oscillator.frequency.setValueAtTime(
        520,
        audioContext.currentTime + 0.1
      )


      gainNode.gain.setValueAtTime(
        0.0001,
        audioContext.currentTime
      )


      gainNode.gain.exponentialRampToValueAtTime(
        0.07,
        audioContext.currentTime + 0.01
      )


      gainNode.gain.exponentialRampToValueAtTime(
        0.0001,
        audioContext.currentTime + 0.18
      )


      oscillator.start()

      oscillator.stop(
        audioContext.currentTime + 0.18
      )


      oscillator.onended = () => {

        audioContext.close()

      }


      return

    }


    /*
    |--------------------------------------------------------------------------
    | SUCCESS SOUND
    |--------------------------------------------------------------------------
    */

    const oscillator =
      audioContext.createOscillator()

    const gainNode =
      audioContext.createGain()


    oscillator.connect(gainNode)

    gainNode.connect(
      audioContext.destination
    )


    oscillator.type = 'square'


    oscillator.frequency.setValueAtTime(
      520,
      audioContext.currentTime
    )


    oscillator.frequency.exponentialRampToValueAtTime(
      850,
      audioContext.currentTime + 0.08
    )


    gainNode.gain.setValueAtTime(
      0.0001,
      audioContext.currentTime
    )


    gainNode.gain.exponentialRampToValueAtTime(
      0.10,
      audioContext.currentTime + 0.01
    )


    gainNode.gain.exponentialRampToValueAtTime(
      0.0001,
      audioContext.currentTime + 0.18
    )


    oscillator.start()

    oscillator.stop(
      audioContext.currentTime + 0.18
    )


    oscillator.onended = () => {

      audioContext.close()

    }

  } catch (error) {

    console.log(
      'Flash sound tidak dapat dimainkan:',
      error
    )

  }

}


/*
|--------------------------------------------------------------------------
| PLAY SOUND WHEN FLASH APPEARS
|--------------------------------------------------------------------------
*/

watch(

  () => props.show,

  (value) => {

    if (value) {

      playSound()

    }

  }

)


/*
|--------------------------------------------------------------------------
| CLOSE
|--------------------------------------------------------------------------
*/

const close = () => {

  emit('close')

}

</script>


<style scoped>

.flash-message {

  position: fixed;

  top: 28px;
  right: 28px;

  width: 370px;
  min-height: 115px;

  z-index: 99999;

  display: flex;
  align-items: center;

  gap: 15px;

  padding: 20px 22px;

  background: #FAF7F0;

  color: #121212;

  border: 4px solid #121212;

  box-shadow: 10px 10px 0 #121212;

  overflow: hidden;

}


.flash-success .flash-icon {
  background: #00E676;
}

.flash-success .flash-progress-bar {
  background: #7E22CE;
}

.flash-success .flash-label {
  background: #FFDE59;
}


.flash-error .flash-icon {
  background: #FF5757;
}

.flash-error .flash-progress-bar {
  background: #FF5757;
}

.flash-error .flash-label {
  background: #FF5757;
}


.flash-warning .flash-icon {
  background: #FFDE59;
}

.flash-warning .flash-progress-bar {
  background: #FFDE59;
}

.flash-warning .flash-label {
  background: #FFDE59;
}


.flash-info .flash-icon {
  background: #00F0FF;
}

.flash-info .flash-progress-bar {
  background: #00F0FF;
}

.flash-info .flash-label {
  background: #00F0FF;
}


.flash-icon {

  position: relative;

  z-index: 3;

  width: 52px;
  height: 52px;

  flex-shrink: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  border: 3px solid #121212;

  box-shadow: 5px 5px 0 #121212;

  animation:
    icon-pop
    .6s
    cubic-bezier(.17,.67,.35,1.5);

}


.flash-icon span {

  font-family:
    'Space Grotesk',
    sans-serif;

  font-size: 28px;

  font-weight: 900;

}


.flash-content {

  position: relative;

  z-index: 3;

  flex: 1;

}


.flash-label {

  display: inline-block;

  margin-bottom: 4px;

  padding: 2px 7px;

  border: 2px solid #121212;

  font-family:
    'Space Grotesk',
    sans-serif;

  font-size: 8px;

  font-weight: 900;

  letter-spacing: 1px;

}


.flash-content strong {

  display: block;

  font-family:
    'Space Grotesk',
    sans-serif;

  font-size: 15px;

  font-weight: 900;

}


.flash-content p {

  margin: 3px 0 0;

  font-size: 11px;

  font-weight: 600;

  line-height: 1.4;

}


.flash-close {

  position: absolute;

  top: 8px;
  right: 10px;

  z-index: 5;

  width: 25px;
  height: 25px;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 0;

  background: transparent;

  border: none;

  font-size: 23px;

  font-weight: 900;

  cursor: pointer;

}


.flash-close:hover {

  transform:
    rotate(10deg)
    scale(1.2);

}


.flash-progress {

  position: absolute;

  bottom: 0;
  left: 0;

  width: 100%;
  height: 7px;

  background: #121212;

}


.flash-progress-bar {

  width: 100%;
  height: 100%;

  transform-origin: left;

  animation:
    progress
    3s
    linear
    forwards;

}


.flash-star {

  position: absolute;

  top: -18px;
  right: 55px;

  font-size: 55px;

  font-weight: 900;

  opacity: .08;

  animation:
    star-spin
    4s
    linear
    infinite;

}


.flash-dot {

  position: absolute;

  right: 18px;
  bottom: 15px;

  width: 8px;
  height: 8px;

  background: #7E22CE;

  border: 2px solid #121212;

  border-radius: 50%;

  animation:
    dot-pulse
    1s
    ease-in-out
    infinite;

}


.flash-enter-active {

  animation:
    flash-slam
    .75s
    cubic-bezier(.16,1,.3,1);

}


@keyframes flash-slam {

  0% {

    opacity: 0;

    transform:
      translateX(500px)
      rotate(12deg)
      scale(.3);

  }

  40% {

    opacity: 1;

    transform:
      translateX(-35px)
      rotate(-5deg)
      scale(1.15);

  }

  60% {

    transform:
      translateX(18px)
      rotate(3deg)
      scale(.96);

  }

  75% {

    transform:
      translateX(-8px)
      rotate(-1deg)
      scale(1.04);

  }

  100% {

    opacity: 1;

    transform:
      translateX(0)
      rotate(0)
      scale(1);

  }

}


.flash-leave-active {

  animation:
    flash-out
    .4s
    ease-in
    forwards;

}


@keyframes flash-out {

  from {

    opacity: 1;

    transform:
      translateX(0)
      rotate(0)
      scale(1);

  }

  to {

    opacity: 0;

    transform:
      translateX(160px)
      rotate(8deg)
      scale(.75);

  }

}


@keyframes icon-pop {

  0% {

    transform:
      scale(0)
      rotate(-30deg);

  }

  55% {

    transform:
      scale(1.25)
      rotate(8deg);

  }

  75% {

    transform:
      scale(.9)
      rotate(-3deg);

  }

  100% {

    transform:
      scale(1)
      rotate(0);

  }

}


@keyframes progress {

  from {
    transform: scaleX(1);
  }

  to {
    transform: scaleX(0);
  }

}


@keyframes star-spin {

  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }

}


@keyframes dot-pulse {

  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.5);
  }

}


@media (max-width: 600px) {

  .flash-message {

    top: 15px;

    left: 15px;

    right: 15px;

    width: auto;

    box-shadow:
      7px 7px 0 #121212;

  }

}

</style>