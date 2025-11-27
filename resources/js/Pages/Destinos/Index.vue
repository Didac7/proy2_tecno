<template>
  <AppLayout>
    <div class="page-header">
      <h1>📍 Gestión de Destinos</h1>
      <Link :href="`${$page.props.appUrl}/destinos/create`" class="btn-primary">
        ➕ Nuevo Destino
      </Link>
    </div>

    <div class="filters-bar">
      <div class="search-box">
        <input 
          v-model="search" 
          type="text" 
          placeholder="Buscar por ciudad o dirección..."
          @input="handleSearch"
        >
      </div>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Ciudad</th>
            <th>Dirección</th>
            <th>Coordenadas</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="destino in destinos.data" :key="destino.id_destino">
            <td><strong>{{ destino.ciudad }}</strong></td>
            <td>{{ destino.direccion }}</td>
            <td>
              <span v-if="destino.latitud && destino.longitud" class="coords">
                {{ destino.latitud }}, {{ destino.longitud }}
              </span>
              <span v-else class="text-muted">No registradas</span>
            </td>
            <td class="actions">
              <Link :href="`${$page.props.appUrl}/destinos/${destino.id_destino}/edit`" class="btn-icon" title="Editar">
                Editar
              </Link>
              <button 
                @click="deleteDestino(destino)" 
                class="btn-icon delete" 
                title="Eliminar"
              >
                Eliminar
              </button>
            </td>
          </tr>
          <tr v-if="destinos.data.length === 0">
            <td colspan="4" class="text-center">No se encontraron destinos</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="pagination" v-if="destinos.links.length > 3">
      <Link
        v-for="(link, key) in destinos.links"
        :key="key"
        :href="link.url || '#'"
        class="page-link"
        :class="{ 'active': link.active, 'disabled': !link.url }"
        v-html="link.label"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  destinos: Object,
  filters: Object
});

const search = ref(props.filters.search || '');

// Función debounce personalizada
const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

const handleSearch = debounce(() => {
  router.get(`${page.props.appUrl}/destinos`, { 
    search: search.value 
  }, { 
    preserveState: true, 
    replace: true 
  });
}, 300);

const deleteDestino = (destino) => {
  if (confirm(`¿Estás seguro de eliminar el destino ${destino.ciudad}?`)) {
    router.delete(`/destinos/${destino.id_destino}`);
  }
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

.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius);
  text-decoration: none;
  font-weight: bold;
  transition: opacity 0.3s;
}

.btn-primary:hover {
  opacity: 0.9;
}

.filters-bar {
  margin-bottom: 2rem;
  background: var(--card-bg);
  padding: 1rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
}

.search-box input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
}

.table-container {
  background: var(--card-bg);
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  overflow: hidden;
  margin-bottom: 2rem;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.data-table th {
  background: var(--bg-secondary);
  font-weight: bold;
  color: var(--text-secondary);
}

.coords {
  font-family: monospace;
  background: var(--bg-secondary);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.25rem;
  transition: transform 0.2s;
  text-decoration: none;
}

.btn-icon:hover {
  transform: scale(1.2);
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.page-link {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--card-bg);
  color: var(--text-color);
  text-decoration: none;
}

.page-link.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.page-link.disabled {
  opacity: 0.5;
  pointer-events: none;
}

.text-center {
  text-align: center;
}

.text-muted {
  color: var(--text-secondary);
  font-style: italic;
}
</style>
