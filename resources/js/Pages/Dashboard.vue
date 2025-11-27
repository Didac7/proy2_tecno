<template>
  <AppLayout>
    <div class="dashboard">
      <h1>📊 Dashboard - Trans Velasco</h1>
      
      <!-- Estadísticas Principales -->
      <div v-if="!es_chofer" class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📦</div>
          <div class="stat-info">
            <h3>{{ stats.total_paquetes }}</h3>
            <p>Total Paquetes</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">🚚</div>
          <div class="stat-info">
            <h3>{{ stats.paquetes_en_transito }}</h3>
            <p>En Tránsito</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-info">
            <h3>{{ stats.paquetes_entregados }}</h3>
            <p>Entregados</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-info">
            <h3>{{ stats.total_clientes }}</h3>
            <p>Clientes</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">🚗</div>
          <div class="stat-info">
            <h3>{{ stats.vehiculos_activos }}</h3>
            <p>Vehículos Activos</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">💰</div>
          <div class="stat-info">
            <h3>Bs. {{ formatMoney(stats.pagos_pendientes) }}</h3>
            <p>Pagos Pendientes</p>
          </div>
        </div>
      </div>

      <!-- Widget GPS para Choferes -->
      <div v-if="$page.props.auth.user.tipo_usuario === 'CHOFER'" class="gps-widget">
        <h2>📍 Rastreo GPS</h2>
        <div class="gps-card">
          <div v-if="!gpsEstado.tiene_vehiculo" class="gps-no-vehiculo">
            <p>⚠️ No tienes un vehículo asignado</p>
          </div>
          <div v-else>
            <div class="gps-status">
              <div class="status-indicator" :class="{ active: gpsEstado.gps_activo }">
                <span class="status-dot"></span>
                <span>{{ gpsEstado.gps_activo ? 'GPS Activo' : 'GPS Inactivo' }}</span>
              </div>
              <div v-if="gpsEstado.ultima_actualizacion" class="last-update">
                Última actualización: {{ formatTime(gpsEstado.ultima_actualizacion) }}
              </div>
            </div>

            <div class="gps-info" v-if="gpsEstado.vehiculo">
              <p><strong>Vehículo:</strong> {{ gpsEstado.vehiculo.placa }} - {{ gpsEstado.vehiculo.modelo }}</p>
            </div>

            <div class="gps-actions">
              <button 
                v-if="!gpsEstado.gps_activo" 
                @click="iniciarGPS" 
                :disabled="gpsLoading"
                class="btn-gps btn-start">
                🚚 Iniciar Viaje
              </button>
              <button 
                v-else 
                @click="finalizarGPS" 
                :disabled="gpsLoading"
                class="btn-gps btn-stop">
                🏁 Finalizar Viaje
              </button>
            </div>

            <div v-if="gpsError" class="gps-error">
              {{ gpsError }}
            </div>
          </div>
        </div>
      </div>

      <!-- Gráfico de Paquetes por Estado -->
      <div v-if="!es_chofer" class="chart-section">
        <h2>📈 Paquetes por Estado</h2>
        <div class="chart-container">
          <div 
            v-for="item in paquetes_por_estado" 
            :key="item.estado_paquete"
            class="chart-bar"
          >
            <div class="bar-label">{{ item.estado_paquete }}</div>
            <div class="bar-wrapper">
              <div 
                class="bar-fill" 
                :style="{ width: getBarWidth(item.total) + '%' }"
              >
                {{ item.total }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Paquetes Recientes -->
      <div class="recent-section">
        <h2>{{ es_chofer ? '📦 Mis Paquetes Asignados' : '📋 Paquetes Recientes' }}</h2>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Código</th>
                <th v-if="!es_chofer">Cliente</th>
                <th v-if="es_chofer">Remitente</th>
                <th v-if="es_chofer">Descripción</th>
                <th>Ruta</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th v-if="!es_chofer">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="paquete in paquetes_recientes" :key="paquete.id_paquete">
                <td><strong>{{ paquete.codigo_seguimiento }}</strong></td>
                <td v-if="!es_chofer">{{ paquete.cliente?.nombre }} {{ paquete.cliente?.apellido }}</td>
                <td v-if="es_chofer">{{ paquete.remitente?.nombre }} {{ paquete.remitente?.apellido }}</td>
                <td v-if="es_chofer">{{ paquete.descripcion_contenido || 'Sin descripción' }}</td>
                <td>
                  {{ paquete.ruta?.origen?.nombre_destino }} → {{ paquete.ruta?.destino?.nombre_destino }}
                </td>
                <td>
                  <span :class="'badge badge-' + getEstadoClass(paquete.estado_paquete)">
                    {{ paquete.estado_paquete }}
                  </span>
                </td>
                <td>{{ formatDate(paquete.fecha_registro) }}</td>
                <td v-if="!es_chofer">
                  <Link :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}`" class="btn-small">
                    Ver
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

// Formatear dinero
const formatMoney = (value) => {
  return new Intl.NumberFormat('es-BO').format(value || 0);
};

// Formatear fecha
const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('es-BO');
};

// Obtener clase de estado
const getEstadoClass = (estado) => {
  const classes = {
    'REGISTRADO': 'info',
    'EN_TRANSITO': 'warning',
    'ENTREGADO': 'success',
    'CANCELADO': 'danger',
  };
  return classes[estado] || 'secondary';
};

// Calcular ancho de barra
const getBarWidth = (value) => {
  const max = Math.max(...props.paquetes_por_estado.map(p => p.total));
  return (value / max) * 100;
};
</script>

<style scoped>
.dashboard {
  padding: 2rem 0;
}

.dashboard h1 {
  margin-bottom: 2rem;
  color: var(--primary);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.stat-icon {
  font-size: 3rem;
}

.stat-info h3 {
  margin: 0;
  font-size: 2rem;
  color: var(--primary);
}

.stat-info p {
  margin: 0.5rem 0 0;
  color: var(--text-secondary);
}

.chart-section,
.recent-section {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 2rem;
  margin-bottom: 2rem;
}

.chart-container {
  margin-top: 1rem;
}

.chart-bar {
  margin-bottom: 1rem;
}

.bar-label {
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.bar-wrapper {
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  overflow: hidden;
}

.bar-fill {
  background: var(--primary);
  color: white;
  padding: 0.5rem;
  text-align: right;
  font-weight: bold;
  transition: width 0.5s;
}

.table-responsive {
  overflow-x: auto;
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

.btn-small {
  padding: 0.5rem 1rem;
  background: var(--primary);
  color: white;
  text-decoration: none;
  border-radius: var(--border-radius);
  font-size: 0.875rem;
}

.btn-small:hover {
  opacity: 0.8;
}

/* GPS Widget Styles */
.gps-widget {
  margin: 2rem 0;
}

.gps-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
}

.gps-no-vehiculo {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.gps-status {
  margin-bottom: 1rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--danger);
  animation: pulse 2s infinite;
}

.status-indicator.active .status-dot {
  background: var(--success);
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.last-update {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.gps-info {
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
}

.gps-actions {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.btn-gps {
  padding: 1rem 2rem;
  font-size: 1.1rem;
  font-weight: bold;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.3s;
}

.btn-start {
  background: var(--success);
  color: white;
}

.btn-stop {
  background: var(--danger);
  color: white;
}

.btn-gps:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.btn-gps:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.gps-error {
  margin-top: 1rem;
  padding: 0.75rem;
  background: var(--danger-light);
  color: var(--danger);
  border-radius: var(--border-radius);
  text-align: center;
}
</style>
