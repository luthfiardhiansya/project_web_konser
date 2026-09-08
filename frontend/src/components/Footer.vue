<script setup>
const emit = defineEmits(['navigate'])

const onNavClick = (viewName) => {
  emit('navigate', viewName)
}
</script>

<template>
  <footer class="bg-ink text-paper nb-border-t py-8 px-4 sm:px-6 lg:px-8 mt-12">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
      <div>
        <div class="font-black text-xl tracking-tighter">INFO MUSIK BDG</div>
        <p class="text-xs text-paper/70 font-medium">Temukan Musikmu di Bandung. Platform ticketing & direktori gigs lokal.</p>
      </div>
      <div class="text-xs font-bold uppercase space-x-4">
        <button @click="onNavClick('view-home')" class="hover:underline">Beranda</button>
        <button @click="onNavClick('view-events')" class="hover:underline">Event</button>
        <button @click="onNavClick('view-organizer-reg')" class="hover:underline">Organizer</button>
      </div>
      <div class="text-[11px] text-paper/50 font-mono">
        © 2026 INFO MUSIK BDG. ALL RIGHTS RESERVED.
      </div>
    </div>
  </footer>
</template>
