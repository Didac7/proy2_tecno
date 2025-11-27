<template>
  <AppLayout>
    <div class="rastreo-page">
      <div class="rastreo-card">
        <div class="icon">🔍</div>
        <h1>Rastrear Paquete</h1>
        <p>Ingresa el código de seguimiento de tu paquete</p>

        <form @submit.prevent="rastrear" class="search-form">
          <input
            v-model="codigo"
            type="text"
            placeholder="AWE-2025-003"
            class="search-input"
            required
          />
          <button type="submit" class="btn-rastrear" :disabled="!codigo">
            🔍 Rastrear
          </button>
        </form>

        <div class="instrucciones">
          <h3>💡 ¿Cómo rastrear tu paquete?</h3>
          <ol>
            <li>Ingresa el código de seguimiento que recibiste al enviar tu paquete</li>
            <li>Haz clic en "Rastrear"</li>
            <li>Verás el estado actual y el historial completo de tu envío</li>
          </ol>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const codigo = ref('');
const page = usePage();

const rastrear = () => {
  if (codigo.value.trim()) {
    router.visit(`${page.props.appUrl}/rastreo/${codigo.value.trim()}`);
  }
};
</script>

<style scoped>
.rastreo-page {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.rastreo-card {
  background: var(--card-bg);
  padding: 3rem;
  border-radius: var(--border-radius);
  border: 2px solid var(--border-color);
  max-width: 600px;
  width: 100%;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

h1 {
  color: var(--primary);
  margin-bottom: 0.5rem;
  font-size: 2rem;
}

p {
  color: var(--text-secondary);
  margin-bottom: 2rem;
}

.search-form {
  display: flex;
  gap: 1rem;
  margin-bottom: 3rem;
}

.search-input {
  flex: 1;
  padding: 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1.1rem;
  text-align: center;
  text-transform: uppercase;
  font-weight: bold;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
}

.btn-rastrear {
  padding: 1rem 2rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s;
  white-space: nowrap;
}

.btn-rastrear:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.btn-rastrear:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.instrucciones {
  background: var(--bg-secondary);
  padding: 2rem;
  border-radius: var(--border-radius);
  text-align: left;
}

.instrucciones h3 {
  color: var(--primary);
  margin-bottom: 1rem;
  text-align: center;
}

.instrucciones ol {
  margin-left: 1.5rem;
  color: var(--text-color);
}

.instrucciones li {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}
</style>
