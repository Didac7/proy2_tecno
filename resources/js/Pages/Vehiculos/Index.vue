<template>
  <AppLayout>
    <div class="page-header">
      <h1>🚗 {{ isChofer ? 'Mi Vehículo' : 'Gestión de Vehículos' }}</h1>
      <Link v-if="!isChofer" :href="`${$page.props.appUrl}/vehiculos/create`" class="btn-primary">
        ➕ Nuevo Vehículo
      </Link>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <div class="filter-group">
        <select v-model="searchForm.estado" @change="buscar" class="filter-select">
          <option value="">Todos los estados</option>
          <option value="DISPONIBLE">Disponible</option>
          <option value="EN_RUTA">En Ruta</option>
          <option value="MANTENIMIENTO">Mantenimiento</option>
        </select>
      </div>
    </div>

    <!-- Grid de Vehículos -->
    <div class="vehicles-grid">
      <div v-for="vehiculo in vehiculos.data" :key="vehiculo.id_vehiculo" class="vehicle-card">
        <div class="vehicle-header">
          <div class="vehicle-icon">🚗</div>
          <span :class="'badge badge-' + getEstadoClass(vehiculo.estado)">
            {{ vehiculo.estado }}
          </span>
        </div>
        
        <div class="vehicle-body">
          <h3>{{ vehiculo.placa }}</h3>
          <p class="vehicle-model">{{ vehiculo.marca }} {{ vehiculo.modelo }}</p>
          <p class="vehicle-year">Año: {{ vehiculo.año }}</p>
          
          <div class="vehicle-info">
            <div class="info-item">
              <span class="label">Tipo:</span>
              <span class="value">{{ vehiculo.tipo_vehiculo }}</span>
            </div>
            <div class="info-item">
              <span class="label">Capacidad:</span>
              <span class="value">{{ vehiculo.capacidad_carga }} kg</span>
            </div>
            <div class="info-item">
              <span class="label">Chofer:</span>
              <span class="value">
                {{ vehiculo.chofer?.nombre || 'Sin asignar' }}
              </span>
            </div>
            <div class="info-item">
              <span class="label">GPS:</span>
              <span :class="vehiculo.gps_activo ? 'text-success' : 'text-danger'">
                {{ vehiculo.gps_activo ? '✅ Activo' : '❌ Inactivo' }}
              </span>
            </div>
          </div>
        </div>

        <div class="vehicle-footer">
          <Link :href="`${$page.props.appUrl}/vehiculos/${vehiculo.id_vehiculo}`" class="btn-small">
            Ver
          </Link>
          <Link v-if="!isChofer" :href="`${$page.props.appUrl}/vehiculos/${vehiculo.id_vehiculo}/edit`" class="btn-small">
            Editar
          </Link>
        </div>
      </div>
    </div>

    <!-- Paginación -->
    <div class="pagination" v-if="vehiculos.links.length > 3">
      <Link
        v-for="link in vehiculos.links"
        :key="link.label"
        :href="link.url"
        :class="{ active: link.active, disabled: !link.url }"
        v-html="link.label"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  vehiculos: Object,
  filters: Object,
});

const page = usePage();
const isChofer = computed(() => page.props.auth?.user?.tipo_usuario === 'CHOFER');

const searchForm = ref({
  estado: props.filters?.estado || '',
});

const buscar = () => {
  router.get(`${page.props.appUrl}/vehiculos`, searchForm.value, {
    preserveState: true,
  });
};

const getEstadoClass = (estado) => {
  const classes = {
    'DISPONIBLE': 'success',
    'EN_RUTA': 'warning',
    'MANTENIMIENTO': 'danger',
  };
  return classes[estado] || 'secondary';
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
  margin: 0;
  color: var(--primary);
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: var(--primary);
  color: white;
  text-decoration: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  transition: all 0.3s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.filters-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.filter-group {
  display: flex;
  gap: 0.5rem;
}

.filter-select {
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  min-width: 200px;
}

.vehicles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.vehicle-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  overflow: hidden;
  transition: all 0.3s;
}

.vehicle-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  border-color: var(--primary);
}

.vehicle-header {
  background: var(--bg-secondary);
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.vehicle-icon {
  font-size: 2rem;
}

.vehicle-body {
  padding: 1.5rem;
}

.vehicle-body h3 {
  margin: 0 0 0.5rem 0;
  color: var(--primary);
  font-size: 1.5rem;
  font-family: monospace;
}

.vehicle-model {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 0.25rem;
}

.vehicle-year {
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.vehicle-info {
  display: grid;
  gap: 0.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border-color);
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  font-weight: bold;
  color: var(--text-secondary);
}

.value {
  color: var(--text-color);
}

.text-success {
  color: #27ae60;
  font-weight: bold;
}

.text-danger {
  color: #e74c3c;
  font-weight: bold;
}

.vehicle-footer {
  padding: 1rem;
  background: var(--bg-secondary);
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-small {
  padding: 0.5rem 1rem;
  background: var(--primary);
  color: white;
  text-decoration: none;
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  transition: all 0.3s;
}

.btn-small:hover {
  opacity: 0.8;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: bold;
}

.badge-success { background: #27ae60; color: white; }
.badge-warning { background: #f39c12; color: white; }
.badge-danger { background: #e74c3c; color: white; }

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.pagination a {
  padding: 0.5rem 1rem;
  background: var(--bg-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  text-decoration: none;
  color: var(--text-color);
  transition: all 0.3s;
}

.pagination a:hover:not(.disabled) {
  background: var(--primary);
  color: white;
}

.pagination a.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.pagination a.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
