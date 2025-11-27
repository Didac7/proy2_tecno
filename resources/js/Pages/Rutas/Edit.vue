<template>
  <AppLayout>
    <div class="page-header">
      <h1>✏️ Editar Ruta</h1>
      <Link :href="`${$page.props.appUrl}/rutas`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <div class="form-row">
          <div class="form-group">
            <label>Origen</label>
            <select v-model="form.origen_id" required>
              <option value="" disabled>Seleccione origen</option>
              <option 
                v-for="destino in destinos" 
                :key="destino.id_destino" 
                :value="destino.id_destino"
              >
                {{ destino.ciudad }}
              </option>
            </select>
            <span v-if="form.errors.origen_id" class="error">{{ form.errors.origen_id }}</span>
          </div>

          <div class="form-group">
            <label>Destino</label>
            <select v-model="form.destino_id" required>
              <option value="" disabled>Seleccione destino</option>
              <option 
                v-for="destino in destinos" 
                :key="destino.id_destino" 
                :value="destino.id_destino"
              >
                {{ destino.ciudad }}
              </option>
            </select>
            <span v-if="form.errors.destino_id" class="error">{{ form.errors.destino_id }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Distancia (km)</label>
            <input 
              v-model="form.distancia_km" 
              type="number" 
              step="0.1"
              placeholder="Ej: 150.5"
              required
            >
            <span v-if="form.errors.distancia_km" class="error">{{ form.errors.distancia_km }}</span>
          </div>

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
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Actualizar Ruta' }}
          </button>
          <Link :href="`${$page.props.appUrl}/rutas`" class="btn-cancel">
            Cancelar
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  ruta: Object,
  destinos: Array
});

const form = useForm({
  origen_id: props.ruta.origen_id,
  destino_id: props.ruta.destino_id,
  distancia_km: props.ruta.distancia_km,
  tiempo_estimado: props.ruta.tiempo_estimado
});

const page = usePage();

const submit = () => {
  form.put(`${page.props.appUrl}/rutas/${props.ruta.id_ruta}`);
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
}

.form-card {
  background: var(--card-bg);
  padding: 2rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  max-width: 600px;
  margin: 0 auto;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-row {
  display: flex;
  gap: 1rem;
}

.form-row .form-group {
  flex: 1;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: var(--text-color);
}

input, select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
}

.error {
  color: #e74c3c;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  cursor: pointer;
  flex: 1;
}

.btn-cancel {
  background: var(--bg-secondary);
  color: var(--text-color);
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius);
  text-decoration: none;
  font-weight: bold;
  text-align: center;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
