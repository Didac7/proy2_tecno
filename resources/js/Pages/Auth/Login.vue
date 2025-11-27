<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>🚚 Trans Velasco</h1>
        <p>Sistema de Gestión de Paquetería</p>
      </div>

      <form @submit.prevent="submit" class="login-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="usuario@ejemplo.com"
            required
            autofocus
          />
          <span v-if="form.errors.email" class="error">{{ form.errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="password-wrapper">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Ingresa tu contraseña"
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

        <div class="form-group checkbox-group">
          <label>
            <input v-model="form.remember" type="checkbox" />
            Recordarme
          </label>
        </div>

        <button type="submit" class="btn-login" :disabled="form.processing">
          {{ form.processing ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>
      </form>

      <div class="register-link">
        <p>¿No tienes una cuenta? <Link href="/register" class="link-register">Crear cuenta</Link></p>
      </div>

      <div class="login-footer">
        <p>© 2025 Trans Velasco - Grupo09sc</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'jovenes';
  document.documentElement.setAttribute('data-theme', savedTheme);
});
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
  padding: 2rem;
}

.login-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 3rem;
  max-width: 450px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  color: var(--primary);
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
}

.login-header p {
  color: var(--text-secondary);
  margin: 0;
}

.login-form {
  margin-bottom: 2rem;
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

.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="text"] {
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

.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: normal;
}

.checkbox-group input[type="checkbox"] {
  width: auto;
  cursor: pointer;
}

.btn-login {
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

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 0.875rem;
  display: block;
  margin-top: 0.25rem;
}

.theme-selector {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  margin-bottom: 1.5rem;
}

.theme-selector label {
  font-weight: bold;
  color: var(--text-color);
}

.theme-selector select {
  padding: 0.5rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--card-bg);
  color: var(--text-color);
  cursor: pointer;
}

.login-footer {
  text-align: center;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.login-footer p {
  margin: 0;
}

.register-link {
  text-align: center;
  margin-bottom: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
}

.register-link p {
  margin: 0;
  color: var(--text-color);
}

.link-register {
  color: var(--primary);
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s;
}

.link-register:hover {
  color: var(--secondary);
  text-decoration: underline;
}
</style>
