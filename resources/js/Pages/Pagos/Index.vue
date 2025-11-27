<template>
  <AppLayout>
    <div class="page-header">
      <h1>💰 Gestión de Pagos</h1>
    </div>

    <!-- Filtros -->
    <div class="filters-bar">
      <div class="search-box">
        <input 
          v-model="search" 
          type="text" 
          placeholder="Buscar por transacción o código..."
          @input="debouncedSearch"
        >
      </div>
      
      <div class="filter-group">
        <select v-model="estadoFilter" @change="applyFilters">
          <option value="">Todos los estados</option>
          <option value="PENDIENTE">Pendiente</option>
          <option value="PAGADO">Pagado</option>
          <option value="CANCELADO">Cancelado</option>
        </select>
      </div>
      
      <div class="filter-group" v-if="clientes.length > 0">
        <select v-model="clienteFilter" @change="applyFilters">
          <option value="">Todos los clientes</option>
          <option v-for="cliente in clientes" :key="cliente.id_usuario" :value="cliente.id_usuario">
            {{ cliente.nombre }} {{ cliente.apellido }} - CI: {{ cliente.ci }}
          </option>
        </select>
      </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Paquete</th>
            <th>Cliente</th>
            <th>Monto</th>
            <th>Método</th>
            <th>Estado</th>
            <th>Fecha Pago</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pago in pagos.data" :key="pago.id_pago">
            <td>#{{ pago.id_pago }}</td>
            <td>
              <Link :href="`/paquetes/${pago.id_paquete}`" class="link-paquete">
                {{ pago.paquete?.codigo_seguimiento }}
              </Link>
            </td>
            <td>
              <div class="cliente-info">
                <div><strong>Remitente:</strong> {{ pago.paquete?.remitente?.nombre }} {{ pago.paquete?.remitente?.apellido }}</div>
                <div v-if="pago.paquete?.destinatario"><strong>Destinatario:</strong> {{ pago.paquete?.destinatario?.nombre }} {{ pago.paquete?.destinatario?.apellido }}</div>
                <div v-else><strong>Destinatario:</strong> {{ pago.paquete?.nombre_destinatario }}</div>
              </div>
            </td>
            <td class="amount">Bs. {{ formatMoney(pago.monto_total) }}</td>
            <td>
              <span v-if="pago.tipo_pago === 'CREDITO'" class="badge badge-info">
                📅 A Cuotas
              </span>
              <span v-else class="badge badge-secondary">
                💵 Contado
              </span>
            </td>
            <td>
              <span :class="'badge badge-' + getEstadoClass(pago.estado_pago)">
                {{ pago.estado_pago }}
              </span>
            </td>
            <td>
              <span v-if="pago.fecha_pago">{{ formatDateTime(pago.fecha_pago) }}</span>
              <span v-else class="text-muted">Pendiente</span>
            </td>
            <td>
              <Link 
                v-if="pago.estado_pago === 'PENDIENTE'"
                :href="`/paquetes/${pago.id_paquete}/pagar`" 
                class="btn-action btn-pay"
                title="Pagar ahora"
              >
                💳 Pagar
              </Link>
              <span v-else class="text-muted">-</span>
            </td>
          </tr>
          <tr v-if="pagos.data.length === 0">
            <td colspan="8" class="empty-state">
              No se encontraron pagos registrados.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="pagination" v-if="pagos.links.length > 3">
      <Link
        v-for="(link, key) in pagos.links"
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
  pagos: Object,
  clientes: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const estadoFilter = ref(props.filters.estado || '');
const clienteFilter = ref(props.filters.cliente || '');

const applyFilters = () => {
  router.get('/pagos', { 
    search: search.value,
    estado: estadoFilter.value,
    cliente: clienteFilter.value
  }, { 
    preserveState: true,
    preserveScroll: true,
    replace: true 
  });
};

// Función debounce simple para evitar dependencia de lodash
const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

const debouncedSearch = debounce(() => {
  applyFilters();
}, 300);

const getEstadoClass = (estado) => {
  const classes = {
    'PENDIENTE': 'warning',
    'PAGADO': 'success',
    'CANCELADO': 'danger',
    'RECHAZADO': 'danger',
  };
  return classes[estado] || 'secondary';
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('es-BO').format(value || 0);
};

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('es-BO');
};
</script>

<style scoped>
.page-header {
  margin-bottom: 2rem;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box input,
.filter-group select {
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1rem;
}

.search-box {
  flex: 1;
  min-width: 250px;
}

.search-box input {
  width: 100%;
}

.table-container {
  background: var(--card-bg);
  border-radius: var(--border-radius);
  border: 2px solid var(--border-color);
  overflow-x: auto;
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
  color: var(--text-secondary);
  font-weight: bold;
}

.data-table tr:last-child td {
  border-bottom: none;
}

.link-paquete {
  color: var(--primary);
  font-weight: bold;
  text-decoration: none;
}

.link-paquete:hover {
  text-decoration: underline;
}

.amount {
  font-family: monospace;
  font-weight: bold;
}

.cliente-info {
  font-size: 0.9rem;
}

.cliente-info div {
  margin: 0.25rem 0;
}

.cliente-info strong {
  color: var(--text-secondary);
  font-size: 0.85rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: bold;
  color: white;
}

.badge-warning { background: var(--warning); color: #000; }
.badge-success { background: var(--success); }
.badge-danger { background: var(--danger); }
.badge-secondary { background: #95a5a6; }

.btn-action {
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: bold;
  display: inline-block;
}

.btn-pay {
  background: var(--primary);
  color: white;
}

.btn-pay:hover {
  opacity: 0.9;
}

.text-muted {
  color: var(--text-secondary);
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--text-secondary);
  font-style: italic;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 2rem;
}

.page-link {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  text-decoration: none;
  color: var(--text-color);
  background: var(--bg-secondary);
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
</style>
