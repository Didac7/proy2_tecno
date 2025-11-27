<template>
  <AppLayout>
    <div class="page-header">
      <h1>➕ Nueva Ruta</h1>
      <Link :href="`${$page.props.appUrl}/rutas`" class="btn-secondary">
        ⬅️ Volver
      </Link>
    </div>

    <div class="form-container">
      <form @submit.prevent="submit" class="create-form">
        <!-- Origen -->
        <div class="form-group">
          <label>Origen</label>
          <select v-model="form.id_origen" required>
            <option value="">Seleccione una ciudad</option>
            <option v-for="destino in destinos" :key="destino.id_destino" :value="destino.id_destino">
              {{ destino.ciudad }}
            </option>
          </select>
          <span v-if="form.errors.id_origen" class="error">{{ form.errors.id_origen }}</span>
        </div>

        <!-- Destino -->
        <div class="form-group">
          <label>Destino</label>
          <select v-model="form.id_destino" required>
            <option value="">Seleccione una ciudad</option>
            <option v-for="destino in destinos" :key="destino.id_destino" :value="destino.id_destino">
              {{ destino.ciudad }}
            </option>
          </select>
          <span v-if="form.errors.id_destino" class="error">{{ form.errors.id_destino }}</span>
        </div>

        <!-- Distancia -->
        <div class="form-group">
          <label>Distancia (km)</label>
          <input 
            v-model="form.distancia_km" 
            type="number" 
            step="0.01" 
            placeholder="Ej: 150.5"
            required
          >
          <span v-if="form.errors.distancia_km" class="error">{{ form.errors.distancia_km }}</span>
        </div>

        <!-- Tiempo Estimado -->
        <div class="form-group">
          <label>Tiempo Estimado</label>
          <input 
            v-model="form.tiempo_estimado" 
            type="text" 
            placeholder="Ej: 2 horas 30 min"
            required
          >
          <span v-if="form.errors.tiempo_estimado" class="error">{{ form.errors.tiempo_estimado }}</span>
        </div>

        <div class="form-actions">
          <Link :href="`${$page.props.appUrl}/rutas`" class="btn-cancel">
            Cancelar
          </Link>
          <button type="submit" class="btn-submit" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : 'Guardar Ruta' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  destinos: Array
});

const form = useForm({
  id_origen: '',
  id_destino: '',
  distancia_km: '',
  tiempo_estimado: ''
});

const page = usePage();

const submit = () => {
  form.post(`${page.props.appUrl}/rutas`);
};
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: var(--primary);
  margin: 0;
}

.btn-secondary {
  color: var(--text-secondary);
  text-decoration: none;
  font-weight: bold;
  font-size: 1.1rem;
  transition: color 0.3s;
}

.btn-secondary:hover {
  color: var(--primary);
}

.form-container {
  background: var(--card-bg);
  padding: 2rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  max-width: 600px;
  margin: 0 auto;
}

.create-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: bold;
  color: var(--text-color);
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.2);
}

.error {
  color: #ef4444;
  font-size: 0.875rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-submit {
  flex: 1;
  background: var(--primary);
  color: white;
  padding: 0.75rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  cursor: pointer;
  transition: opacity 0.3s;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-cancel {
  flex: 1;
  background: var(--bg-secondary);
  color: var(--text-color);
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  text-align: center;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s;
}

.btn-cancel:hover {
  background: var(--border-color);
}
</style>
