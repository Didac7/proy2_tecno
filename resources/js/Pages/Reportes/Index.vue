<template>
  <AppLayout>
    <div class="page-header">
      <h1>📊 Reportes y Estadísticas</h1>
      <a href="/reportes/pdf" class="btn-primary" target="_blank">
        📥 Descargar PDF
      </a>
    </div>

    <div class="reports-nav">
      <Link 
        href="/reportes" 
        class="nav-item" 
        :class="{ active: !activeTab }"
      >
        Resumen
      </Link>
      <Link 
        href="/reportes/diario" 
        class="nav-item" 
        :class="{ active: activeTab === 'diario' }"
      >
        📅 Diario
      </Link>
      <Link 
        href="/reportes/clientes" 
        class="nav-item" 
        :class="{ active: activeTab === 'clientes' }"
      >
        👥 Clientes
      </Link>
      <Link 
        href="/reportes/vehiculos" 
        class="nav-item" 
        :class="{ active: activeTab === 'vehiculos' }"
      >
        🚗 Vehículos
      </Link>
      <Link 
        href="/reportes/rutas" 
        class="nav-item" 
        :class="{ active: activeTab === 'rutas' }"
      >
        🛣️ Rutas
      </Link>
    </div>

    <div class="report-content">
      <!-- Resumen General -->
      <div v-if="!activeTab && stats" class="dashboard-grid">
        <!-- Tarjetas de Estadísticas -->
        <div class="stats-row">
          <div class="stat-card income">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
              <h3>Ingresos Hoy</h3>
              <p class="stat-value">Bs. {{ formatMoney(stats.ingresos_hoy) }}</p>
              <small>Mes: Bs. {{ formatMoney(stats.ingresos_mes) }}</small>
            </div>
          </div>
          <div class="stat-card packages">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
              <h3>Paquetes Hoy</h3>
              <p class="stat-value">{{ stats.paquetes_hoy }}</p>
              <small>Mes: {{ stats.paquetes_mes }}</small>
            </div>
          </div>
          <div class="stat-card transit">
            <div class="stat-icon">🚚</div>
            <div class="stat-info">
              <h3>En Tránsito</h3>
              <p class="stat-value">{{ stats.paquetes_en_transito }}</p>
              <small>Entregados Mes: {{ stats.paquetes_entregados_mes }}</small>
            </div>
          </div>
          <div class="stat-card clients">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
              <h3>Clientes Nuevos</h3>
              <p class="stat-value">{{ stats.clientes_nuevos_hoy }}</p>
              <small>Total: {{ stats.total_clientes }}</small>
            </div>
          </div>
        </div>

        <!-- Segunda Fila: Top Rutas y Clientes -->
        <div class="dashboard-row">
          <div class="dashboard-card">
            <h3>🏆 Top 5 Rutas</h3>
            <table class="mini-table">
              <thead>
                <tr>
                  <th>Ruta</th>
                  <th>Envíos</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ruta in topRutas" :key="ruta.id_ruta">
                  <td>{{ ruta.origen?.ciudad }} → {{ ruta.destino?.ciudad }}</td>
                  <td><strong>{{ ruta.paquetes_count }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="dashboard-card">
            <h3>⭐ Top 5 Clientes</h3>
            <table class="mini-table">
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Envíos</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cliente in topClientes" :key="cliente.id_usuario">
                  <td>{{ cliente.nombre }} {{ cliente.apellido }}</td>
                  <td><strong>{{ cliente.paquetes_count }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tercera Fila: Estado de Vehículos -->
        <div class="dashboard-row">
          <div class="dashboard-card full-width">
            <h3>🚛 Rendimiento de Vehículos</h3>
            <div class="vehicles-list">
              <div v-for="vehiculo in vehiculosRendimiento" :key="vehiculo.id_vehiculo" class="vehicle-item">
                <div class="vehicle-info">
                  <span class="plate">{{ vehiculo.placa }}</span>
                  <span class="driver">{{ vehiculo.chofer?.nombre || 'Sin Chofer' }}</span>
                </div>
                <div class="vehicle-stats">
                  <div class="progress-bar">
                    <div 
                      class="progress-fill" 
                      :style="{ width: Math.min(vehiculo.paquetes_count * 2, 100) + '%' }"
                    ></div>
                  </div>
                  <span class="count">{{ vehiculo.paquetes_count }} envíos</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reporte Diario -->
      <div v-if="activeTab === 'diario'" class="table-container">
        <h3>Paquetes Registrados Hoy</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Destinatario</th>
              <th>Ruta</th>
              <th>Estado</th>
              <th>Valor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data" :key="item.id_paquete">
              <td><strong>{{ item.codigo_seguimiento }}</strong></td>
              <td>{{ item.destinatario?.nombre }} {{ item.destinatario?.apellido }}</td>
              <td>{{ item.ruta?.origen?.ciudad }} → {{ item.ruta?.destino?.ciudad }}</td>
              <td><span class="badge">{{ item.estado_paquete }}</span></td>
              <td>Bs. {{ formatMoney(item.valor_declarado) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reporte Clientes -->
      <div v-if="activeTab === 'clientes'" class="table-container">
        <h3>Top Clientes</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Email</th>
              <th>Total Paquetes Recibidos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data" :key="item.id_usuario">
              <td>{{ item.nombre }} {{ item.apellido }}</td>
              <td>{{ item.email }}</td>
              <td><strong>{{ item.paquetes_count }}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reporte Vehículos -->
      <div v-if="activeTab === 'vehiculos'" class="table-container">
        <h3>Estado de Vehículos</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Placa</th>
              <th>Modelo</th>
              <th>Estado</th>
              <th>Paquetes en Tránsito</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data" :key="item.id_vehiculo">
              <td><strong>{{ item.placa }}</strong></td>
              <td>{{ item.marca }} {{ item.modelo }}</td>
              <td>
                <span :class="'badge badge-' + (item.estado === 'DISPONIBLE' ? 'success' : 'warning')">
                  {{ item.estado }}
                </span>
              </td>
              <td>{{ item.paquetes_count }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reporte Rutas -->
      <div v-if="activeTab === 'rutas'" class="table-container">
        <h3>Rutas Más Utilizadas</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>Origen</th>
              <th>Destino</th>
              <th>Total Envíos</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data" :key="item.id_ruta">
              <td>{{ item.origen?.ciudad }}</td>
              <td>{{ item.destino?.ciudad }}</td>
              <td><strong>{{ item.paquetes_count }}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  stats: Object,
  activeTab: String,
  data: Array,
  paquetesPorEstado: Array,
  ingresos7Dias: Array,
  topRutas: Array,
  topClientes: Array,
  vehiculosRendimiento: Array
});

const formatMoney = (value) => {
  return new Intl.NumberFormat('es-BO').format(value || 0);
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

.reports-nav {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.nav-item {
  padding: 0.75rem 1.5rem;
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  text-decoration: none;
  color: var(--text-color);
  font-weight: bold;
  white-space: nowrap;
  transition: all 0.3s;
}

.nav-item:hover, .nav-item.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius);
  text-decoration: none;
  font-weight: bold;
  font-size: 0.9rem;
  transition: opacity 0.3s;
}

.btn-primary:hover {
  opacity: 0.9;
}

/* Dashboard Grid */
.dashboard-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: var(--card-bg);
  padding: 1.5rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  font-size: 2.5rem;
  background: var(--bg-secondary);
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.stat-info h3 {
  margin: 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--text-color);
  margin: 0.25rem 0;
}

.stat-info small {
  color: var(--text-secondary);
  font-size: 0.8rem;
}

/* Variantes de tarjetas */
.stat-card.income .stat-value { color: #2ecc71; }
.stat-card.packages .stat-value { color: #3498db; }
.stat-card.transit .stat-value { color: #f1c40f; }
.stat-card.clients .stat-value { color: #9b59b6; }

.dashboard-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.dashboard-card {
  background: var(--card-bg);
  padding: 1.5rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
}

.dashboard-card.full-width {
  grid-column: 1 / -1;
}

.dashboard-card h3 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: var(--primary);
  border-bottom: 2px solid var(--bg-secondary);
  padding-bottom: 0.5rem;
}

/* Mini Tablas */
.mini-table {
  width: 100%;
  border-collapse: collapse;
}

.mini-table th, .mini-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.mini-table th {
  color: var(--text-secondary);
  font-size: 0.9rem;
}

/* Lista de Vehículos */
.vehicles-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.vehicle-item {
  background: var(--bg-secondary);
  padding: 1rem;
  border-radius: var(--border-radius);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.vehicle-info {
  display: flex;
  flex-direction: column;
}

.plate {
  font-weight: bold;
  color: var(--primary);
}

.driver {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.vehicle-stats {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
  width: 120px;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: var(--border-color);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--primary);
  border-radius: 3px;
}

.count {
  font-size: 0.8rem;
  font-weight: bold;
}

/* Tablas Generales */
.table-container {
  background: var(--card-bg);
  padding: 1.5rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
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

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.85rem;
  font-weight: bold;
  background: var(--bg-secondary);
  color: var(--text-color);
}

.badge-success { background: #27ae60; color: white; }
.badge-warning { background: #f39c12; color: white; }
</style>
