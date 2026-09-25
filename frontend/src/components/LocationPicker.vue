<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  venueName: {
    type: String,
    default: ''
  },

  venueAddress: {
    type: String,
    default: ''
  },

  venueLatitude: {
    type: [Number, String],
    default: -6.9175
  },

  venueLongitude: {
    type: [Number, String],
    default: 107.6191
  },

  scanLatitude: {
    type: [Number, String],
    default: -6.9175
  },

  scanLongitude: {
    type: [Number, String],
    default: 107.6191
  },

  radius: {
    type: [Number, String],
    default: 30
  }
})

const emit = defineEmits([
  'update:scanLatitude',
  'update:scanLongitude',
  'update:radius',
  'update:venueLatitude',
  'update:venueLongitude',
  'select-venue',
  'select-location'
])

const mapContainer = ref(null)
const searchQuery = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
const searchError = ref('')

let map = null
let venueMarker = null
let scanMarker = null
let radiusCircle = null
let searchController = null

const venueLat = ref(Number(props.venueLatitude))
const venueLng = ref(Number(props.venueLongitude))

const scanLat = ref(Number(props.scanLatitude))
const scanLng = ref(Number(props.scanLongitude))

const radiusValue = ref(Number(props.radius) || 30)

searchQuery.value = props.venueAddress || props.venueName

const reverseGeocode = async (lat, lng) => {
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
      { headers: { 'Accept-Language': 'id' } }
    )

    if (!response.ok) return null

    const result = await response.json()
    const address = result.address || {}

    return {
      name: result.name || address.amenity || address.building || address.road || '',
      address: result.display_name || ''
    }
  } catch (error) {
    console.error('Gagal mengambil detail lokasi:', error)
    return null
  }
}

const moveScanToVenue = (lat, lng) => {
  scanLat.value = lat
  scanLng.value = lng

  if (scanMarker) scanMarker.setLatLng([lat, lng])
  updateRadiusCircle()

  emit('update:scanLatitude', lat)
  emit('update:scanLongitude', lng)
}

const selectVenueLocation = (lat, lng, details = {}) => {
  venueLat.value = lat
  venueLng.value = lng

  if (venueMarker) venueMarker.setLatLng([lat, lng])
  moveScanToVenue(lat, lng)
  if (map) map.setView([lat, lng], 17, { animate: true })

  emit('update:venueLatitude', lat)
  emit('update:venueLongitude', lng)

  const displayName = details.name || details.address || 'Lokasi Venue'
  const location = {
    type: 'venue',
    lat,
    lng,
    name: details.name || '',
    address: details.address || '',
    displayName
  }

  emit('select-venue', location)
  emit('select-location', location)
}

const searchLocations = async () => {
  const query = searchQuery.value.trim()

  if (!query) {
    searchResults.value = []
    searchError.value = 'Masukkan nama lokasi atau alamat terlebih dahulu.'
    return
  }

  searchLoading.value = true
  searchError.value = ''
  searchResults.value = []
  searchController?.abort()
  searchController = new AbortController()

  try {
    const params = new URLSearchParams({
      q: query,
      format: 'jsonv2',
      addressdetails: '1',
      limit: '5'
    })
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?${params}`,
      {
        signal: searchController.signal,
        headers: { 'Accept-Language': 'id' }
      }
    )

    if (!response.ok) throw new Error('Search request failed')

    searchResults.value = await response.json()
    if (searchResults.value.length === 0) {
      searchError.value = 'Lokasi tidak ditemukan. Coba kata kunci lain.'
    }
  } catch (error) {
    if (error.name !== 'AbortError') {
      searchError.value = 'Pencarian lokasi gagal. Silakan coba lagi.'
    }
  } finally {
    searchLoading.value = false
  }
}

const chooseSearchResult = (result) => {
  const lat = Number(result.lat)
  const lng = Number(result.lon)

  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return

  const name = result.name || result.display_name?.split(',')[0] || ''
  selectVenueLocation(lat, lng, {
    name,
    address: result.display_name || ''
  })

  searchQuery.value = result.display_name || name
  searchResults.value = []
  searchError.value = ''
}

/*
|--------------------------------------------------------------------------
| ICON VENUE
|--------------------------------------------------------------------------
*/

const venueIcon = L.divIcon({
  className: 'custom-map-icon',
  html: `
    <div class="venue-marker">
      <div class="venue-marker-pin">
        <span>🏟️</span>
      </div>
      <div class="venue-marker-label">
        VENUE
      </div>
    </div>
  `,
  iconSize: [76, 76],
  iconAnchor: [38, 68]
})

/*
|--------------------------------------------------------------------------
| ICON SCAN
|--------------------------------------------------------------------------
*/

const scanIcon = L.divIcon({
  className: 'custom-map-icon',
  html: `
    <div class="scan-marker">
      <div class="scan-marker-pin">
        <span>🚪</span>
      </div>
      <div class="scan-marker-label">
        SCAN
      </div>
    </div>
  `,
  iconSize: [76, 76],
  iconAnchor: [38, 68]
})

/*
|--------------------------------------------------------------------------
| INIT MAP
|--------------------------------------------------------------------------
*/

const initMap = async () => {
  await nextTick()

  if (!mapContainer.value) return

  const initialVenueLat = Number.isFinite(venueLat.value)
    ? venueLat.value
    : -6.9175

  const initialVenueLng = Number.isFinite(venueLng.value)
    ? venueLng.value
    : 107.6191

  const initialScanLat = Number.isFinite(scanLat.value)
    ? scanLat.value
    : initialVenueLat

  const initialScanLng = Number.isFinite(scanLng.value)
    ? scanLng.value
    : initialVenueLng

  map = L.map(mapContainer.value, {
    zoomControl: true,
    attributionControl: true
  }).setView(
    [initialVenueLat, initialVenueLng],
    16
  )

  /*
  |--------------------------------------------------------------------------
  | OPENSTREETMAP
  |--------------------------------------------------------------------------
  */

  L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
      maxZoom: 20,
      attribution: '&copy; OpenStreetMap contributors'
    }
  ).addTo(map)

  /*
  |--------------------------------------------------------------------------
  | VENUE MARKER
  |--------------------------------------------------------------------------
  */

  venueMarker = L.marker(
    [initialVenueLat, initialVenueLng],
    {
      icon: venueIcon,
      draggable: true,
      zIndexOffset: 500
    }
  ).addTo(map)

  venueMarker.bindPopup(`
    <div class="map-popup">
      <strong>🏟️ Lokasi Venue</strong>
      <br>
      <span>Geser marker untuk mengubah lokasi venue.</span>
    </div>
  `)

  venueMarker.on('dragend', handleVenueDrag)

  /*
  |--------------------------------------------------------------------------
  | SCAN MARKER
  |--------------------------------------------------------------------------
  */

  scanMarker = L.marker(
    [initialScanLat, initialScanLng],
    {
      icon: scanIcon,
      draggable: true,
      zIndexOffset: 1000
    }
  ).addTo(map)

  scanMarker.bindPopup(`
    <div class="map-popup">
      <strong>🚪 Titik Scan / Pintu Masuk</strong>
      <br>
      <span>Geser marker ini ke lokasi pintu masuk.</span>
    </div>
  `)

  scanMarker.on('dragend', handleScanDrag)

  /*
  |--------------------------------------------------------------------------
  | RADIUS
  |--------------------------------------------------------------------------
  */

  radiusCircle = L.circle(
    [initialScanLat, initialScanLng],
    {
      radius: radiusValue.value,
      color: '#7c3aed',
      weight: 2,
      opacity: 0.8,
      fillColor: '#7c3aed',
      fillOpacity: 0.12
    }
  ).addTo(map)

  /*
  |--------------------------------------------------------------------------
  | MAP CLICK
  |--------------------------------------------------------------------------
  */

  map.on('click', handleMapClick)

  /*
  |--------------------------------------------------------------------------
  | INVALIDATE SIZE
  |--------------------------------------------------------------------------
  */

  setTimeout(() => {
    if (map) {
      map.invalidateSize()
    }
  }, 200)

  fitMapToMarkers()
}

/*
|--------------------------------------------------------------------------
| VENUE DRAG
|--------------------------------------------------------------------------
*/

const handleVenueDrag = (event) => {
  const position = event.target.getLatLng()

  selectVenueLocation(position.lat, position.lng)
  reverseGeocode(position.lat, position.lng).then((details) => {
    if (details) selectVenueLocation(position.lat, position.lng, details)
  })
}

/*
|--------------------------------------------------------------------------
| SCAN DRAG
|--------------------------------------------------------------------------
*/

const handleScanDrag = (event) => {
  const position = event.target.getLatLng()

  scanLat.value = position.lat
  scanLng.value = position.lng

  updateRadiusCircle()

  emit('update:scanLatitude', position.lat)
  emit('update:scanLongitude', position.lng)

  emit('select-location', {
    type: 'scan',
    lat: position.lat,
    lng: position.lng,
    displayName: 'Titik Scan / Pintu Masuk'
  })
}

/*
|--------------------------------------------------------------------------
| MAP CLICK
|--------------------------------------------------------------------------
*/

const handleMapClick = (event) => {
  if (!scanMarker) return

  const lat = event.latlng.lat
  const lng = event.latlng.lng

  scanLat.value = lat
  scanLng.value = lng

  scanMarker.setLatLng([lat, lng])

  updateRadiusCircle()

  emit('update:scanLatitude', lat)
  emit('update:scanLongitude', lng)

  emit('select-location', {
    type: 'scan',
    lat,
    lng,
    displayName: 'Titik Scan / Pintu Masuk'
  })
}

/*
|--------------------------------------------------------------------------
| UPDATE RADIUS
|--------------------------------------------------------------------------
*/

const updateRadiusCircle = () => {
  if (!radiusCircle) return

  radiusCircle.setLatLng([
    scanLat.value,
    scanLng.value
  ])

  radiusCircle.setRadius(
    Number(radiusValue.value) || 30
  )
}

/*
|--------------------------------------------------------------------------
| RADIUS CHANGE
|--------------------------------------------------------------------------
*/

const onRadiusChange = () => {
  let value = Number(radiusValue.value)

  if (!Number.isFinite(value)) {
    value = 30
  }

  if (value < 5) {
    value = 5
  }

  if (value > 1000) {
    value = 1000
  }

  radiusValue.value = value

  updateRadiusCircle()

  emit('update:radius', value)
}

/*
|--------------------------------------------------------------------------
| FIT MAP
|--------------------------------------------------------------------------
*/

const fitMapToMarkers = () => {
  if (!map || !venueMarker || !scanMarker) return

  const venuePoint = venueMarker.getLatLng()
  const scanPoint = scanMarker.getLatLng()

  const bounds = L.latLngBounds([
    [venuePoint.lat, venuePoint.lng],
    [scanPoint.lat, scanPoint.lng]
  ])

  if (
    Math.abs(venuePoint.lat - scanPoint.lat) < 0.0001 &&
    Math.abs(venuePoint.lng - scanPoint.lng) < 0.0001
  ) {
    map.setView(
      [venuePoint.lat, venuePoint.lng],
      16
    )

    return
  }

  map.fitBounds(bounds, {
    padding: [80, 80],
    maxZoom: 17
  })
}

/*
|--------------------------------------------------------------------------
| CENTER VENUE
|--------------------------------------------------------------------------
*/

const centerToVenue = () => {
  if (!map) return

  map.setView(
    [venueLat.value, venueLng.value],
    17,
    {
      animate: true
    }
  )

  if (venueMarker) {
    venueMarker.openPopup()
  }
}

/*
|--------------------------------------------------------------------------
| CENTER SCAN
|--------------------------------------------------------------------------
*/

const centerToScan = () => {
  if (!map) return

  map.setView(
    [scanLat.value, scanLng.value],
    17,
    {
      animate: true
    }
  )

  if (scanMarker) {
    scanMarker.openPopup()
  }
}

/*
|--------------------------------------------------------------------------
| WATCH VENUE LATITUDE
|--------------------------------------------------------------------------
*/

watch(
  () => props.venueLatitude,
  (value) => {
    const lat = Number(value)

    if (!Number.isFinite(lat)) return

    venueLat.value = lat

    if (venueMarker) {
      venueMarker.setLatLng([
        venueLat.value,
        venueLng.value
      ])
    }
  }
)

watch(
  () => [props.venueName, props.venueAddress],
  ([name, address]) => {
    if (!searchResults.value.length && !searchLoading.value) {
      searchQuery.value = address || name || ''
    }
  }
)

/*
|--------------------------------------------------------------------------
| WATCH VENUE LONGITUDE
|--------------------------------------------------------------------------
*/

watch(
  () => props.venueLongitude,
  (value) => {
    const lng = Number(value)

    if (!Number.isFinite(lng)) return

    venueLng.value = lng

    if (venueMarker) {
      venueMarker.setLatLng([
        venueLat.value,
        venueLng.value
      ])
    }
  }
)

/*
|--------------------------------------------------------------------------
| WATCH SCAN LATITUDE
|--------------------------------------------------------------------------
*/

watch(
  () => props.scanLatitude,
  (value) => {
    const lat = Number(value)

    if (!Number.isFinite(lat)) return

    scanLat.value = lat

    if (scanMarker) {
      scanMarker.setLatLng([
        scanLat.value,
        scanLng.value
      ])

      updateRadiusCircle()
    }
  }
)

/*
|--------------------------------------------------------------------------
| WATCH SCAN LONGITUDE
|--------------------------------------------------------------------------
*/

watch(
  () => props.scanLongitude,
  (value) => {
    const lng = Number(value)

    if (!Number.isFinite(lng)) return

    scanLng.value = lng

    if (scanMarker) {
      scanMarker.setLatLng([
        scanLat.value,
        scanLng.value
      ])

      updateRadiusCircle()
    }
  }
)

/*
|--------------------------------------------------------------------------
| WATCH RADIUS
|--------------------------------------------------------------------------
*/

watch(
  () => props.radius,
  (value) => {
    const radius = Number(value)

    if (!Number.isFinite(radius)) return

    radiusValue.value = radius

    updateRadiusCircle()
  }
)

/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(() => {
  initMap()
})

/*
|--------------------------------------------------------------------------
| DESTROY
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
  searchController?.abort()

  if (map) {
    map.remove()
    map = null
  }
})
</script>

<template>
  <div class="location-picker">

    <form class="location-search" @submit.prevent="searchLocations">
      <input
        v-model="searchQuery"
        type="search"
        class="location-search-input"
        placeholder="Cari nama lokasi atau alamat..."
        aria-label="Cari lokasi atau alamat"
      />
      <button type="submit" class="location-search-button" :disabled="searchLoading">
        {{ searchLoading ? 'Mencari...' : 'Cari lokasi' }}
      </button>
    </form>

    <div v-if="searchResults.length || searchError" class="search-results">
      <button
        v-for="result in searchResults"
        :key="result.place_id"
        type="button"
        class="search-result"
        @click="chooseSearchResult(result)"
      >
        <strong>{{ result.name || result.display_name.split(',')[0] }}</strong>
        <span>{{ result.display_name }}</span>
      </button>
      <p v-if="searchError" class="search-message">{{ searchError }}</p>
    </div>

    <!-- MAP -->
    <div
      ref="mapContainer"
      class="map-container"
    ></div>

    <!-- MAP INFO -->
    <div class="map-info">

      <div class="map-info-title">
        <span>📍</span>
        <strong>Pengaturan Lokasi Event</strong>
      </div>

      <!-- LEGEND -->
      <div class="map-legend">

        <!-- VENUE -->
        <div class="legend-item">
          <div class="legend-icon venue">
            🏟️
          </div>

          <div class="legend-content">
            <strong>Venue</strong>

            <span>
              {{ venueLat.toFixed(6) }},
              {{ venueLng.toFixed(6) }}
            </span>
          </div>

          <button
            type="button"
            class="legend-button"
            @click="centerToVenue"
          >
            Lihat
          </button>
        </div>

        <!-- SCAN -->
        <div class="legend-item">
          <div class="legend-icon scan">
            🚪
          </div>

          <div class="legend-content">
            <strong>Scan / Pintu Masuk</strong>

            <span>
              {{ scanLat.toFixed(6) }},
              {{ scanLng.toFixed(6) }}
            </span>
          </div>

          <button
            type="button"
            class="legend-button"
            @click="centerToScan"
          >
            Lihat
          </button>
        </div>

      </div>

      <!-- RADIUS -->
      <div class="radius-control">

        <div class="radius-header">

          <div>
            <strong>⭕ Radius Check-in</strong>

            <p>
              Peserta hanya dapat check-in dalam area ini.
            </p>
          </div>

          <div class="radius-value">
            {{ radiusValue }} m
          </div>

        </div>

        <div class="radius-input-row">

          <input
            v-model.number="radiusValue"
            type="range"
            min="5"
            max="500"
            step="5"
            class="radius-slider"
            @input="onRadiusChange"
          />

          <input
            v-model.number="radiusValue"
            type="number"
            min="5"
            max="1000"
            step="5"
            class="radius-number"
            @change="onRadiusChange"
          />

          <span>meter</span>

        </div>

        <div class="radius-help">
          <span>5 m</span>
          <span>100 m</span>
          <span>250 m</span>
          <span>500 m</span>
        </div>

      </div>

      <!-- INSTRUCTION -->
      <div class="map-instruction">

        <div class="instruction-icon">
          💡
        </div>

        <div>
          <strong>Cara mengatur lokasi</strong>

          <ul>
            <li>
              Geser marker
              <b>🏟️ Venue</b>
              untuk menentukan lokasi utama event.
            </li>

            <li>
              Geser marker
              <b>🚪 Scan</b>
              ke lokasi pintu masuk/check-in.
            </li>

            <li>
              Klik langsung pada map untuk
              memindahkan titik Scan.
            </li>

            <li>
              Lingkaran ungu menunjukkan
              <b>radius area check-in</b>.
            </li>
          </ul>
        </div>

      </div>

    </div>

  </div>
</template>

<style scoped>
.location-picker {
  width: 100%;
}

.location-search {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.location-search-input {
  flex: 1;
  min-width: 0;
  padding: 0.7rem 0.8rem;
  border: 1px solid var(--border-color, #d1d5db);
  border-radius: 7px;
  background: var(--card-bg, #fff);
  color: var(--text-main, #222);
  font-size: 0.85rem;
}

.location-search-button {
  padding: 0.7rem 0.9rem;
  border: 0;
  border-radius: 7px;
  background: var(--primary, #7c3aed);
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
}

.location-search-button:disabled {
  opacity: 0.65;
  cursor: wait;
}

.search-results {
  display: grid;
  gap: 0.35rem;
  margin-bottom: 0.5rem;
}

.search-result {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  width: 100%;
  padding: 0.65rem 0.75rem;
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 7px;
  background: var(--card-bg, #fff);
  color: var(--text-main, #222);
  text-align: left;
  cursor: pointer;
}

.search-result:hover {
  border-color: var(--primary, #7c3aed);
  background: #f5f3ff;
}

.search-result strong {
  font-size: 0.8rem;
}

.search-result span,
.search-message {
  margin: 0;
  color: var(--text-muted, #888);
  font-size: 0.72rem;
  line-height: 1.35;
}

/* =========================================================
   MAP
========================================================= */

.map-container {
  width: 100%;
  height: 400px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--border-color, #e5e7eb);
  background: #f3f4f6;
  z-index: 1;
}

/* =========================================================
   INFO
========================================================= */

.map-info {
  margin-top: 0.75rem;
}

.map-info-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

/* =========================================================
   LEGEND
========================================================= */

.map-legend {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.75rem;
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px;
  background: var(--card-bg, #fff);
}

.legend-icon {
  width: 38px;
  height: 38px;
  min-width: 38px;
  border-radius: 9px;

  display: flex;
  align-items: center;
  justify-content: center;

  font-size: 19px;
}

.legend-icon.venue {
  background: #ede9fe;
}

.legend-icon.scan {
  background: #dcfce7;
}

.legend-content {
  min-width: 0;
  flex: 1;

  display: flex;
  flex-direction: column;
  gap: 2px;
}

.legend-content strong {
  font-size: 0.8rem;
  color: var(--text-main, #222);
}

.legend-content span {
  font-size: 0.68rem;
  color: var(--text-muted, #888);

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.legend-button {
  border: 1px solid var(--border-color, #e5e7eb);
  background: var(--bg-subtle, #f9fafb);
  color: var(--text-main, #222);

  padding: 0.35rem 0.55rem;
  border-radius: 6px;

  font-size: 0.7rem;
  font-weight: 600;

  cursor: pointer;
  white-space: nowrap;
}

.legend-button:hover {
  background: var(--primary, #7c3aed);
  color: white;
  border-color: var(--primary, #7c3aed);
}

/* =========================================================
   RADIUS
========================================================= */

.radius-control {
  margin-top: 0.75rem;

  padding: 0.85rem;

  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 10px;

  background: var(--card-bg, #fff);
}

.radius-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.radius-header strong {
  font-size: 0.82rem;
}

.radius-header p {
  margin: 0.2rem 0 0;

  font-size: 0.7rem;
  color: var(--text-muted, #888);
}

.radius-value {
  padding: 0.4rem 0.65rem;

  border-radius: 7px;

  background: #ede9fe;
  color: #6d28d9;

  font-size: 0.8rem;
  font-weight: 700;

  white-space: nowrap;
}

.radius-input-row {
  display: flex;
  align-items: center;
  gap: 0.65rem;

  margin-top: 0.75rem;
}

.radius-slider {
  flex: 1;
  cursor: pointer;
}

.radius-number {
  width: 85px;
  padding: 0.45rem 0.5rem;

  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 7px;

  font-size: 0.8rem;
  text-align: center;

  background: var(--card-bg, #fff);
  color: var(--text-main, #222);
}

.radius-input-row > span {
  font-size: 0.75rem;
  color: var(--text-muted, #888);
}

.radius-help {
  display: flex;
  justify-content: space-between;

  margin-top: 0.35rem;

  font-size: 0.65rem;
  color: var(--text-muted, #aaa);
}

/* =========================================================
   INSTRUCTION
========================================================= */

.map-instruction {
  display: flex;
  gap: 0.65rem;

  margin-top: 0.75rem;
  padding: 0.8rem;

  border-radius: 9px;

  background: #f5f3ff;
  border: 1px solid #ddd6fe;

  color: #4c1d95;
}

.instruction-icon {
  font-size: 1rem;
}

.map-instruction strong {
  font-size: 0.78rem;
}

.map-instruction ul {
  margin: 0.4rem 0 0;
  padding-left: 1.1rem;
}

.map-instruction li {
  margin-bottom: 0.2rem;

  font-size: 0.7rem;
  line-height: 1.45;
}

/* =========================================================
   MARKER
========================================================= */

:deep(.custom-map-icon) {
  background: transparent !important;
  border: none !important;
}

:deep(.venue-marker),
:deep(.scan-marker) {
  display: flex;
  flex-direction: column;
  align-items: center;
}

:deep(.venue-marker-pin),
:deep(.scan-marker-pin) {
  width: 38px;
  height: 38px;

  border-radius: 50% 50% 50% 0;

  display: flex;
  align-items: center;
  justify-content: center;

  transform: rotate(-45deg);

  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);

  border: 3px solid white;
}

:deep(.venue-marker-pin) {
  background: #7c3aed;
}

:deep(.scan-marker-pin) {
  background: #16a34a;
}

:deep(.venue-marker-pin span),
:deep(.scan-marker-pin span) {
  transform: rotate(45deg);
  font-size: 18px;
}

:deep(.venue-marker-label),
:deep(.scan-marker-label) {
  margin-top: -4px;

  padding: 2px 6px;

  border-radius: 4px;

  font-size: 8px;
  font-weight: 800;

  color: white;

  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

:deep(.venue-marker-label) {
  background: #7c3aed;
}

:deep(.scan-marker-label) {
  background: #16a34a;
}

/* =========================================================
   POPUP
========================================================= */

:deep(.map-popup strong) {
  font-size: 13px;
}

:deep(.map-popup span) {
  font-size: 11px;
  color: #666;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {
  .map-container {
    height: 320px;
  }

  .map-legend {
    grid-template-columns: 1fr;
  }

  .radius-input-row {
    flex-wrap: wrap;
  }

  .radius-slider {
    width: 100%;
    flex-basis: 100%;
  }
}
</style>
