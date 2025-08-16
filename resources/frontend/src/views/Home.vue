<script setup lang="ts">
import CvAnalyzer from '../components/CvAnalyzer.vue'
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
      <p class="text-xl text-gray-600">Analise currículos e gerencie oportunidades</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4 text-center">Análise de CVs</h2>
        <p class="text-gray-600 mb-4">
          Faça upload de currículos e obtenha análises detalhadas das competências dos candidatos.
        </p>
        <div class="text-sm text-gray-500 mb-4">
          ✓ Extração automática de skills<br>
          ✓ Análise de experiência<br>
          ✓ Comparação entre candidatos
        </div>
        <div class="flex justify-center">
          <router-link 
            to="/cv-analysis" 
            class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors mr-2"
          >
            Analisar Currículos
          </router-link>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4 text-center">Gestão de Oportunidades</h2>
        <p class="text-gray-600 mb-4">
          Crie e gerencie vagas de emprego, receba candidaturas e faça ranking automático.
        </p>
        <div class="text-sm text-gray-500 mb-4">
          ✓ Páginas públicas de vagas<br>
          ✓ Formulário de candidatura<br>
          ✓ Ranking automático por skills
        </div>
        <div class="flex justify-center">
          <router-link 
            to="/admin/oportunidades" 
            class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
          >
            Ver Oportunidades
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
