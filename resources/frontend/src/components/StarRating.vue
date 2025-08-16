<template>
  <div class="flex items-center space-x-1">
    <!-- Estrelas -->
    <button
      v-for="star in 5"
      :key="star"
      @click="handleStarClick(star)"
      @mouseenter="hoverRating = star"
      @mouseleave="hoverRating = 0"
      :disabled="disabled"
      class="transition-colors duration-150 focus:outline-none"
      :class="[
        disabled ? 'cursor-default' : 'cursor-pointer hover:scale-110',
        getSizeClass()
      ]"
      :title="disabled ? `Avaliação: ${rating || 0} estrela${rating !== 1 ? 's' : ''}` : `Dar ${star} estrela${star !== 1 ? 's' : ''}`"
    >
      <svg 
        class="transition-colors duration-150" 
        :class="getStarClass(star)"
        fill="currentColor" 
        viewBox="0 0 20 20"
      >
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
      </svg>
    </button>
    
    <!-- Texto da avaliação (opcional) -->
    <span v-if="showText" class="text-sm text-gray-600 ml-2">
      {{ rating ? `${rating}/5` : 'Sem avaliação' }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  rating?: number | null
  disabled?: boolean
  showText?: boolean
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  rating: null,
  disabled: false,
  showText: false,
  size: 'md'
})

const emit = defineEmits<{
  update: [rating: number]
}>()

const hoverRating = ref(0)

const displayRating = computed(() => {
  return hoverRating.value || props.rating || 0
})

const getSizeClass = () => {
  const sizes = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5', 
    lg: 'w-6 h-6'
  }
  return sizes[props.size]
}

const getStarClass = (star: number) => {
  const isFilled = star <= displayRating.value
  
  if (props.disabled) {
    return isFilled ? 'text-yellow-400' : 'text-gray-300'
  }
  
  return isFilled ? 'text-yellow-400' : 'text-gray-300'
}

const handleStarClick = (star: number) => {
  if (props.disabled) return
  emit('update', star)
}
</script>

<style scoped>
button:hover {
  transform: scale(1.1);
}
</style>
