<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import L from 'leaflet'

const props = defineProps({
  latitude: {
    type: [Number, String],
    default: null
  },
  longitude: {
    type: [Number, String],
    default: null
  },
  title: {
    type: String,
    default: ''
  },
  venue: {
    type: String,
    default: ''
  },
  address: {
    type: String,
    default: ''
  }
})

const mapContainer = ref(null)
let map = null
let marker = null

const defaultLat = -6.9175
const defaultLng = 107.6191

const customIcon = L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
})

const initMap = () => {
  if (!mapContainer.value) return

  const hasCoords = props.latitude && props.longitude && !isNaN(props.latitude) && !isNaN(props.longitude)
  const lat = hasCoords ? Number(props.latitude) : defaultLat
  const lng = hasCoords ? Number(props.longitude) : defaultLng

  if (map) {
    map.remove()
    map = null
  }

  map = L.map(mapContainer.value).setView([lat, lng], hasCoords ? 15 : 13)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map)

  marker = L.marker([lat, lng], { icon: customIcon }).addTo(map)

  const popupContent = `
    <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 2px;">
      <strong style="font-size: 14px; text-transform: uppercase; color: #111;">${props.title || props.venue || 'Lokasi Event'}</strong><br/>
      <span style="color: #444; font-weight: bold;">📍 ${props.venue || ''}</span>
      ${props.address ? `<br/><small style="color: #666;">${props.address}</small>` : ''}
    </div>
  `
  marker.bindPopup(popupContent).openPopup()
}

onMounted(() => {
  initMap()
})

onBeforeUnmount(() => {
  if (map) {
    map.remove()
    map = null
  }
})

watch(() => [props.latitude, props.longitude], () => {
  initMap()
})
</script>

<template>
  <div class="event-map-card">
    <div class="map-header">
      <h3 class="font-black text-sm uppercase flex items-center gap-2">
        <span>📍 LOKASI PETA EVENT</span>
      </h3>
      <span v-if="venue" class="text-xs font-bold text-muted">{{ venue }}</span>
    </div>

    <div ref="mapContainer" class="event-map-container"></div>
  </div>
</template>

<style scoped>
.event-map-card {
  width: 100%;
  border: 2px solid #111;
  box-shadow: 5px 5px 0 #111;
  background: #fff;
  overflow: hidden;
}

.map-header {
  padding: 12px 16px;
  background: #fff;
  border-bottom: 2px solid #111;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.event-map-container {
  width: 100%;
  height: 280px;
  z-index: 1;
}

@media (max-width: 640px) {
  .event-map-container {
    height: 220px;
  }
}
</style>
