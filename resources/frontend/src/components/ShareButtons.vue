<template>
  <div class="share-buttons">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">
      📢 Partilhar esta Oportunidade
    </h3>
    <p class="text-sm text-gray-600 text-center mb-6">
      Ajude outras pessoas a encontrar esta oportunidade incrível!
    </p>
    <div class="flex flex-wrap gap-3 justify-center">
      <!-- Facebook -->
      <a
        :href="facebookUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        @click="trackShare('facebook')"
      >
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        Facebook
      </a>

      <!-- Twitter/X -->
      <a
        :href="twitterUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        @click="trackShare('twitter')"
      >
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
        </svg>
        X (Twitter)
      </a>

      <!-- LinkedIn -->
      <a
        :href="linkedinUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        @click="trackShare('linkedin')"
      >
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
        </svg>
        LinkedIn
      </a>

      <!-- WhatsApp (apenas em dispositivos móveis) -->
      <a
        v-if="isMobile"
        :href="whatsappUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        @click="trackShare('whatsapp')"
      >
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
        </svg>
        WhatsApp
      </a>

      <!-- Copiar Link -->
      <button
        @click="copyLink"
        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        :class="{ 'bg-green-600 hover:bg-green-700': copied }"
      >
        <svg v-if="!copied" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ copied ? '✅ Copiado!' : 'Copiar Link' }}
      </button>
    </div>
    
    <!-- Informações adicionais -->
    <div class="mt-4 text-xs text-gray-500 text-center">
      Os links incluem parâmetros de rastreamento para análises de desempenho
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { UrlBuilder } from '@/utils/urlBuilder'

interface Props {
  titulo: string
  descricao: string
  url: string
  slug: string
  companyLogo?: string
}

const props = defineProps<Props>()

const copied = ref(false)
const isMobile = ref(false)
const metaData = ref<any>(null)

// Buscar meta dados da oportunidade
const fetchMetaData = async () => {
  try {
    const response = await fetch(`${UrlBuilder.api(`oportunidades/${props.slug}/meta`)}`)
    if (response.ok) {
      metaData.value = await response.json()
    }
  } catch (error) {
    console.error('Erro ao buscar meta dados:', error)
  }
}

// URLs de partilha com parâmetros UTM
const createUtmUrl = (platform: string) => {
  // Usar a URL do backend para meta tags sociais em vez do frontend direto
  const baseUrl = UrlBuilder.social(`oportunidade/${props.slug}`)
  const url = new URL(baseUrl)
  url.searchParams.set('utm_source', platform)
  url.searchParams.set('utm_medium', 'social')
  url.searchParams.set('utm_campaign', 'job_share')
  url.searchParams.set('utm_content', props.titulo.toLowerCase().replace(/\s+/g, '_'))
  return url.toString()
}

const createShareText = () => {
  const company = metaData.value?.company?.name || 'Empresa'
  const location = metaData.value?.location ? ` em ${metaData.value.location}` : ''
  const skills = metaData.value?.skills ? `\n\n🎯 Skills: ${metaData.value.skills}` : ''
  
  // Descrição breve (primeiras 100 caracteres)
  const briefDescription = props.descricao.length > 100 
    ? props.descricao.substring(0, 100) + '...' 
    : props.descricao
  
  return `🚀 ${props.titulo}

📍 ${company}${location}

📝 ${briefDescription}${skills}

#emprego #carreira #oportunidade`
}

const facebookUrl = computed(() => {
  const baseUrl = 'https://www.facebook.com/sharer/sharer.php'
  const params = new URLSearchParams({
    u: createUtmUrl('facebook'),
    quote: createShareText()
  })
  return `${baseUrl}?${params.toString()}`
})

const twitterUrl = computed(() => {
  const baseUrl = 'https://twitter.com/intent/tweet'
  const text = createShareText()
  // Limitar texto para Twitter (280 caracteres - URL)
  const tweetText = text.length > 220 ? text.substring(0, 220) + '...' : text
  const params = new URLSearchParams({
    text: tweetText,
    url: createUtmUrl('twitter'),
    hashtags: 'emprego,carreira,oportunidade'
  })
  return `${baseUrl}?${params.toString()}`
})

const linkedinUrl = computed(() => {
  const baseUrl = 'https://www.linkedin.com/sharing/share-offsite/'
  const params = new URLSearchParams({
    url: createUtmUrl('linkedin')
  })
  return `${baseUrl}?${params.toString()}`
})

const whatsappUrl = computed(() => {
  const text = `${createShareText()}\n\nVer mais: ${createUtmUrl('whatsapp')}`
  return `https://wa.me/?text=${encodeURIComponent(text)}`
})

// Detectar se é dispositivo móvel
onMounted(() => {
  isMobile.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
  fetchMetaData() // Buscar meta dados quando o componente carrega
})

// Copiar link para clipboard
const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(createUtmUrl('copy_link'))
    copied.value = true
    
    // Rastrear ação de cópia
    await trackShare('copy_link')
    
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Erro ao copiar link:', err)
    // Fallback para dispositivos que não suportam clipboard API
    const textArea = document.createElement('textarea')
    textArea.value = createUtmUrl('copy_link')
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    copied.value = true
    
    // Rastrear ação de cópia
    await trackShare('copy_link')
    
    setTimeout(() => {
      copied.value = false
    }, 2000)
  }
}

// Rastreamento de partilhas (optional - para analytics)
const trackShare = async (platform: string) => {
  console.log(`Compartilhado via ${platform}:`, props.url)
  
  try {
    // Enviar evento de tracking para o backend
    await fetch(UrlBuilder.api(`oportunidades/${props.slug}/share`), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        platform: platform,
        utm_source: platform,
        utm_medium: 'social',
        utm_campaign: 'job_share',
        utm_content: props.titulo.toLowerCase().replace(/\s+/g, '_')
      })
    })
    
    console.log(`Tracking enviado para ${platform}`)
  } catch (error) {
    console.error('Erro ao rastrear partilha:', error)
    // Não bloquear a partilha se o tracking falhar
  }
}
</script>

<style scoped>
.share-buttons {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 2rem;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  text-align: center;
  position: relative;
  overflow: hidden;
}

.share-buttons::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #3b82f6, #6366f1, #8b5cf6, #a855f7);
}

.share-buttons a,
.share-buttons button {
  transition: all 0.2s ease-in-out;
}

.share-buttons a:hover,
.share-buttons button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.3);
}

.share-buttons a:active,
.share-buttons button:active {
  transform: translateY(0);
}
</style>
