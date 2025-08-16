<template>
  <div class="osm-container">
    <div ref="mapContainer" class="map-canvas"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick, watch, type PropType } from 'vue'
import L from 'leaflet'

interface Location {
  city: string
  region: string
  country: string
  latitude: number
  longitude: number
  visits: number
}

const props = defineProps({
  locations: {
    type: Array as PropType<Location[]>,
    required: true,
    default: () => []
  }
})

const mapContainer = ref<HTMLElement>()
let map: L.Map | null = null
let markers: L.Marker[] = []

const mapCenter = computed(() => {
  if (!props.locations.length) return { lat: 50.0, lng: 10.0 } // Centro da Europa
  
  const lats = props.locations.map(l => l.latitude)
  const lngs = props.locations.map(l => l.longitude)
  
  return {
    lat: lats.reduce((a, b) => a + b, 0) / lats.length,
    lng: lngs.reduce((a, b) => a + b, 0) / lngs.length
  }
})

const getMarkerColor = (index: number) => {
  const colors = ['#ef4444', '#3b82f6', '#10b981', '#eab308', '#8b5cf6']
  return colors[index] || '#6b7280'
}

const initMap = () => {
  if (!mapContainer.value) {
    return
  }

  try {
    // Configurar ícones padrão do Leaflet
    delete (L.Icon.Default.prototype as any)._getIconUrl
    L.Icon.Default.mergeOptions({
      iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
      iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
      shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    })

    // Inicializar o mapa
    map = L.map(mapContainer.value).setView([mapCenter.value.lat, mapCenter.value.lng], 6)

    // Adicionar tiles do OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(map)

    // Adicionar marcadores
    addMarkers()

    // Ajustar o zoom para mostrar todos os marcadores
    if (markers.length > 0) {
      const group = new L.FeatureGroup(markers)
      map.fitBounds(group.getBounds().pad(0.1))
    }
  } catch (error) {
    console.error('Erro ao inicializar o mapa:', error)
  }
}

const addMarkers = () => {
  if (!map) return

  // Limpar marcadores existentes
  markers.forEach(marker => marker.remove())
  markers = []

  // Adicionar novos marcadores
  props.locations.slice(0, 5).forEach((location, index) => {
    if (!map) return

    // Criar ícone personalizado
    const customIcon = L.divIcon({
      className: 'custom-map-marker',
      html: `<div style="
        width: 24px;
        height: 24px;
        background-color: ${getMarkerColor(index)};
        border: 2px solid white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        font-size: 12px;
        font-weight: bold;
      ">${index + 1}</div>`,
      iconSize: [24, 24],
      iconAnchor: [12, 12],
      popupAnchor: [0, -12]
    })

    // Criar marcador
    const marker = L.marker([location.latitude, location.longitude], { 
      icon: customIcon 
    }).addTo(map)

    // Adicionar popup
    marker.bindPopup(`
      <div style="text-align: left;">
        <strong>${location.city}, ${location.country}</strong><br>
        <span>${location.visits} visitas</span>
      </div>
    `)

    markers.push(marker)
  })
}

onMounted(() => {
  // Aguardar o próximo tick para garantir que o DOM está pronto
  nextTick(() => {
    setTimeout(initMap, 100)
  })
})

// Watch para reagir a mudanças nas locations
watch(() => props.locations, (newLocations) => {
  if (map && newLocations.length > 0) {
    addMarkers()
    
    // Reajustar bounds se houver marcadores
    if (markers.length > 0) {
      const group = new L.FeatureGroup(markers)
      map.fitBounds(group.getBounds().pad(0.1))
    }
  }
}, { deep: true })

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})
</script>

<style>
.osm-container {
  width: 100%;
  height: 400px;
  position: relative;
}

.map-canvas {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 1;
}

.custom-map-marker {
  background: transparent !important;
  border: none !important;
}

.leaflet-container {
  height: 100% !important;
  width: 100% !important;
}

.leaflet-popup-content {
  margin: 8px 12px;
  line-height: 1.4;
}
</style>
