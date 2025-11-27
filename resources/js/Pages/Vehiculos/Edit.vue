<template>
  <AppLayout>
    <div class="page-header">
      <h1>✏️ Editar Vehículo</h1>
      <Link :href="`${$page.props.appUrl}/vehiculos`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <div class="form-row">
          <div class="form-group">
            <label>Placa</label>
            <input 
              v-model="form.placa" 
              type="text" 
              required
            >
            <span v-if="form.errors.placa" class="error">{{ form.errors.placa }}</span>
          </div>

          <div class="form-group">
            <label>Marca</label>
            <input 
              v-model="form.marca" 
              type="text" 
              required
            >
            <span v-if="form.errors.marca" class="error">{{ form.errors.marca }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Modelo</label>
            <input 
              v-model="form.modelo" 
              type="text" 
              required
            >
            <span v-if="form.errors.modelo" class="error">{{ form.errors.modelo }}</span>
          </div>

          <div class="form-group">
            <label>Año</label>
            <input 
              v-model="form.anio" 
              type="number" 
              required
            >
            <span v-if="form.errors.anio" class="error">{{ form.errors.anio }}</span>
          </div>
        </div>

        <div class="form-group">
          <label>Capacidad de Carga (kg)</label>
          <input 
            v-model="form.capacidad_carga" 
            type="number" 
            step="0.01"
            required
          >
          <span v-if="form.errors.capacidad_carga" class="error">{{ form.errors.capacidad_carga }}</span>
        </div>

        <div class="form-group">
          <label>Estado</label>
          <select v-model="form.estado" required>
            <option value="DISPONIBLE">Disponible</option>
            <option value="EN_MANTENIMIENTO">En Mantenimiento</option>
            <option value="FUERA_DE_SERVICIO">Fuera de Servicio</option>
          </select>
          <span v-if="form.errors.estado" class="error">{{ form.errors.estado }}</span>
        </div>

        <div class="form-group">
          <label>Chofer Asignado</label>
          <select v-model="form.id_chofer">
            <option :value="null">Sin asignar</option>
            <option v-for="chofer in choferes" :key="chofer.id_usuario" :value="chofer.id_usuario">
              {{ chofer.nombre }} {{ chofer.apellido }}
            </option>
          </select>
          <span v-if="form.errors.id_chofer" class="error">{{ form.errors.id_chofer }}</span>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Actualizar Vehículo' }}
          </button>
          <Link :href="`${$page.props.appUrl}/vehiculos`" class="btn-cancel">
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
  vehiculo: Object,
  choferes: Array
});

const form = useForm({
  placa: props.vehiculo.placa,
  marca: props.vehiculo.marca,
  modelo: props.vehiculo.modelo,
  anio: props.vehiculo.anio,
  capacidad_carga: props.vehiculo.capacidad_carga,
  estado: props.vehiculo.estado,
  id_chofer: props.vehiculo.id_chofer
});

const submit = () => {
  form.put(`/vehiculos/${props.vehiculo.id_vehiculo}`);
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
