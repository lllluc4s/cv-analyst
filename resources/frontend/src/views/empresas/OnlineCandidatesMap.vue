<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center justify-between">
            <div>
              <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                  <li>
                    <router-link 
                      to="/empresas/dashboard" 
                      class="text-gray-400 hover:text-gray-500"
                    >
                      Dashboard
                    </router-link>
                  </li>
                  <li>
                    <div class="flex items-center">
                      <svg class="flex-shrink-0 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span class="ml-4 text-sm font-medium text-gray-500">Candidatos Online</span>
                    </div>
                  </li>
                </ol>
              </nav>
              <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                🌍 Mapa de Candidatos Online
              </h1>
              <p class="mt-1 text-sm text-gray-500">
                Visualização em tempo real dos candidatos ativos na plataforma
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                  <span class="text-2xl">🟢</span>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Online Agora
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ stats.total_online || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                  <span class="text-2xl">👥</span>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Ativos Recentemente
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ stats.total_recently_active || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                  <span class="text-2xl">🗺️</span>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Países Ativos
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ stats.countries || 0 }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Container -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Mapa Global de Atividade
            </h3>
            <div class="flex items-center space-x-2">
              <button
                @click="refreshData"
                :disabled="loading"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Atualizar
              </button>
            </div>
          </div>
          
          <!-- Mapbox Map -->
          <div 
            ref="mapContainer" 
            class="w-full rounded-lg bg-gray-100"
            style="height: 600px;"
          ></div>
        </div>
      </div>

      <!-- Country Breakdown -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Countries List -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Atividade por País
            </h3>
            <div v-if="byCountry.length > 0" class="space-y-3">
              <div 
                v-for="country in byCountry" 
                :key="country.country"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center">
                  <span class="text-2xl mr-3">🏴</span>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ country.country }}</p>
                    <p class="text-xs text-gray-500">{{ country.count }} candidato(s)</p>
                  </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ country.count }}
                </span>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              Nenhum candidato online no momento
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Candidatos Online
            </h3>
            <div v-if="candidates.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
              <div 
                v-for="candidate in candidates.slice(0, 10)" 
                :key="candidate.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center">
                  <img 
                    :src="generateDynamicAvatar(candidate.name, candidate.email, candidate.profile_photo)" 
                    :alt="candidate.name"
                    class="w-8 h-8 rounded-full mr-3"
                    @error="handleImageError"
                  >
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ candidate.name }}</p>
                    <p class="text-xs text-gray-500">
                      {{ candidate.location.city }}, {{ candidate.location.country }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center">
                  <span 
                    :class="{
                      'bg-green-100 text-green-800': candidate.is_online,
                      'bg-yellow-100 text-yellow-800': !candidate.is_online
                    }"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  >
                    <span 
                      :class="{
                        'bg-green-400': candidate.is_online,
                        'bg-yellow-400': !candidate.is_online
                      }"
                      class="w-1.5 h-1.5 rounded-full mr-1"
                    ></span>
                    {{ candidate.is_online ? 'Online' : 'Recente' }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              Nenhum candidato online no momento
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, type Ref } from 'vue'
import axios from 'axios'

// Mapbox imports
import mapboxgl from 'mapbox-gl'
import 'mapbox-gl/dist/mapbox-gl.css'

// State
const mapContainer = ref<HTMLElement>()
const map = ref<mapboxgl.Map>()
const loading = ref(false)
const candidates = ref<any[]>([])
const stats = ref<any>({})
const byCountry = ref<any[]>([])

// Animated counters
const animatedUserCount = ref(0)
const animatedCountryCount = ref(0)

// Mapbox token from environment
const MAPBOX_TOKEN = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN

// Auto-refresh interval
let refreshInterval: number
let rotationInterval: number

// Function to animate counter
const animateCounter = (target: Ref<number>, newValue: number, duration = 1000) => {
  const startValue = target.value
  const startTime = Date.now()
  
  const animate = () => {
    const elapsed = Date.now() - startTime
    const progress = Math.min(elapsed / duration, 1)
    
    // Easing function for smooth animation
    const easeOutQuart = 1 - Math.pow(1 - progress, 4)
    target.value = Math.round(startValue + (newValue - startValue) * easeOutQuart)
    
    if (progress < 1) {
      requestAnimationFrame(animate)
    }
  }
  
  requestAnimationFrame(animate)
}

const loadOnlineCandidates = async () => {
  try {
    loading.value = true
    
    const token = localStorage.getItem('company_token')
    
    if (!token) {
      console.log('Token não encontrado, usando dados de teste')
      // Use test data if no token
      candidates.value = [
        {
          id: 1,
          name: 'Lucas Rodrigues Silva',
          email: 'lucas@test.com',
          avatar: 'https://ui-avatars.com/api/?name=Lucas+Rodrigues&background=0D8ABC&color=fff&size=80',
          location: {
            country: 'Brasil',
            city: 'São Paulo',
            region: 'SP',
            latitude: -23.5505,
            longitude: -46.6333
          },
          last_seen: new Date().toISOString(),
          is_online: true,
          status: 'online'
        }
      ]
      stats.value = {
        total_online: 1,
        total_recently_active: 1,
        countries: 1
      }
      byCountry.value = [
        {
          country: 'Brasil',
          count: 1,
          candidates: candidates.value
        }
      ]
      
      // Animate counters
      animateCounter(animatedUserCount, stats.value.total_online || 0)
      animateCounter(animatedCountryCount, stats.value.countries || 0)    } else {
      const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/companies/online-candidates/map`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      })
      
      candidates.value = response.data.candidates
      stats.value = response.data.stats
      byCountry.value = response.data.by_country
      
      // Animate counters
      animateCounter(animatedUserCount, stats.value.total_online || 0)
      animateCounter(animatedCountryCount, stats.value.countries || 0)
    }

    console.log('Candidatos carregados:', candidates.value)

    // Update map with new data only if map is ready
    if (map.value && map.value.loaded()) {
      setTimeout(() => {
        updateMapMarkers()
      }, 200)
    }

  } catch (error) {
    console.error('Erro ao carregar candidatos online:', error)
    // Use test data on error
    candidates.value = [
      {
        id: 1,
        name: 'Lucas Rodrigues Silva',
        email: 'lucas@test.com',
        avatar: 'https://ui-avatars.com/api/?name=Lucas+Rodrigues&background=0D8ABC&color=fff&size=80',
        location: {
          country: 'Brasil',
          city: 'São Paulo',
          region: 'SP',
          latitude: -23.5505,
          longitude: -46.6333
        },
        last_seen: new Date().toISOString(),
        is_online: true,
        status: 'online'
      }
    ]
    stats.value = {
      total_online: 1,
      total_recently_active: 1,
      countries: 1
    }
    if (map.value && map.value.loaded()) {
      setTimeout(() => {
        updateMapMarkers()
      }, 200)
    }
  } finally {
    loading.value = false
  }
}

const initializeMap = () => {
  console.log('=== INICIALIZANDO MAPA ===')
  console.log('Container:', mapContainer.value)
  console.log('Token Mapbox:', MAPBOX_TOKEN)
  console.log('Todas as variáveis de ambiente:', import.meta.env)
  
  if (!mapContainer.value) {
    console.error('Container do mapa não encontrado!')
    return
  }

  if (!MAPBOX_TOKEN) {
    console.error('Token do Mapbox não encontrado!')
    console.log('Usando token de fallback...')
    // Use token de fallback para teste
    mapboxgl.accessToken = 'pk.eyJ1IjoibHVjYXNyb2QiLCJhIjoiY21kMnlndDNpMDEwMzJucG80czF6dXNkcCJ9.U0kQiQ3rfTjxxL_LBD6U5w'
  } else {
    mapboxgl.accessToken = MAPBOX_TOKEN
  }

  console.log('Inicializando mapa...')

  try {
    console.log('Token definido')

    // Create map with 3D globe projection
    map.value = new mapboxgl.Map({
      container: mapContainer.value,
      style: 'mapbox://styles/mapbox/satellite-streets-v12',
      center: [0, 20],
      zoom: 1.5,
      projection: 'globe' // Enable 3D globe view
    })

    console.log('Mapa criado, aguardando carregar...')
    
    // Add error handler
    map.value.on('error', (e) => {
      console.error('Erro no mapa:', e)
    })
    
  } catch (error) {
    console.error('Erro ao criar mapa:', error)
    return
  }

  // Add atmosphere effect for 3D globe
  map.value?.on('style.load', () => {
    console.log('Estilo do mapa carregado')
    map.value!.setFog({
      'color': 'rgb(186, 210, 235)', // Lower atmosphere
      'high-color': 'rgb(36, 92, 223)', // Upper atmosphere
      'horizon-blend': 0.02, // Atmosphere thickness (default 0.2 at low zooms)
      'space-color': 'rgb(11, 11, 25)', // Background color
      'star-intensity': 0.6 // Background star brightness (default 0.35 at low zooms )
    })
    
    // Start automatic rotation
    startGlobeRotation()
  })

  // Add controls
  map.value?.addControl(new mapboxgl.NavigationControl())
  map.value?.addControl(new mapboxgl.FullscreenControl())

  // Wait for map to load before adding markers
  map.value?.on('load', () => {
    console.log('Mapa totalmente carregado, redimensionando e atualizando marcadores...')
    
    // Force map resize to ensure proper positioning
    setTimeout(() => {
      map.value!.resize()
      if (candidates.value.length > 0) {
        updateMapMarkers()
      }
    }, 100)
  })
}

// Globe rotation function
const startGlobeRotation = () => {
  let userInteracting = false
  
  // Track user interactions
  map.value!.on('mousedown', () => {
    userInteracting = true
    if (rotationInterval) {
      clearInterval(rotationInterval)
    }
  })

  map.value!.on('mouseup', () => {
    setTimeout(() => {
      userInteracting = false
      startRotationLoop()
    }, 2000) // Wait 2 seconds before resuming rotation
  })

  map.value!.on('dragstart', () => {
    userInteracting = true
    if (rotationInterval) {
      clearInterval(rotationInterval)
    }
  })

  map.value!.on('dragend', () => {
    setTimeout(() => {
      userInteracting = false
      startRotationLoop()
    }, 2000)
  })

  const startRotationLoop = () => {
    if (rotationInterval) {
      clearInterval(rotationInterval)
    }
    
    rotationInterval = setInterval(() => {
      if (!userInteracting && map.value) {
        const zoom = map.value.getZoom()
        if (zoom < 3) {
          const center = map.value.getCenter()
          const newLng = center.lng - 0.6 // Rotate
          
          map.value.easeTo({
            center: [newLng, center.lat],
            duration: 1000,
            easing: (t) => t
          })
        }
      }
    }, 1000) // Rotate every second
  }
  
  // Start initial rotation
  startRotationLoop()
}

const updateMapMarkers = () => {
  if (!map.value) {
    console.log('Mapa não inicializado ainda')
    return
  }

  // Clear existing markers
  const existingMarkers = document.querySelectorAll('.mapboxgl-marker')
  existingMarkers.forEach(marker => marker.remove())

  console.log('Adicionando marcadores para', candidates.value.length, 'candidatos')

  // Add markers for each candidate
  candidates.value.forEach((candidate, index) => {
    const lat = candidate.location?.latitude
    const lng = candidate.location?.longitude
    
    console.log(`Candidato ${index + 1}: ${candidate.name}`, { lat, lng })
    
    if (lat && lng && lat !== 0 && lng !== 0) {
      // Validate coordinates
      if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        console.error('Coordenadas inválidas:', { lat, lng })
        return
      }

      // Generate dynamic avatar URL with profile_photo priority
      const avatarUrl = candidate.avatar || generateDynamicAvatar(candidate.name, candidate.email, candidate.profile_photo)
      
      // Calculate session time
      const sessionTime = getSessionTime(candidate.last_seen)

      // Create custom marker element - simplified approach
      const markerElement = document.createElement('div')
      markerElement.style.cssText = `
        width: 50px;
        height: 50px;
        background-image: url('${avatarUrl}');
        background-size: 40px 40px;
        background-repeat: no-repeat;
        background-position: center;
        border-radius: 50%;
        border: 3px solid ${candidate.is_online ? '#22c55e' : '#fbbf24'};
        box-shadow: 0 0 0 0 rgba(${candidate.is_online ? '34, 197, 94' : '251, 191, 36'}, 0.7);
        animation: pulse 2s infinite;
        cursor: pointer;
      `

      // Create popup with enhanced info including session time
      const popup = new mapboxgl.Popup({ offset: 25 }).setHTML(`
        <div class="popup-content">
          <div class="flex items-center space-x-3">
            <img src="${avatarUrl}" alt="${candidate.name}" class="w-12 h-12 rounded-full" />
            <div>
              <h3 class="font-semibold text-gray-900">${candidate.name}</h3>
              <p class="text-sm text-gray-600">${candidate.location.city}, ${candidate.location.country}</p>
              <p class="text-xs text-green-600">
                🟢 Online agora
              </p>
              <p class="text-xs text-blue-600 font-medium" id="timer-${candidate.id}">
                ⏱️ 00:00:00
              </p>
            </div>
          </div>
        </div>
      `)

      // Add timer functionality
      popup.on('open', () => {
        const startTime = new Date(candidate.last_seen || new Date()).getTime()
        const timerElement = document.getElementById(`timer-${candidate.id}`)
        
        const updateTimer = () => {
          const now = new Date().getTime()
          const diff = now - startTime
          
          const hours = Math.floor(diff / (1000 * 60 * 60))
          const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
          const seconds = Math.floor((diff % (1000 * 60)) / 1000)
          
          const formattedTime = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
          
          if (timerElement) {
            timerElement.innerHTML = `⏱️ ${formattedTime}`
          }
        }
        
        // Update immediately and then every second
        updateTimer()
        const interval = setInterval(updateTimer, 1000)
        
        // Clear interval when popup closes
        popup.on('close', () => {
          clearInterval(interval)
        })
      })

      try {
        // Add marker to map with correct longitude, latitude order
        const marker = new mapboxgl.Marker(markerElement)
          .setLngLat([lng, lat])  // [longitude, latitude]
          .setPopup(popup)
          .addTo(map.value!)
        
        console.log(`Marcador adicionado para ${candidate.name} em [${lng}, ${lat}]`)
      } catch (error) {
        console.error('Erro ao adicionar marcador:', error)
      }
    } else {
      console.log(`Coordenadas inválidas para ${candidate.name}:`, { lat, lng })
    }
  })
}

// Generate dynamic avatar using different services
const generateDynamicAvatar = (name: string, email?: string, profilePhoto?: string) => {
  // Prioridade 1: Profile photo (upload do usuário)
  if (profilePhoto) {
    return `${import.meta.env.VITE_API_URL}/storage/${profilePhoto}`
  }
  
  // Prioridade 2: Gravatar (se email disponível)
  if (email) {
    const emailHash = btoa(email.toLowerCase().trim()).replace(/[^a-zA-Z0-9]/g, '')
    return `https://www.gravatar.com/avatar/${emailHash}?d=retro&s=100`
  }
  
  // Prioridade 3: Iniciais do nome (UI Avatars)
  const encodedName = encodeURIComponent(name)
  const colors = ['0D8ABC', 'F39C12', 'E74C3C', '9B59B6', '27AE60', 'E67E22', '3498DB', '2ECC71']
  const randomColor = colors[Math.floor(Math.random() * colors.length)]
  
  return `https://ui-avatars.com/api/?name=${encodedName}&background=${randomColor}&color=fff&size=100&rounded=true`
}

// Calculate session time from last_seen
const getSessionTime = (lastSeen: string) => {
  if (!lastSeen) return 'Tempo desconhecido'
  
  const now = new Date()
  const lastSeenDate = new Date(lastSeen)
  const diffInMinutes = Math.floor((now.getTime() - lastSeenDate.getTime()) / (1000 * 60))
  
  if (diffInMinutes < 1) {
    return 'Online agora'
  } else if (diffInMinutes < 60) {
    return `Online há ${diffInMinutes} min`
  } else if (diffInMinutes < 1440) { // less than 24 hours
    const hours = Math.floor(diffInMinutes / 60)
    return `Online há ${hours}h`
  } else {
    const days = Math.floor(diffInMinutes / 1440)
    return `Ativo há ${days}d`
  }
}

const refreshData = () => {
  loadOnlineCandidates()
}

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.src = `https://www.gravatar.com/avatar/default?d=identicon&s=100`
}

onMounted(() => {
  // Initialize map first
  initializeMap()
  
  // Load candidates data
  loadOnlineCandidates()
  
  // Auto-refresh every 30 seconds
  refreshInterval = setInterval(() => {
    loadOnlineCandidates()
  }, 30000) as any
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  if (rotationInterval) {
    clearInterval(rotationInterval)
  }
  if (map.value) {
    map.value.remove()
  }
})
</script>

<style scoped>
/* Custom marker styles */
:deep(.custom-marker) {
  cursor: pointer;
}

:deep(.marker-content) {
  position: relative;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}

:deep(.avatar) {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 3px solid #fff;
  box-shadow: 0 2px 10px rgba(0,0,0,0.3);
  position: relative;
  z-index: 2;
}

:deep(.pulse) {
  position: absolute;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  animation: pulse 2s infinite;
  z-index: 1;
  top: 0;
  left: 0;
}

:deep(.pulse.online) {
  background-color: rgba(34, 197, 94, 0.4);
  border: 2px solid #22c55e;
}

:deep(.pulse.recent) {
  background-color: rgba(251, 191, 36, 0.4);
  border: 2px solid #fbbf24;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  
  70% {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
  }
  
  100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
  }
}

/* Popup styles */
:deep(.mapboxgl-popup-content) {
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

:deep(.popup-content) {
  min-width: 200px;
}
</style>
