<template>
  <AppLayout>
    <div class="search-header">
      <h1>🔍 Resultados de Búsqueda</h1>
      <p>Búsqueda: <strong>"{{ query }}"</strong></p>
    </div>

    <!-- Paquetes -->
    <div v-if="resultados.paquetes.length > 0" class="results-section">
      <h2>📦 Paquetes ({{ resultados.paquetes.length }})</h2>
      <div class="results-grid">
        <Link
          v-for="paquete in resultados.paquetes"
          :key="paquete.id_paquete"
          :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}`"
          class="result-card"
        >
          <div class="result-icon">📦</div>
          <div class="result-content">
            <h3>{{ paquete.codigo_seguimiento }}</h3>
            <p>{{ paquete.descripcion_contenido }}</p>
            <span :class="'badge badge-' + getEstadoClass(paquete.estado_paquete)">
              {{ paquete.estado_paquete }}
            </span>
          </div>
        </Link>
      </div>
    </div>

    <!-- Usuarios -->
    <div v-if="resultados.usuarios.length > 0" class="results-section">
      <h2>👥 Usuarios ({{ resultados.usuarios.length }})</h2>
      <div class="results-grid">
        <Link
          v-for="usuario in resultados.usuarios"
          :key="usuario.id_usuario"
          :href="`${$page.props.appUrl}/usuarios/${usuario.id_usuario}`"
          class="result-card"
        >
          <div class="result-icon">👤</div>
          <div class="result-content">
            <h3>{{ usuario.nombre }} {{ usuario.apellido }}</h3>
            <p>{{ usuario.email }}</p>
            <span :class="'badge badge-' + getTipoClass(usuario.tipo_usuario)">
              {{ usuario.tipo_usuario }}
            </span>
          </div>
        </Link>
      </div>
    </div>

    <!-- Vehículos -->
    <div v-if="resultados.vehiculos.length > 0" class="results-section">
      <h2>🚗 Vehículos ({{ resultados.vehiculos.length }})</h2>
      <div class="results-grid">
        <Link
          v-for="vehiculo in resultados.vehiculos"
          :key="vehiculo.id_vehiculo"
          :href="`${$page.props.appUrl}/vehiculos/${vehiculo.id_vehiculo}`"
          class="result-card"
        >
          <div class="result-icon">🚗</div>
          <div class="result-content">
            <h3>{{ vehiculo.placa }}</h3>
            <p>{{ vehiculo.marca }} {{ vehiculo.modelo }}</p>
            <span :class="'badge badge-' + (vehiculo.estado === 'DISPONIBLE' ? 'success' : 'warning')">
              {{ vehiculo.estado }}
            </span>
          </div>
        </Link>
      </div>
    </div>

    <!-- Rutas -->
    <div v-if="resultados.rutas.length > 0" class="results-section">
      <h2>🗺️ Rutas ({{ resultados.rutas.length }})</h2>
      <div class="results-grid">
        <div
          v-for="ruta in resultados.rutas"
          :key="ruta.id_ruta"
          class="result-card"
        >
          <div class="result-icon">🗺️</div>
          <div class="result-content">
            <h3>{{ ruta.nombre_ruta }}</h3>
            <p>{{ ruta.origen?.ciudad }} → {{ ruta.destino?.ciudad }}</p>
            <span class="badge badge-info">{{ ruta.distancia_km }} km</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sin resultados -->
    <div v-if="!tieneResultados" class="no-results">
      <div class="no-results-icon">🔍</div>
      <h2>No se encontraron resultados</h2>
      <p>Intenta con otros términos de búsqueda</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  query: String,
  resultados: Object,
});

const tieneResultados = computed(() => {
  return props.resultados.paquetes.length > 0 ||
         props.resultados.usuarios.length > 0 ||
         props.resultados.vehiculos.length > 0 ||
         props.resultados.rutas.length > 0;
});

const getEstadoClass = (estado) => {
  const classes = {
    'REGISTRADO': 'info',
    'EN_TRANSITO': 'warning',
    'ENTREGADO': 'success',
    'CANCELADO': 'danger',
  };
  return classes[estado] || 'secondary';
};

const getTipoClass = (tipo) => {
  const classes = {
    'PROPIETARIO': 'primary',
    'SECRETARIA': 'info',
    'CHOFER': 'warning',
    'CLIENTE': 'secondary',
  };
  return classes[tipo] || 'secondary';
};
</script>

<style scoped>
.search-header {
  margin-bottom: 2rem;
}

.search-header h1 {
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.search-header p {
  color: var(--text-secondary);
}

.results-section {
  margin-bottom: 3rem;
}

.results-section h2 {
  color: var(--primary);
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--border-color);
}

.results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.result-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  text-decoration: none;
  color: var(--text-color);
  transition: all 0.3s;
}

.result-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  border-color: var(--primary);
}

.result-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.result-content {
  flex: 1;
}

.result-content h3 {
  margin: 0 0 0.5rem 0;
  color: var(--primary);
}

.result-content p {
  margin: 0 0 0.5rem 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: bold;
  display: inline-block;
}

.badge-primary { background: #6C63FF; color: white; }
.badge-info { background: #3498db; color: white; }
.badge-warning { background: #f39c12; color: white; }
.badge-success { background: #27ae60; color: white; }
.badge-danger { background: #e74c3c; color: white; }
.badge-secondary { background: #95a5a6; color: white; }

.no-results {
  text-align: center;
  padding: 4rem 2rem;
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
}

.no-results-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.no-results h2 {
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.no-results p {
  color: var(--text-secondary);
}
</style>
