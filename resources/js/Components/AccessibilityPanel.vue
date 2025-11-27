<template>
  <div class="accessibility-panel" :class="{ 'panel-open': isOpen }">
    <button @click="togglePanel" class="panel-toggle" title="Opciones de Accesibilidad">
      ⚙️
    </button>

    <div v-if="isOpen" class="panel-content">
      <h3>⚙️ Configuración</h3>

      <!-- Temas -->
      <div class="config-section">
        <label>🎨 Tema</label>
        <select v-model="currentTheme" @change="handleThemeChange" class="config-select">
          <option value="jovenes">🎮 Jóvenes</option>
          <option value="ninos">👶 Niños</option>
          <option value="adultos">💼 Adultos</option>
        </select>
      </div>

      <!-- Modo Oscuro -->
      <div class="config-section">
        <label>
          <input type="checkbox" v-model="currentDarkMode" @change="handleDarkModeToggle">
          🌙 Modo Oscuro
        </label>
        <small>Auto: {{ isDayTime() ? '☀️ Día' : '🌙 Noche' }}</small>
      </div>

      <!-- Tamaño de Fuente -->
      <div class="config-section">
        <label>📏 Tamaño de Letra</label>
        <div class="font-size-buttons">
          <button 
            @click="handleFontSizeChange('small')" 
            :class="{ active: currentFontSize === 'small' }"
            class="size-btn"
          >
            A
          </button>
          <button 
            @click="handleFontSizeChange('normal')" 
            :class="{ active: currentFontSize === 'normal' }"
            class="size-btn"
          >
            A
          </button>
          <button 
            @click="handleFontSizeChange('large')" 
            :class="{ active: currentFontSize === 'large' }"
            class="size-btn size-large"
          >
            A
          </button>
        </div>
      </div>

      <!-- Alto Contraste -->
      <div class="config-section">
        <label>
          <input type="checkbox" v-model="currentHighContrast" @change="handleContrastToggle">
          🔆 Alto Contraste
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTheme } from '@/composables/useTheme';

const { theme, darkMode, fontSize, highContrast, setTheme, toggleDarkMode, setFontSize, toggleHighContrast, init } = useTheme();

const isOpen = ref(false);
const currentTheme = ref(theme.value);
const currentDarkMode = ref(darkMode.value);
const currentFontSize = ref(fontSize.value);
const currentHighContrast = ref(highContrast.value);

const togglePanel = () => {
  isOpen.value = !isOpen.value;
};

const handleThemeChange = () => {
  setTheme(currentTheme.value);
};

const handleDarkModeToggle = () => {
  toggleDarkMode();
};

const handleFontSizeChange = (size) => {
  currentFontSize.value = size;
  setFontSize(size);
};

const handleContrastToggle = () => {
  toggleHighContrast();
};

const isDayTime = () => {
  const hour = new Date().getHours();
  return hour >= 6 && hour < 20;
};

onMounted(() => {
  init();
  currentTheme.value = theme.value;
  currentDarkMode.value = darkMode.value;
  currentFontSize.value = fontSize.value;
  currentHighContrast.value = highContrast.value;
});
</script>

<style scoped>
.accessibility-panel {
  position: fixed;
  top: 50%;
  right: 0;
  transform: translateY(-50%);
  z-index: 9999;
}

.panel-toggle {
  position: absolute;
  right: 0;
  top: 0;
  background: var(--primary);
  color: white;
  border: none;
  padding: 1rem;
  font-size: 1.5rem;
  cursor: pointer;
  border-radius: 8px 0 0 8px;
  box-shadow: -2px 2px 8px rgba(0,0,0,0.2);
  transition: all 0.3s;
}

.panel-toggle:hover {
  transform: scale(1.1);
}

.panel-content {
  position: absolute;
  right: 60px;
  top: 0;
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: 8px;
  padding: 1.5rem;
  min-width: 280px;
  box-shadow: -4px 4px 16px rgba(0,0,0,0.2);
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.panel-content h3 {
  margin: 0 0 1rem 0;
  color: var(--primary);
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 0.5rem;
}

.config-section {
  margin-bottom: 1.5rem;
}

.config-section label {
  display: block;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.config-section small {
  display: block;
  color: var(--text-secondary);
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.config-select {
  width: 100%;
  padding: 0.5rem;
  border: 2px solid var(--border-color);
  border-radius: 4px;
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1rem;
}

.font-size-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: space-between;
}

.size-btn {
  flex: 1;
  padding: 0.5rem;
  border: 2px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-color);
  cursor: pointer;
  border-radius: 4px;
  font-weight: bold;
  transition: all 0.2s;
}

.size-btn:hover {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.size-btn.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.size-btn:nth-child(1) {
  font-size: 0.85rem;
}

.size-btn:nth-child(2) {
  font-size: 1rem;
}

.size-btn:nth-child(3) {
  font-size: 1.25rem;
}

input[type="checkbox"] {
  margin-right: 0.5rem;
  cursor: pointer;
  width: 18px;
  height: 18px;
}
</style>
