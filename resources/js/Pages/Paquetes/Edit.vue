<template>
  <AppLayout>
    <div class="page-header">
      <h1>✏️ Editar Paquete</h1>
      <Link :href="`${$page.props.appUrl}/paquetes`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <div class="form-group">
          <label>Descripción del Contenido</label>
          <textarea 
            v-model="form.descripcion_contenido" 
            rows="3"
            required
          ></textarea>
          <span v-if="form.errors.descripcion_contenido" class="error">{{ form.errors.descripcion_contenido }}</span>
        </div>

        <div class="form-group">
          <label>Precio del Envío (Bs.)</label>
          <input 
            v-model="form.precio" 
            type="number" 
            step="0.01"
            min="1"
            required
          >
          <span v-if="form.errors.precio" class="error">{{ form.errors.precio }}</span>
        </div>

        <div class="form-group">
          <label>Estado</label>
          <select v-model="form.estado_paquete" required>
            <option value="REGISTRADO">Registrado</option>
            <option value="PENDIENTE">Pendiente</option>
            <option value="EN_ALMACEN">En Almacén</option>
            <option value="EN_TRANSITO">En Tránsito</option>
            <option value="ENTREGADO">Entregado</option>
            <option value="CANCELADO">Cancelado</option>
          </select>
          <span v-if="form.errors.estado_paquete" class="error">{{ form.errors.estado_paquete }}</span>
        </div>

        <div class="form-group">
          <label>Dirección de Entrega</label>
          <input 
            v-model="form.direccion_entrega" 
            type="text" 
            required
          >
          <span v-if="form.errors.direccion_entrega" class="error">{{ form.errors.direccion_entrega }}</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Nombre Destinatario</label>
            <input 
              v-model="form.nombre_destinatario" 
              type="text" 
              required
            >
            <span v-if="form.errors.nombre_destinatario" class="error">{{ form.errors.nombre_destinatario }}</span>
          </div>

          <div class="form-group">
            <label>Teléfono Destinatario</label>
            <input 
              v-model="form.telefono_destinatario" 
              type="text" 
              required
            >
            <span v-if="form.errors.telefono_destinatario" class="error">{{ form.errors.telefono_destinatario }}</span>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Actualizar Paquete' }}
          </button>
          <Link :href="`${$page.props.appUrl}/paquetes`" class="btn-cancel">
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
  paquete: Object
});

const form = useForm({
  descripcion_contenido: props.paquete.descripcion_contenido,
  precio: props.paquete.precio,
  estado_paquete: props.paquete.estado_paquete,
  direccion_entrega: props.paquete.direccion_entrega,
  nombre_destinatario: props.paquete.nombre_destinatario,
  telefono_destinatario: props.paquete.telefono_destinatario
});

const page = usePage();

const submit = () => {
  form.put(`${page.props.appUrl}/paquetes/${props.paquete.id_paquete}`);
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

input, select, textarea {
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
