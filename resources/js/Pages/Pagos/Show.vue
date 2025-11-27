<template>
  <AppLayout>
    <div class="page-header">
      <h1>🧾 Detalle de Pago</h1>
      <Link :href="`${$page.props.appUrl}/pagos`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="detail-card">
      <div class="detail-header">
        <div class="status-badge" :class="pago.estado_pago === 'COMPLETADO' ? 'success' : 'warning'">
          {{ pago.estado_pago }}
        </div>
        <h2>Monto: Bs. {{ pago.monto_total }}</h2>
      </div>

      <div class="detail-grid">
        <div class="detail-item">
          <label>Código Paquete</label>
          <p>{{ pago.paquete?.codigo_seguimiento }}</p>
        </div>

        <div class="detail-item">
          <label>Cliente</label>
          <p>{{ pago.paquete?.cliente?.nombre }} {{ pago.paquete?.cliente?.apellido }}</p>
        </div>

        <div class="detail-item">
          <label>Método de Pago</label>
          <p>{{ pago.metodo_pago }}</p>
        </div>

        <div class="detail-item">
          <label>Fecha de Pago</label>
          <p>{{ formatDate(pago.fecha_pago) }}</p>
        </div>

        <div class="detail-item full-width">
          <label>Ruta</label>
          <p>{{ pago.paquete?.ruta?.origen?.ciudad }} → {{ pago.paquete?.ruta?.destino?.ciudad }}</p>
        </div>
      </div>

      <div class="actions-bar" v-if="pago.estado_pago === 'PENDIENTE'">
        <button @click="confirmarPago" class="btn-primary">
          ✅ Confirmar Pago
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  pago: Object
});

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const confirmarPago = () => {
  if (confirm('¿Está seguro de confirmar este pago?')) {
    router.post(`/pagos/${props.pago.id_pago}/registrar`);
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

.btn-secondary {
  color: var(--text-secondary);
  text-decoration: none;
  font-weight: bold;
}

.detail-card {
  background: var(--card-bg);
  padding: 2rem;
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  max-width: 800px;
  margin: 0 auto;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  font-weight: bold;
  color: white;
}

.status-badge.success { background: #2ecc71; }
.status-badge.warning { background: #f39c12; }

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
}

.detail-item label {
  display: block;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.detail-item p {
  font-size: 1.1rem;
  font-weight: bold;
  color: var(--text-color);
  margin: 0;
}

.full-width {
  grid-column: 1 / -1;
}

.actions-bar {
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
  text-align: right;
}

.btn-primary {
  background: var(--primary);
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: bold;
  cursor: pointer;
}
</style>
