<template>
  <AppLayout>
    <div class="page-header">
      <h1>➕ Nuevo Vehículo</h1>
      <Link :href="`${$page.props.appUrl}/vehiculos`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <div class="form-section">
          <h2>🚗 Información del Vehículo</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="placa">Placa *</label>
              <input
                id="placa"
                v-model="form.placa"
                type="text"
                placeholder="ABC-1234"
                required
              />
              <span v-if="form.errors.placa" class="error">{{ form.errors.placa }}</span>
            </div>

            <div class="form-group">
              <label for="marca">Marca *</label>
              <input
                id="marca"
                v-model="form.marca"
                type="text"
                placeholder="Toyota, Nissan, etc."
                required
              />
            </div>

            <div class="form-group">
              <label for="modelo">Modelo *</label>
              <input
                id="modelo"
                v-model="form.modelo"
                type="text"
                placeholder="Hilux, Frontier, etc."
                required
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="año">Año *</label>
              <input
                id="año"
                v-model="form.año"
                type="number"
                :min="1900"
                :max="new Date().getFullYear() + 1"
                placeholder="2024"
                required
              />
              <span v-if="form.errors.año" class="error">{{ form.errors.año }}</span>
            </div>

            <div class="form-group">
              <label for="tipo">Tipo de Vehículo *</label>
              <select id="tipo" v-model="form.tipo_vehiculo" required>
                <option value="">Seleccione...</option>
                <option value="CAMION">Camión</option>
                <option value="CAMIONETA">Camioneta</option>
                <option value="FURGON">Furgón</option>
                <option value="MOTO">Moto</option>
              </select>
            </div>

            <div class="form-group">
              <label for="capacidad">Capacidad de Carga (kg) *</label>
              <input
                id="capacidad"
                v-model="form.capacidad_carga"
                type="number"
                step="0.01"
                min="0"
                placeholder="1000"
                required
              />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h2>👤 Asignación</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="chofer">Chofer Asignado</label>
              <select id="chofer" v-model="form.id_chofer">
                <option value="">Sin asignar</option>
                <option v-for="chofer in choferes" :key="chofer.id_usuario" :value="chofer.id_usuario">
                  {{ chofer.nombre }} {{ chofer.apellido }} ({{ chofer.ci }})
                </option>
              </select>
            </div>

            <div class="form-group checkbox-group">
              <label>
                <input v-model="form.gps_activo" type="checkbox" />
                GPS Activo
              </label>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Guardar Vehículo' }}
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
  choferes: Array,
});

const form = useForm({
  placa: '',
  marca: '',
  modelo: '',
  año: new Date().getFullYear(),
  capacidad_carga: '',
  tipo_vehiculo: '',
  id_chofer: '',
  gps_activo: true,
});

const submit = () => {
  form.post('/vehiculos');
};
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: var(--bg-secondary);
  color: var(--text-color);
  text-decoration: none;
  border-radius: var(--border-radius);
  border: 2px solid var(--border-color);
  font-weight: bold;
}

.form-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 2px solid var(--border-color);
}

.form-section:last-of-type {
  border-bottom: none;
}

.form-section h2 {
  color: var(--primary);
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
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
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
}

.checkbox-group {
  flex-direction: row;
  align-items: center;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
  width: auto;
  cursor: pointer;
}

.error {
  color: #e74c3c;
  font-size: 0.875rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-primary {
  padding: 1rem 2rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel {
  padding: 1rem 2rem;
  background: transparent;
  color: var(--text-color);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  text-decoration: none;
  font-weight: bold;
}
</style>
