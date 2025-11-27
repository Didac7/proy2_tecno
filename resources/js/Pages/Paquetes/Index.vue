<template>
  <AppLayout>
    <div class="page-header">
      <h1>📦 {{ getTitulo }}</h1>
      <Link v-if="canCreate" :href="`${$page.props.appUrl}/paquetes/create`" class="btn-primary">
        ➕ Nuevo Paquete
      </Link>
    </div>

    <!-- Filtros -->
    <div class="filters-card">
      <div class="filter-group">
        <input
          v-model="searchForm.search"
          type="text"
          placeholder="Buscar por código o destinatario..."
          class="filter-input"
          @keyup.enter="buscar"
        />
        <button @click="buscar" class="btn-search">🔍 Buscar</button>
      </div>

      <div class="filter-group" v-if="!isCliente && !isChofer">
        <select v-model="searchForm.estado" @change="buscar" class="filter-select">
          <option value="">Todos los estados</option>
          <option value="REGISTRADO">Registrado</option>
          <option value="EN_TRANSITO">En Tránsito</option>
          <option value="ENTREGADO">Entregado</option>
          <option value="CANCELADO">Cancelado</option>
        </select>
      </div>
    </div>

    <!-- Tabla de Paquetes -->
    <div class="table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Código</th>
              <th v-if="!isChofer">Cliente</th>
              <th>Destino</th>
              <th v-if="!isChofer">Estado</th>
              <th>Contenido</th>
              <th v-if="!isChofer">Precio</th>
              <th v-if="!isChofer">Fecha</th>
              <th v-if="!isCliente">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="paquete in paquetes.data" :key="paquete.id_paquete">
              <td>
                <strong class="codigo">{{ paquete.codigo_seguimiento }}</strong>
              </td>
              <td v-if="!isChofer">
                <div class="cliente-info">
                  <strong>{{ paquete.remitente?.nombre }} {{ paquete.remitente?.apellido }}</strong>
                  <span class="arrow">→</span>
                  <span v-if="paquete.destinatario">
                    {{ paquete.destinatario.nombre }} {{ paquete.destinatario.apellido }}
                  </span>
                  <span v-else>
                    {{ paquete.nombre_destinatario }}
                  </span>
                </div>
              </td>
              <td>
                <span class="ruta">
                  {{ paquete.ruta?.destino?.ciudad }}
                </span>
                <br>
                <small class="text-secondary">{{ paquete.direccion_entrega }}</small>
              </td>
              <td v-if="!isChofer">
                <span :class="'badge badge-' + getEstadoClass(paquete.estado_paquete)">
                  {{ paquete.estado_paquete }}
                </span>
              </td>
              <td>{{ paquete.descripcion_contenido }}</td>
              <td v-if="!isChofer">Bs. {{ formatMoney(paquete.precio) }}</td>
              <td v-if="!isChofer">{{ formatDate(paquete.fecha_registro) }}</td>
              <td v-if="!isCliente">
                <div class="action-buttons">
                  <Link :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}`" class="btn-icon" title="Ver">
                    Ver
                  </Link>
                  <Link 
                    v-if="canEdit" 
                    :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}/edit`" 
                    class="btn-icon" 
                    title="Editar"
                  >
                    Editar
                  </Link>
                  <Link 
                    v-if="!paquete.pago || paquete.pago.estado_pago !== 'PAGADO'" 
                    :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}/pagar`" 
                    class="btn-icon btn-pagar" 
                    title="Pagar"
                  >
                    💳
                  </Link>
                  <span v-else class="badge badge-success" title="Pagado">✓ Pagado</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="pagination" v-if="paquetes.links.length > 3">
        <Link
          v-for="link in paquetes.links"
          :key="link.label"
          :href="link.url"
          :class="{ active: link.active, disabled: !link.url }"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  paquetes: Object,
  filters: Object,
});

const page = usePage();
const isCliente = computed(() => page.props.auth?.user?.tipo_usuario === 'CLIENTE');
const isChofer = computed(() => page.props.auth?.user?.tipo_usuario === 'CHOFER');
const canCreate = computed(() => !isCliente.value && !isChofer.value);
const canEdit = computed(() => !isCliente.value && !isChofer.value);

const getTitulo = computed(() => {
  if (isCliente.value) return 'Mis Paquetes';
  if (isChofer.value) return 'Paquetes Asignados';
  return 'Gestión de Paquetes';
});

const searchForm = ref({
  search: props.filters?.search || '',
  estado: props.filters?.estado || '',
});


const buscar = () => {
  router.get(`${page.props.appUrl}/paquetes`, searchForm.value, {
    preserveState: true,
  });
};

const getEstadoClass = (estado) => {
  const classes = {
    'REGISTRADO': 'info',
    'EN_TRANSITO': 'warning',
    'ENTREGADO': 'success',
    'CANCELADO': 'danger',
  };
  return classes[estado] || 'secondary';
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('es-BO').format(value || 0);
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('es-BO');
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
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  gap: 0.5rem;
  flex: 1;
  min-width: 250px;
}

.filter-input,
.filter-select {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
}

.btn-search {
  padding: 0.75rem 1.5rem;
  background: var(--secondary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-weight: bold;
}

.table-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
}

.table-responsive {
  overflow-x: auto;
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
  color: var(--primary);
}

.data-table tbody tr:hover {
  background: var(--bg-secondary);
}

.codigo {
  color: var(--primary);
  font-family: monospace;
}

.ruta {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.text-secondary {
  color: var(--text-secondary);
  font-size: 0.85rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: bold;
}

.badge-info { background: #3498db; color: white; }
.badge-warning { background: #f39c12; color: white; }
.badge-success { background: #27ae60; color: white; }
.badge-danger { background: #e74c3c; color: white; }

.cliente-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
}

.cliente-info .arrow {
  color: var(--primary);
  margin: 0 0.25rem;
  font-weight: bold;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  text-decoration: none;
  font-size: 1.2rem;
  transition: all 0.3s;
}

.btn-icon:hover {
  background: var(--primary);
  transform: scale(1.1);
}

.btn-pagar {
  background: var(--success);
  color: white;
}

.btn-pagar:hover {
  background: var(--primary);
  transform: scale(1.1);
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
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
