<template>
  <AppLayout>
    <div class="rastreo-container">
      <div class="rastreo-header">
        <h1>🔍 Rastrear Paquete</h1>
        <p>Ingresa el código de seguimiento de tu paquete</p>
      </div>

      <div class="search-card">
        <form @submit.prevent="buscar" class="search-form">
          <input
            v-model="codigo"
            type="text"
            placeholder="Ej: TRV-2025-001"
            class="search-input"
            required
          />
          <button type="submit" class="btn-search">
            🔍 Rastrear
          </button>
        </form>
      </div>

      <div class="info-card">
        <h3>💡 ¿Cómo rastrear tu paquete?</h3>
        <ol>
          <li>Ingresa el código de seguimiento que recibiste al enviar tu paquete</li>
          <li>Haz clic en "Rastrear"</li>
          <li>Verás el estado actual y el historial completo de tu envío</li>
        </ol>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const codigo = ref('');

const buscar = () => {
  if (codigo.value.trim()) {
    router.visit(`/rastreo/${codigo.value.trim()}`);
  }
};
</script>

<style scoped>
.rastreo-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 0;
}

.rastreo-header {
  text-align: center;
  margin-bottom: 3rem;
}

.rastreo-header h1 {
  color: var(--primary);
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.rastreo-header p {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.search-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.search-form {
  display: flex;
  gap: 1rem;
}

.search-input {
  flex: 1;
  padding: 1rem 1.5rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  font-size: 1.2rem;
  background: var(--bg-secondary);
  color: var(--text-color);
  text-align: center;
  text-transform: uppercase;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
}

.btn-search {
  padding: 1rem 2rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 1.2rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-search:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.info-card {
  background: var(--bg-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
}

.info-card h3 {
  color: var(--primary);
  margin-bottom: 1rem;
}

.info-card ol {
  padding-left: 1.5rem;
}

.info-card li {
  margin-bottom: 0.5rem;
  line-height: 1.6;
}
</style>
