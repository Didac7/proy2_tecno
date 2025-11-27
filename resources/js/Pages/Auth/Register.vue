<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h1>🚚 Trans Velasco</h1>
        <p>Crear Cuenta de Cliente</p>
      </div>

      <form @submit.prevent="submit" class="register-form">
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
        </div>

        <div class="form-row">
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

        <div class="form-group">
          <label for="email">Correo Electrónico *</label>
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
          <label for="direccion">Dirección</label>
          <input
            id="direccion"
            v-model="form.direccion"
            type="text"
            placeholder="Calle, número, zona..."
          />
        </div>

        <div class="form-group">
          <label for="password">Contraseña *</label>
          <div class="password-wrapper">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Mínimo 6 caracteres"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="toggle-password"
              :title="showPassword ? 'Ocultar' : 'Mostrar'"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
          <span v-if="form.errors.password" class="error">{{ form.errors.password }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar Contraseña *</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            placeholder="Repite tu contraseña"
            required
          />
        </div>

        <button type="submit" class="btn-register" :disabled="form.processing">
          {{ form.processing ? 'Creando cuenta...' : '✅ Crear Cuenta' }}
        </button>
      </form>

      <div class="login-link">
        <p>¿Ya tienes una cuenta? <Link :href="`${$page.props.appUrl}/login`" class="link-login">Iniciar sesión</Link></p>
      </div>

      <div class="register-footer">
        <p>© 2025 Trans Velasco - Grupo09sc</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  ci: '',
  nombre: '',
  apellido: '',
  telefono: '',
  email: '',
  direccion: '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);

const submit = () => {
  form.post('register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  padding: 2rem;
}

.register-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 3rem;
  max-width: 600px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.register-header {
  text-align: center;
  margin-bottom: 2rem;
}

.register-header h1 {
  color: var(--primary);
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
}

.register-header p {
  color: var(--text-secondary);
  margin: 0;
}

.register-form {
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: var(--text-color);
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1rem;
}

.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-wrapper input {
  flex: 1;
  padding-right: 3rem;
}

.toggle-password {
  position: absolute;
  right: 0.5rem;
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 1.5rem;
  padding: 0.5rem;
  color: var(--text-secondary);
  transition: all 0.3s;
}

.toggle-password:hover {
  color: var(--primary);
  transform: scale(1.1);
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
}

.btn-register {
  width: 100%;
  padding: 1rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-register:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.btn-register:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 0.875rem;
  display: block;
  margin-top: 0.25rem;
}

.login-link {
  text-align: center;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
}

.login-link p {
  margin: 0;
  color: var(--text-color);
}

.link-login {
  color: var(--primary);
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s;
}

.link-login:hover {
  color: var(--secondary);
  text-decoration: underline;
}

.register-footer {
  text-align: center;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.register-footer p {
  margin: 0;
}

@media (max-width: 600px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
