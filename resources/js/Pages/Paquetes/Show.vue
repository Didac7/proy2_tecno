<template>
  <AppLayout>
    <div class="page-header">
      <h1>📦 Detalle del Paquete</h1>
      <Link :href="`${$page.props.appUrl}/paquetes`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="detail-grid">
      <!-- Información Principal -->
      <div class="detail-card">
        <h2>📋 Información del Paquete</h2>
        <div class="detail-row">
          <span class="label">Código:</span>
          <span class="value codigo">{{ paquete.codigo_seguimiento }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Estado:</span>
          <span :class="'badge badge-' + getEstadoClass(paquete.estado_paquete)">
            {{ paquete.estado_paquete }}
          </span>
        </div>
        <div class="detail-row">
          <span class="label">Tipo:</span>
          <span class="value">{{ paquete.tipo_paquete }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Prioridad:</span>
          <span class="value">{{ paquete.prioridad }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Descripción:</span>
          <span class="value">{{ paquete.descripcion_contenido }}</span>
        </div>
      </div>

      <!-- Cliente -->
      <div class="detail-card">
        <h2>👤 Cliente</h2>
        <div class="detail-row">
          <span class="label">Nombre:</span>
          <span class="value">{{ paquete.cliente?.nombre }} {{ paquete.cliente?.apellido }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Email:</span>
          <span class="value">{{ paquete.cliente?.email }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Teléfono:</span>
          <span class="value">{{ paquete.cliente?.telefono }}</span>
        </div>
      </div>

      <!-- Ruta -->
      <div class="detail-card">
        <h2>🗺️ Ruta</h2>
        <div class="detail-row">
          <span class="label">Origen:</span>
          <span class="value">{{ paquete.ruta?.origen?.ciudad }}, {{ paquete.ruta?.origen?.departamento }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Destino:</span>
          <span class="value">{{ paquete.ruta?.destino?.ciudad }}, {{ paquete.ruta?.destino?.departamento }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Distancia:</span>
          <span class="value">{{ paquete.ruta?.distancia_km }} km</span>
        </div>
      </div>

      <!-- Vehículo -->
      <div class="detail-card">
        <h2>🚗 Vehículo Asignado</h2>
        <div class="detail-row">
          <span class="label">Placa:</span>
          <span class="value">{{ paquete.vehiculo?.placa }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Vehículo:</span>
          <span class="value">{{ paquete.vehiculo?.marca }} {{ paquete.vehiculo?.modelo }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Chofer:</span>
          <span class="value">{{ paquete.vehiculo?.chofer?.nombre }} {{ paquete.vehiculo?.chofer?.apellido }}</span>
        </div>
      </div>

      <!-- Estado de Pago -->
      <div class="detail-card payment-status-card">
        <h2>💰 Estado de Pago</h2>
        <div class="detail-row">
          <span class="label">Precio del Envío:</span>
          <span class="value precio-envio">Bs. {{ formatMoney(paquete.precio) }}</span>
        </div>
        <div v-if="paquete.pago" class="payment-info">
          <div class="detail-row">
            <span class="label">Estado:</span>
            <span :class="'badge badge-' + getEstadoPagoClass(paquete.pago.estado_pago)">
              {{ paquete.pago.estado_pago }}
            </span>
          </div>
          <div class="detail-row">
            <span class="label">Tipo de Pago:</span>
            <span class="value">{{ paquete.pago.tipo_pago }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Monto Pagado:</span>
            <span class="value">Bs. {{ formatMoney(paquete.pago.monto_pagado) }}</span>
          </div>
          <div v-if="paquete.pago.tipo_pago === 'CREDITO' && paquete.pago.plan_pago" class="detail-row">
            <span class="label">Cuotas Pagadas:</span>
            <span class="value">{{ paquete.pago.plan_pago.cuotas_pagadas }} / {{ paquete.pago.plan_pago.numero_cuotas }}</span>
          </div>
          <div v-if="paquete.pago.fecha_pago" class="detail-row">
            <span class="label">Fecha de Pago:</span>
            <span class="value">{{ formatDateTime(paquete.pago.fecha_pago) }}</span>
          </div>
          <div v-if="paquete.pago.tipo_pago === 'CREDITO'" class="cuotas-link">
            <Link :href="`${$page.props.appUrl}/pagos/${paquete.pago.id_pago}/cuotas`" class="btn-ver-cuotas">
              📅 Ver Plan de Cuotas
            </Link>
          </div>
        </div>
        <div v-else class="no-payment">
          <p>⚠️ Este paquete aún no tiene un pago registrado</p>
          <Link :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}/pagar`" class="btn-crear-pago">
            💳 Crear Pago
          </Link>
        </div>
      </div>

      <!-- Destinatario -->
      <div class="detail-card">
        <h2>📍 Destinatario</h2>
        <div class="detail-row">
          <span class="label">Nombre:</span>
          <span class="value">{{ paquete.nombre_destinatario }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Teléfono:</span>
          <span class="value">{{ paquete.telefono_destinatario }}</span>
        </div>
        <div class="detail-row">
          <span class="label">Dirección Entrega:</span>
          <span class="value">{{ paquete.direccion_entrega }}</span>
        </div>
      </div>
    </div>

    <!-- Seguimiento -->
    <div class="detail-card full-width">
      <h2>📍 Historial de Seguimiento</h2>
      <div class="timeline">
        <div 
          v-for="(seg, index) in paquete.seguimientos" 
          :key="seg.id_seguimiento"
          class="timeline-item"
        >
          <div class="timeline-marker">{{ index + 1 }}</div>
          <div class="timeline-content">
            <div class="timeline-header">
              <strong>{{ seg.estado_seguimiento }}</strong>
              <span class="timeline-date">{{ formatDateTime(seg.fecha_hora) }}</span>
            </div>
            <p>{{ seg.ubicacion_actual }}</p>
            <p v-if="seg.descripcion" class="timeline-desc">{{ seg.descripcion }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Acciones -->
    <div class="actions-bar">
      <Link 
        :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}/pagar`" 
        class="btn-payment"
      >
        💳 Pagar Envío
      </Link>
      <Link 
        v-if="canEdit" 
        :href="`${$page.props.appUrl}/paquetes/${paquete.id_paquete}/edit`" 
        class="btn-primary"
      >
        ✏️ Editar
      </Link>
      <button 
        v-if="canDeliver && paquete.estado_paquete !== 'ENTREGADO'" 
        @click="marcarEntregado"
        class="btn-success"
      >
        ✅ Marcar como Entregado
      </button>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  paquete: Object,
});

const canEdit = true;
const canDeliver = true;
const page = usePage();

const getEstadoClass = (estado) => {
  const classes = {
    'REGISTRADO': 'info',
    'EN_TRANSITO': 'warning',
    'ENTREGADO': 'success',
    'CANCELADO': 'danger',
  };
  return classes[estado] || 'secondary';
};

const getEstadoPagoClass = (estado) => {
  const classes = {
    'PENDIENTE': 'warning',
    'PARCIAL': 'info',
    'PAGADO': 'success',
    'ANULADO': 'danger',
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

const marcarEntregado = () => {
  if (confirm('¿Está seguro de marcar este paquete como entregado?')) {
    router.post(`${page.props.appUrl}/paquetes/${props.paquete.id_paquete}/entregar`);
  }
};
</script>

<style scoped>
.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.detail-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
}

.detail-card.full-width {
  grid-column: 1 / -1;
}

.detail-card h2 {
  color: var(--primary);
  margin-bottom: 1rem;
  font-size: 1.2rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
  border-bottom: none;
}

.label {
  font-weight: bold;
  color: var(--text-secondary);
}

.value {
  color: var(--text-color);
  text-align: right;
}

.codigo {
  font-family: monospace;
  color: var(--primary);
  font-weight: bold;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline-item {
  position: relative;
  padding-bottom: 2rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  width: 2rem;
  height: 2rem;
  background: var(--primary);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.timeline-content {
  background: var(--bg-secondary);
  padding: 1rem;
  border-radius: var(--border-radius);
  border-left: 3px solid var(--primary);
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.timeline-date {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.timeline-desc {
  margin-top: 0.5rem;
  font-style: italic;
  color: var(--text-secondary);
}

.actions-bar {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.btn-success {
  padding: 1rem 2rem;
  background: #27ae60;
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  cursor: pointer;
}

.btn-payment {
  padding: 1rem 2rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: all 0.3s;
}

.btn-payment:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.payment-status-card {
  border-left: 4px solid var(--primary);
}

.precio-envio {
  color: var(--primary);
  font-weight: bold;
  font-size: 1.1rem;
}

.payment-info {
  margin-top: 1rem;
}

.no-payment {
  text-align: center;
  padding: 2rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
}

.no-payment p {
  margin-bottom: 1rem;
  color: var(--text-secondary);
}

.btn-crear-pago, .btn-ver-cuotas {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: var(--primary);
  color: white;
  text-decoration: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  transition: all 0.3s;
}

.btn-crear-pago:hover, .btn-ver-cuotas:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.cuotas-link {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
  text-align: center;
}

.badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
  color: white;
  font-size: 0.875rem;
}

.badge-success {
  background: #27ae60;
}

.badge-warning {
  background: #f39c12;
}

.badge-danger {
  background: #e74c3c;
}

.badge-info {
  background: #3498db;
}

.badge-secondary {
  background: #95a5a6;
}

</style>
