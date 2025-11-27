import { ref, onMounted, watch } from 'vue';

export function useTheme() {
  const theme = ref('jovenes');
  const darkMode = ref(false);
  const fontSize = ref('normal');
  const highContrast = ref(false);

  // Detectar si es de día o de noche según la hora local
  const isDayTime = () => {
    const hour = new Date().getHours();
    return hour >= 6 && hour < 20; // Día: 6am - 8pm
  };

  // Aplicar modo día/noche automático
  const applyAutoDarkMode = () => {
    const isDay = isDayTime();
    darkMode.value = !isDay;
    document.documentElement.setAttribute('data-dark-mode', darkMode.value ? 'true' : 'false');
  };

  // Aplicar tema
  const applyTheme = () => {
    document.documentElement.setAttribute('data-theme', theme.value);
    localStorage.setItem('theme', theme.value);
  };

  // Aplicar tamaño de fuente
  const applyFontSize = () => {
    document.documentElement.setAttribute('data-font-size', fontSize.value);
    localStorage.setItem('fontSize', fontSize.value);
  };

  // Aplicar alto contraste
  const applyHighContrast = () => {
    document.documentElement.setAttribute('data-high-contrast', highContrast.value ? 'true' : 'false');
    localStorage.setItem('highContrast', highContrast.value.toString());
  };

  // Cambiar tema
  const setTheme = (newTheme) => {
    theme.value = newTheme;
    applyTheme();
  };

  // Toggle modo oscuro manual
  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    document.documentElement.setAttribute('data-dark-mode', darkMode.value ? 'true' : 'false');
    localStorage.setItem('darkMode', darkMode.value.toString());
  };

  // Cambiar tamaño de fuente
  const setFontSize = (size) => {
    fontSize.value = size;
    applyFontSize();
  };

  // Toggle alto contraste
  const toggleHighContrast = () => {
    highContrast.value = !highContrast.value;
    applyHighContrast();
  };

  // Inicializar
  const init = () => {
    // Cargar preferencias guardadas
    const savedTheme = localStorage.getItem('theme');
    const savedDarkMode = localStorage.getItem('darkMode');
    const savedFontSize = localStorage.getItem('fontSize');
    const savedHighContrast = localStorage.getItem('highContrast');

    if (savedTheme) {
      theme.value = savedTheme;
    }

    if (savedDarkMode !== null) {
      darkMode.value = savedDarkMode === 'true';
    } else {
      // Si no hay preferencia guardada, usar modo automático
      applyAutoDarkMode();
    }

    if (savedFontSize) {
      fontSize.value = savedFontSize;
    }

    if (savedHighContrast !== null) {
      highContrast.value = savedHighContrast === 'true';
    }

    // Aplicar configuraciones
    applyTheme();
    applyFontSize();
    applyHighContrast();
    document.documentElement.setAttribute('data-dark-mode', darkMode.value ? 'true' : 'false');

    // Actualizar modo día/noche cada hora
    setInterval(() => {
      if (localStorage.getItem('darkMode') === null) {
        applyAutoDarkMode();
      }
    }, 3600000); // Cada hora
  };

  return {
    theme,
    darkMode,
    fontSize,
    highContrast,
    setTheme,
    toggleDarkMode,
    setFontSize,
    toggleHighContrast,
    init,
  };
}
