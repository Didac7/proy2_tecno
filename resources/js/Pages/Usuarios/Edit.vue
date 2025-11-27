<template>
  <AppLayout>
    <div class="page-header">
      <h1>✏️ Editar Usuario</h1>
      <Link :href="`${$page.props.appUrl}/usuarios`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <!-- CI y Email -->
        <div class="form-row">
          <div class="form-group">
            <label>CI</label>
            <input 
              v-model="form.ci" 
              type="text" 
              required
            >
            <span v-if="form.errors.ci" class="error">{{ form.errors.ci }}</span>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input 
              v-model="form.email" 
              type="email" 
              required
            >
            <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Nombre</label>
            <input 
              v-model="form.nombre" 
              type="text" 
              required
            >
            <span v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</span>
          </div>

          <div class="form-group">
            <label>Apellido</label>
            <input 
              v-model="form.apellido" 
              type="text" 
              required
            >
            <span v-if="form.errors.apellido" class="error">{{ form.errors.apellido }}</span>
          </div>
        </div>

        <div class="form-group">
          <label>Teléfono</label>
          <input 
            v-model="form.telefono" 
            type="text" 
            required
          >
          <span v-if="form.errors.telefono" class="error">{{ form.errors.telefono }}</span>
        </div>

        <div class="form-group">
          <label>Dirección</label>
          <input 
            v-model="form.direccion" 
            type="text" 
          >
          <span v-if="form.errors.direccion" class="error">{{ form.errors.direccion }}</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Rol</label>
            <select v-model="form.tipo_usuario" required :disabled="isSecretaria && form.tipo_usuario === 'PROPIETARIO'">
              <option value="CLIENTE">Cliente</option>
              <option value="CHOFER">Chofer</option>
              <option value="SECRETARIA">Secretaria</option>
              <option v-if="!isSecretaria" value="PROPIETARIO">Propietario</option>
            </select>
            <span v-if="form.errors.tipo_usuario" class="error">{{ form.errors.tipo_usuario }}</span>
          </div>

          <div class="form-group">
            <label>Estado</label>
            <select v-model="form.estado" required>
              <option value="ACTIVO">Activo</option>
              <option value="INACTIVO">Inactivo</option>
            </select>
            <span v-if="form.errors.estado" class="error">{{ form.errors.estado }}</span>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Actualizar Usuario' }}
          </button>
          <Link :href="`${$page.props.appUrl}/usuarios`" class="btn-cancel">
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
import { computed } from 'vue';

const props = defineProps({
  usuario: Object
});

const page = usePage();
const isSecretaria = computed(() => page.props.auth?.user?.tipo_usuario === 'SECRETARIA');

const form = useForm({
  ci: props.usuario.ci,
  nombre: props.usuario.nombre,
  apellido: props.usuario.apellido,
  email: props.usuario.email,
  telefono: props.usuario.telefono,
  direccion: props.usuario.direccion,
  tipo_usuario: props.usuario.tipo_usuario,
  estado: props.usuario.estado
});

const submit = () => {
  form.put(`/usuarios/${props.usuario.id_usuario}`);
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
