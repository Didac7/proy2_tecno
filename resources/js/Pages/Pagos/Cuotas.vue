<template>
  <AppLayout>
    <div class="cuotas-container">
      <div class="cuotas-card">
        <div class="cuotas-header">
          <h1>📅 Plan de Cuotas</h1>
          <p>Paquete: {{ pago.paquete?.codigo_seguimiento }}</p>
        </div>

        <!-- Resumen del Plan -->
        <div class="plan-info">
          <h3>📊 Resumen del Plan</h3>
          <p><strong>Total:</strong> Bs. {{ pago.monto_total }}</p>
          <p><strong>Cuotas:</strong> {{ plan.numero_cuotas }} x Bs. {{ plan.monto_cuota }}</p>
          <p><strong>Pagadas:</strong> {{ plan.cuotas_pagadas }} / {{ plan.numero_cuotas }}</p>
        </div>

        <!-- Lista de Cuotas -->
        <div class="cuotas-list">
          <h3>💳 Cuotas</h3>
          <div v-for="cuota in cuotas" :key="cuota.id_cuota" class="cuota-item">
            <div class="cuota-info">
              <h4>Cuota {{ cuota.numero_cuota }}</h4>
              <p>Monto: Bs. {{ cuota.monto }}</p>
              <p>Vence: {{ formatearFecha(cuota.fecha_vencimiento) }}</p>
            </div>
            <div class="cuota-actions">
              <span v-if="cuota.estado === 'PAGADA'" class="badge-pagado">
                ✅ Pagada el {{ formatearFecha(cuota.fecha_pago) }}
              </span>
              <div v-else class="actions-group">
                <button @click="pagarCuota(cuota.id_cuota)" class="btn-pagar-cuota" :disabled="loading">
                  💳 Pagar con QR
                </button>
                
                <!-- Opción de cobro en efectivo para secretaria -->
                <button v-if="$page.props.auth.user.tipo_usuario !== 'CLIENTE'" 
                        @click="pagarCuotaEfectivo(cuota.id_cuota)" 
                        class="btn-pagar-efectivo" 
                        :disabled="loading">
                  💰 Cobrar Efectivo
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal QR -->
        <div v-if="qrData" class="modal-overlay" @click="cerrarModal">
          <div class="modal-content" @click.stop>
            <h3>📱 Escanea para pagar Cuota {{ cuotaSeleccionada?.numero_cuota }}</h3>
            <div class="qr-container">
              <img :src="'data:image/png;base64,' + qrData.qrBase64" alt="QR Cuota" />
            </div>
            <p>Monto: <strong>Bs. {{ cuotaSeleccionada?.monto }}</strong></p>
            <button @click="cerrarModal" class="btn-cerrar">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
  pago: Object,
  plan: Object,
  cuotas: Array,
});

const loading = ref(false);
const qrData = ref(null);
const cuotaSeleccionada = ref(null);

const pagarCuota = async (idCuota) => {
  loading.value = true;
  try {
    const response = await axios.post(`/api/cuotas/${idCuota}/pagar`);
    if (response.data.success) {
      qrData.value = response.data.qr;
      cuotaSeleccionada.value = props.cuotas.find(c => c.id_cuota === idCuota);
    }
  } catch (error) {
    alert('Error al generar QR');
  } finally {
    loading.value = false;
  }
};

const pagarCuotaEfectivo = async (idCuota) => {
  if (!confirm('¿Confirmar cobro en efectivo de esta cuota?')) return;
  
  loading.value = true;
  try {
    router.post(`/pagos/cuotas/${idCuota}/pagar-efectivo`, {}, {
      onSuccess: () => {
        alert('Pago de cuota registrado correctamente');
        // Recargar la página para mostrar el estado actualizado
        router.reload({ only: ['cuotas', 'plan'] });
      },
      onError: () => {
        alert('Error al registrar el pago');
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } catch (error) {
    alert('Error al procesar el pago');
    loading.value = false;
  }
};

const cerrarModal = () => {
  qrData.value = null;
  cuotaSeleccionada.value = null;
};

const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleDateString('es-BO');
};
</script>

<style scoped>
.cuotas-container {
  padding: 2rem;
}

.cuotas-card {
  max-width: 800px;
  margin: 0 auto;
  background: var(--card-bg);
  border-radius: var(--border-radius);
  padding: 2rem;
  border: 1px solid var(--border-color);
}

.plan-info {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.cuota-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.cuota-info h4 {
  margin: 0;
  color: var(--text-color);
}

.cuota-info p {
  margin: 0.25rem 0 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.actions-group {
  display: flex;
  gap: 0.5rem;
}

.btn-pagar-cuota {
  background: var(--primary);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
}

.btn-pagar-efectivo {
  background: var(--success);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius);
  cursor: pointer;
}

.badge-pagado {
  background: var(--success-light);
  color: var(--success);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius);
  text-align: center;
  max-width: 400px;
  width: 90%;
}

.qr-container img {
  max-width: 100%;
  margin: 1rem 0;
}

.btn-cerrar {
  margin-top: 1rem;
  padding: 0.5rem 2rem;
  background: var(--text-secondary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
}
</style>
