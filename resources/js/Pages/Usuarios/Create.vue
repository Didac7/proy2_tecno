<template>
  <AppLayout>
    <div class="page-header">
      <h1>➕ Nuevo Usuario</h1>
      <Link href="/usuarios" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <div class="form-section">
          <h2>👤 Información Personal</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="ci">CI *</label>
              <input
                id="ci"
                v-model="form.ci"
                type="text"
                placeholder="1234567"
                required
              />
              <span v-if="form.errors.ci" class="error">{{ form.errors.ci }}</span>
            </div>

            <div class="form-group">
              <label for="nombre">Nombre *</label>
              <input
                id="nombre"
                v-model="form.nombre"
                type="text"
                placeholder="Juan"
                required
              />
              <span v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</span>
            </div>

            <div class="form-group">
              <label for="apellido">Apellido *</label>
              <input
                id="apellido"
                v-model="form.apellido"
                type="text"
                placeholder="Pérez"
                required
              />
              <span v-if="form.errors.apellido" class="error">{{ form.errors.apellido }}</span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="tipo">Tipo de Usuario *</label>
              <select id="tipo" v-model="form.tipo_usuario" required>
                <option value="">Seleccione...</option>
                <option value="PROPIETARIO">Propietario</option>
                <option value="SECRETARIA">Secretaria</option>
                <option value="CHOFER">Chofer</option>
                <option value="CLIENTE">Cliente</option>
              </select>
              <span v-if="form.errors.tipo_usuario" class="error">{{ form.errors.tipo_usuario }}</span>
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono *</label>
              <input
                id="telefono"
                v-model="form.telefono"
                type="tel"
                placeholder="70123456"
                required
              />
              <span v-if="form.errors.telefono" class="error">{{ form.errors.telefono }}</span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="estado">Estado *</label>
              <select id="estado" v-model="form.estado" required>
                <option value="ACTIVO">Activo</option>
                <option value="INACTIVO">Inactivo</option>
              </select>
              <span v-if="form.errors.estado" class="error">{{ form.errors.estado }}</span>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h2>📧 Información de Contacto</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="email">Email *</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="usuario@ejemplo.com"
                required
              />
              <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
            </div>

            <div class="form-group">
              <label for="password">Contraseña *</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Mínimo 6 caracteres"
                required
              />
              <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
            </div>
          </div>

          <div class="form-group">
            <label for="direccion">Dirección</label>
            <input
              id="direccion"
              v-model="form.direccion"
              type="text"
              placeholder="Calle, número, zona..."
            />
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Guardar Usuario' }}
          </button>
          <Link href="/usuarios" class="btn-cancel">
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

const form = useForm({
  ci: '',
  nombre: '',
  apellido: '',
  tipo_usuario: '',
  telefono: '',
  email: '',
  password: '',
  direccion: '',
  estado: 'ACTIVO',
});

const submit = () => {
  form.post('/usuarios');
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
