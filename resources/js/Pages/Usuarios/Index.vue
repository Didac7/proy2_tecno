<template>
  <AppLayout>
    <div class="page-header">
      <h1>👥 Gestión de Usuarios</h1>
      <Link href="/usuarios/create" class="btn-primary">
        ➕ Nuevo Usuario
      </Link>
    </div>

    <div class="filters-bar">
      <div class="search-box">
        <input 
          v-model="search" 
          type="text" 
          placeholder="Buscar por nombre, email o CI..."
          @input="handleSearch"
        >
      </div>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nombre Completo</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Teléfono</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="usuario in usuarios.data" :key="usuario.id_usuario">
            <td>
              <div class="user-info">
                <span class="user-name">{{ usuario.nombre }} {{ usuario.apellido }}</span>
                <span class="user-ci">CI: {{ usuario.ci }}</span>
              </div>
            </td>
            <td>{{ usuario.email }}</td>
            <td>
              <span :class="'badge badge-' + getRoleClass(usuario.tipo_usuario)">
                {{ usuario.tipo_usuario }}
              </span>
            </td>
            <td>{{ usuario.telefono || '-' }}</td>
            <td>
              <span :class="usuario.estado === 'ACTIVO' ? 'status-active' : 'status-inactive'">
                {{ usuario.estado === 'ACTIVO' ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="actions">
              <Link :href="`/usuarios/${usuario.id_usuario}/edit`" class="btn-icon" title="Editar">
                Editar
              </Link>
              <button 
                @click="deleteUsuario(usuario)" 
                class="btn-icon delete" 
                title="Eliminar"
              >
                Eliminar
              </button>
            </td>
          </tr>
          <tr v-if="usuarios.data.length === 0">
            <td colspan="6" class="text-center">No se encontraron usuarios</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="pagination" v-if="usuarios.links.length > 3">
      <Link
        v-for="(link, key) in usuarios.links"
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
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  usuarios: Object,
  filters: Object
});

const search = ref(props.filters.search || '');
const tipoUsuario = ref(props.filters.tipo_usuario || '');

// Función debounce personalizada
const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

const handleSearch = debounce(() => {
  router.get('/usuarios', { 
    search: search.value,
    tipo_usuario: tipoUsuario.value
  }, { 
    preserveState: true, 
    replace: true 
  });
}, 300);

const deleteUsuario = (usuario) => {
  if (confirm(`¿Estás seguro de eliminar a ${usuario.nombre} ${usuario.apellido}?`)) {
    router.delete(`/usuarios/${usuario.id_usuario}`);
  }
};

const getRoleClass = (role) => {
  const classes = {
    'PROPIETARIO': 'purple',
    'SECRETARIA': 'blue',
    'CHOFER': 'orange',
    'CLIENTE': 'green'
  };
  return classes[role] || 'gray';
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
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  background: var(--card-bg);
  padding: 1rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
}

.search-box {
  flex: 1;
}

.search-box input,
.filter-box select {
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

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: bold;
  color: var(--text-color);
}

.user-ci {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.85rem;
  font-weight: bold;
}

.badge-purple { background: #9b59b6; color: white; }
.badge-blue { background: #3498db; color: white; }
.badge-orange { background: #e67e22; color: white; }
.badge-green { background: #2ecc71; color: white; }
.badge-gray { background: #95a5a6; color: white; }

.status-active {
  color: #2ecc71;
  font-weight: bold;
}

.status-inactive {
  color: #e74c3c;
  font-weight: bold;
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
</style>
