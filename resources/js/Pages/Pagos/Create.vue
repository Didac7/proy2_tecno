<template>
  <AppLayout>
    <div class="checkout-container">
      <div class="checkout-card">
        <div class="checkout-header">
          <h1>💳 Pagar Envío</h1>
          <p>Paquete: {{ paquete.codigo_seguimiento }}</p>
        </div>

        <!-- Detalles del Paquete -->
        <div class="paquete-details">
          <h3>📦 Detalles del Envío</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="label">Remitente:</span>
              <span class="value">{{ paquete.remitente?.nombre }} {{ paquete.remitente?.apellido }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Destinatario:</span>
              <span class="value">{{ paquete.nombre_destinatario }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Ruta:</span>
              <span class="value">{{ paquete.ruta?.origen?.nombre_destino }} → {{ paquete.ruta?.destino?.nombre_destino }}</span>
            </div>
          </div>
        </div>

        <!-- Monto a Pagar -->
        <div class="monto-section">
          <h3>💰 Monto Total</h3>
          <div class="monto-display">
            <span class="currency">Bs.</span>
            <span class="amount">{{ monto.toFixed(2) }}</span>
          </div>
        </div>

        <!-- Selector de Tipo de Pago -->
        <div v-if="!pagoCreado || (pagoExistente && pagoExistente.estado_pago === 'PENDIENTE')" class="tipo-pago-section">
          <h3>🎯 Tipo de Pago</h3>
          <div class="tipo-pago-options">
            <label class="tipo-option" :class="{ active: form.tipo_pago === 'CONTADO' }">
              <input type="radio" v-model="form.tipo_pago" value="CONTADO" />
              <div class="option-content">
                <span class="option-icon">💵</span>
                <span class="option-label">Pago al Contado</span>
                <span class="option-desc">Paga el total ahora</span>
              </div>
            </label>

            <label class="tipo-option" :class="{ active: form.tipo_pago === 'CREDITO' }">
              <input type="radio" v-model="form.tipo_pago" value="CREDITO" />
              <div class="option-content">
                <span class="option-icon">📅</span>
                <span class="option-label">Pago a Crédito</span>
                <span class="option-desc">Paga en cuotas mensuales</span>
              </div>
            </label>
          </div>

          <!-- Selector de Cuotas (solo si es crédito) -->
          <div v-if="form.tipo_pago === 'CREDITO'" class="cuotas-section">
            <h4>Selecciona el número de cuotas:</h4>
            <div class="cuotas-options">
              <label v-for="opcion in opcionesCuotas" :key="opcion.value" 
                     class="cuota-option" 
                     :class="{ active: form.numero_cuotas === opcion.value }">
                <input type="radio" v-model="form.numero_cuotas" :value="opcion.value" />
                <div class="cuota-content">
                  <span class="cuota-label">{{ opcion.label }}</span>
                  <span class="cuota-monto">Bs. {{ calcularMontoCuota(opcion.value) }}</span>
                  <span class="cuota-desc">por mes</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Selector de Método de Cobro (Solo Secretaria/Admin) -->
          <div v-if="$page.props.auth.user.tipo_usuario !== 'CLIENTE'" class="metodo-cobro-section">
            <h3>💳 Método de Cobro</h3>
            <div class="metodo-options">
              <label class="metodo-option" :class="{ active: form.metodo_cobro === 'QR' }">
                <input type="radio" v-model="form.metodo_cobro" value="QR" />
                <span class="metodo-icon">📱</span>
                <span class="metodo-label">Generar QR (PagoFácil)</span>
              </label>
              <label class="metodo-option" :class="{ active: form.metodo_cobro === 'EFECTIVO' }">
                <input type="radio" v-model="form.metodo_cobro" value="EFECTIVO" />
                <span class="metodo-icon">💰</span>
                <span class="metodo-label">Cobro en Efectivo</span>
              </label>
            </div>
          </div>

          <!-- Botón Crear/Actualizar Pago -->
          <div class="action-section">
            <button @click="crearPago" :disabled="loading || !validarFormulario()" class="btn-crear-pago">
              {{ loading ? '⏳ Procesando...' : getButtonText() }}
            </button>
          </div>
        </div>

        <!-- Generar QR (solo si es QR y ya se creó el pago) -->
        <div v-if="((pagoCreado || pagoExistente) && form.metodo_cobro === 'QR' && !qrData)" class="action-section">
          <button @click="generarQR" :disabled="loading" class="btn-generar-qr">
            {{ loading ? '⏳ Generando QR...' : '🎯 Generar Código QR' }}
          </button>
        </div>

        <!-- QR Code Display -->
        <div v-if="qrData" class="qr-section">
          <h3>📱 Escanea el código QR</h3>
          <div class="qr-container">
            <img :src="'data:image/png;base64,' + qrData.qrBase64" alt="Código QR de PagoFácil" class="qr-image" />
          </div>
          <p class="qr-instruction">
            Abre tu aplicación de banco y escanea este código para pagar 
            <strong>Bs. {{ monto.toFixed(2) }}</strong>
          </p>
          <p class="expiration-note">Este código expira en unas horas.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
  paquete: Object,
  monto: Number,
  pagoExistente: Object,
  opcionesCuotas: Array,
});

const form = reactive({
  id_paquete: props.paquete.id_paquete,
  tipo_pago: props.pagoExistente ? props.pagoExistente.tipo_pago : 'CONTADO',
  numero_cuotas: 3,
  metodo_cobro: 'QR', // QR o EFECTIVO
  observaciones: '',
});

const qrData = ref(null);
const loading = ref(false);
const error = ref(null);
const estadoPago = ref(props.pagoExistente ? props.pagoExistente.estado_pago : 'PENDIENTE');
const pagoCreado = ref(!!props.pagoExistente);

const calcularMontoCuota = (numeroCuotas) => {
  return (props.monto / numeroCuotas).toFixed(2);
};

const validarFormulario = () => {
  if (form.tipo_pago === 'CREDITO' && !form.numero_cuotas) {
    return false;
  }
  return true;
};

const getButtonText = () => {
  if (pagoCreado.value) return '🔄 Actualizar Método de Pago';
  if (form.metodo_cobro === 'EFECTIVO') return '✓ Registrar Cobro Manual';
  return '✓ Confirmar Pago';
};

const crearPago = async () => {
  loading.value = true;
  error.value = null;

  try {
    router.post('/pagos', form, {
      onSuccess: () => {
        pagoCreado.value = true;
        if (form.tipo_pago === 'CREDITO' && form.metodo_cobro === 'QR') {
          // Redirigirá automáticamente a la vista de cuotas
        } else if (form.metodo_cobro === 'QR') {
          // Generar QR automáticamente para pagos al contado con QR
          setTimeout(() => {
            generarQR();
          }, 500);
        }
        // Si es EFECTIVO, el backend redirige
      },
      onError: (errors) => {
        error.value = Object.values(errors).join(', ');
        loading.value = false;
      },
    });
  } catch (err) {
    error.value = 'Error al crear el pago';
    loading.value = false;
  }
};

const generarQR = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.post(`/api/pagos/${props.paquete.id_paquete}/generar-qr`);
    
    if (response.data.success) {
      qrData.value = response.data.qr;
    } else {
      error.value = response.data.message || 'Error al generar el código QR';
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al conectar con el servidor de pagos';
  } finally {
    loading.value = false;
  }
};

const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleString('es-BO');
};
</script>

<style scoped>
.checkout-container {
  padding: 2rem;
}

.checkout-card {
  max-width: 900px;
  margin: 0 auto;
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 2rem;
}

.checkout-header {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--border-color);
}

.checkout-header h1 {
  color: var(--primary);
  margin: 0 0 0.5rem 0;
}

.paquete-details, .monto-section, .tipo-pago-section, .qr-section {
  margin-bottom: 2rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item .label {
  font-weight: bold;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.monto-display {
  text-align: center;
  padding: 2rem;
  background: var(--bg-secondary);
  border-radius: var(--border-radius);
  border: 3px solid var(--primary);
}

.amount {
  font-size: 3rem;
  font-weight: bold;
  color: var(--primary);
}

.tipo-pago-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.tipo-option {
  cursor: pointer;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  transition: all 0.3s;
}

.tipo-option.active {
  border-color: var(--primary);
  background: var(--bg-secondary);
}

.tipo-option input[type="radio"] {
  display: none;
}

.option-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.option-icon {
  font-size: 2rem;
}

.option-label {
  font-weight: bold;
  color: var(--text-color);
}

.option-desc {
  font-size: 0.9rem;
  color: var(--text-secondary);
  text-align: center;
}

.cuotas-options {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.cuota-option {
  cursor: pointer;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1rem;
  text-align: center;
  transition: all 0.3s;
}

.cuota-option.active {
  border-color: var(--success);
  background: var(--bg-secondary);
}

.cuota-option input[type="radio"] {
  display: none;
}

.cuota-monto {
  display: block;
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--primary);
  margin: 0.5rem 0;
}

.action-section {
  text-align: center;
  margin-top: 2rem;
}

.btn-crear-pago, .btn-generar-qr {
  background: var(--primary);
  color: white;
  border: none;
  padding: 1rem 2rem;
  font-size: 1.2rem;
  font-weight: bold;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.3s;
}

.btn-crear-pago:hover:not(:disabled), .btn-generar-qr:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.btn-crear-pago:disabled, .btn-generar-qr:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.qr-container {
  display: flex;
  justify-content: center;
  margin: 2rem 0;
  padding: 2rem;
  background: white;
  border-radius: var(--border-radius);
}

.qr-image {
  max-width: 300px;
  width: 100%;
}

.error-message {
  background: #fee;
  color: #c00;
  padding: 1rem;
  border-radius: var(--border-radius);
  border-left: 4px solid #c00;
  margin-top: 1rem;
}

.metodo-cobro-section {
  margin-bottom: 2rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.metodo-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.metodo-option {
  cursor: pointer;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s;
}

.metodo-option.active {
  border-color: var(--primary);
  background: var(--bg-secondary);
}

.metodo-option input[type="radio"] {
  display: none;
}

.metodo-icon {
  font-size: 1.5rem;
}

.metodo-label {
  font-weight: bold;
  color: var(--text-color);
}

.manual-fields {
  background: var(--bg-secondary);
  padding: 1.5rem;
  border-radius: var(--border-radius);
  margin-top: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-input {
  padding: 0.8rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--card-bg);
  color: var(--text-color);
  font-family: inherit;
  resize: vertical;
  min-height: 80px;
}

@media (max-width: 768px) {
  .tipo-pago-options, .cuotas-options, .metodo-options {
    grid-template-columns: 1fr;
  }
}
</style>
