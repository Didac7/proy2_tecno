<template>
  <AppLayout>
    <div class="page-header">
      <h1>✏️ Editar Destino</h1>
      <Link :href="`${$page.props.appUrl}/destinos`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <div class="form-group">
          <label>Ciudad</label>
          <input 
            v-model="form.ciudad" 
            type="text" 
            placeholder="Ej: Santa Cruz"
            required
          >
          <span v-if="form.errors.ciudad" class="error">{{ form.errors.ciudad }}</span>
        </div>

        <div class="form-group">
          <label>Dirección</label>
          <input 
            v-model="form.direccion" 
            type="text" 
            placeholder="Ej: Av. Banzer 4to Anillo"
            required
          >
          <span v-if="form.errors.direccion" class="error">{{ form.errors.direccion }}</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Latitud</label>
            <input 
              v-model="form.latitud" 
              type="number" 
              step="any"
              placeholder="-17.7833"
            >
            <span v-if="form.errors.latitud" class="error">{{ form.errors.latitud }}</span>
          </div>

          <div class="form-group">
            <label>Longitud</label>
            <input 
              v-model="form.longitud" 
              type="number" 
              step="any"
              placeholder="-63.1821"
            >
            <span v-if="form.errors.longitud" class="error">{{ form.errors.longitud }}</span>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Actualizar Destino' }}
          </button>
          <Link :href="`${$page.props.appUrl}/destinos`" class="btn-cancel">
            Cancelar
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  destino: Object
});

const form = useForm({
  ciudad: props.destino.ciudad,
  direccion: props.destino.direccion,
  latitud: props.destino.latitud,
  longitud: props.destino.longitud
});

const submit = () => {
  form.put(`/destinos/${props.destino.id_destino}`);
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

input {
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
