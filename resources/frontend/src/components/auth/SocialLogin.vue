<template>
  <div class="social-login">
    <div class="social-buttons">
      <a :href="backendUrl + '/auth/google/redirect'" class="btn-social btn-google">
        <i class="fa-brands fa-google"></i>
        <span>{{ actionText }} com Google</span>
      </a>
      <a :href="backendUrl + '/auth/github/redirect'" class="btn-social btn-github">
        <i class="fa-brands fa-github"></i>
        <span>{{ actionText }} com GitHub</span>
      </a>
      <a :href="backendUrl + '/auth/linkedin/redirect'" class="btn-social btn-linkedin">
        <i class="fa-brands fa-linkedin"></i>
        <span>{{ actionText }} com LinkedIn</span>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'SocialLogin',
  props: {
    isRegister: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    actionText() {
      return this.isRegister ? 'Registrar' : 'Entrar';
    },
    backendUrl() {
      return UrlBuilder.getApiBaseUrl();
    }
  }
});
</script>

<script setup lang="ts">
import { UrlBuilder } from '@/utils/urlBuilder';
</script>

<style scoped>
.social-login {
  margin-bottom: 2rem;
}

.social-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-social {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  color: white;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-social i {
  margin-right: 0.75rem;
  font-size: 1.25rem;
}

.btn-google {
  background-color: #DB4437;
}

.btn-google:hover {
  background-color: #C1351E;
}

.btn-github {
  background-color: #24292e;
}

.btn-github:hover {
  background-color: #000000;
}

.btn-linkedin {
  background-color: #0077B5;
}

.btn-linkedin:hover {
  background-color: #005E8C;
}

.divider {
  display: flex;
  align-items: center;
  margin: 1.5rem 0;
  color: #6B7280;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #E5E7EB;
}

.divider span {
  padding: 0 1rem;
  font-size: 0.875rem;
}
</style>
