<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import type { Business } from '@/types'

const props = defineProps<{ businesses: Business[]; center?: { lat: number; lng: number } | null }>()

const router = useRouter()
const mapEl = ref<HTMLDivElement | null>(null)
let map: L.Map | null = null
let markers: L.Marker[] = []
let userMarker: L.Marker | null = null

const businessIcon = L.divIcon({
  className: '',
  html: '<div style="width:14px;height:14px;border-radius:50%;background:var(--color-teal);border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3)"></div>',
  iconSize: [14, 14],
  iconAnchor: [7, 7],
})

const userIcon = L.divIcon({
  className: '',
  html: '<div style="width:14px;height:14px;border-radius:50%;background:#f5a623;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3)"></div>',
  iconSize: [14, 14],
  iconAnchor: [7, 7],
})

function renderMarkers() {
  if (!map) return

  markers.forEach((m) => m.remove())
  markers = []

  const withCoords = props.businesses.filter((b) => b.lat !== null && b.lng !== null)

  withCoords.forEach((business) => {
    const marker = L.marker([business.lat as number, business.lng as number], { icon: businessIcon }).addTo(map!)
    marker.bindPopup(`<strong>${business.name}</strong>`)
    marker.on('click', () => router.push(`/business/${business.slug}`))
    markers.push(marker)
  })

  if (userMarker) {
    userMarker.remove()
    userMarker = null
  }
  if (props.center) {
    userMarker = L.marker([props.center.lat, props.center.lng], { icon: userIcon }).addTo(map)
  }

  const points: [number, number][] = withCoords.map((b) => [b.lat as number, b.lng as number])
  if (props.center) points.push([props.center.lat, props.center.lng])

  if (points.length) {
    map.fitBounds(L.latLngBounds(points), { padding: [30, 30], maxZoom: 15 })
  } else {
    map.setView([35.1856, 33.3823], 10)
  }
}

onMounted(() => {
  if (!mapEl.value) return
  map = L.map(mapEl.value, { zoomControl: true }).setView([35.1856, 33.3823], 10)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map)
  renderMarkers()
})

onBeforeUnmount(() => {
  map?.remove()
  map = null
})

watch(() => props.businesses, renderMarkers)
watch(() => props.center, renderMarkers)
</script>

<template>
  <div ref="mapEl" class="w-full h-full rounded-2xl overflow-hidden" style="min-height: 300px" />
</template>
