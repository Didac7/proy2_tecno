<template>
  <AppLayout>
    <div class="page-header">
      <h1>➕ Nuevo Paquete</h1>
      <Link :href="`${$page.props.appUrl}/paquetes`" class="btn-secondary">
        ← Volver
      </Link>
    </div>

    <div class="form-card">
      <form @submit.prevent="submit">
        <!-- Clientes -->
        <div class="form-section">
          <h2>👥 Remitente</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="remitente">Remitente (Cliente que envía) *</label>
              <select id="remitente" v-model="form.id_remitente" required>
                <option value="">Seleccione el remitente</option>
                <option v-for="cliente in clientes" :key="cliente.id_usuario" :value="cliente.id_usuario">
                  {{ cliente.nombre }} {{ cliente.apellido }} - CI: {{ cliente.ci }}
                </option>
              </select>
              <span v-if="form.errors.id_remitente" class="error">
                {{ form.errors.id_remitente }}
              </span>
            </div>
          </div>
        </div>

        <!-- Información del Paquete -->
        <div class="form-section">
          <h2>📦 Información del Paquete</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="codigo">Código de Seguimiento *</label>
              <input
                id="codigo"
                v-model="form.codigo_seguimiento"
                type="text"
                placeholder="TRV-2025-001"
                required
              />
              <span v-if="form.errors.codigo_seguimiento" class="error">
                {{ form.errors.codigo_seguimiento }}
              </span>
            </div>

            <div class="form-group">
              <label for="ruta">Ruta *</label>
              <select id="ruta" v-model="form.id_ruta" required>
                <option value="">Seleccione una ruta</option>
                <option v-for="ruta in rutas" :key="ruta.id_ruta" :value="ruta.id_ruta">
                  {{ ruta.origen?.ciudad }} → {{ ruta.destino?.ciudad }} ({{ ruta.distancia_km }} km)
                </option>
              </select>
              <span v-if="form.errors.id_ruta" class="error">
                {{ form.errors.id_ruta }}
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="vehiculo">Vehículo *</label>
              <select id="vehiculo" v-model="form.id_vehiculo" required>
                <option value="">Seleccione un vehículo</option>
                <option v-for="vehiculo in vehiculos" :key="vehiculo.id_vehiculo" :value="vehiculo.id_vehiculo">
                  {{ vehiculo.placa }} - {{ vehiculo.marca }} {{ vehiculo.modelo }} ({{ vehiculo.tipo_vehiculo }} - {{ vehiculo.capacidad_carga }} kg)
                </option>
              </select>
              <span v-if="form.errors.id_vehiculo" class="error">
                {{ form.errors.id_vehiculo }}
              </span>
            </div>

            <div class="form-group">
              <label for="tipo">Tipo de Paquete *</label>
              <select id="tipo" v-model="form.tipo_paquete" required>
                <option value="GENERAL">Normal</option>
                <option value="FRAGIL">Frágil</option>
                <option value="PERECEDERO">Perecedero</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="prioridad">Prioridad *</label>
              <select id="prioridad" v-model="form.prioridad" required>
                <option value="NORMAL">Normal</option>
                <option value="URGENTE">Urgente</option>
                <option value="EXPRESS">Express</option>
              </select>
            </div>

            <div class="form-group">
              <label for="descripcion">Descripción del Contenido *</label>
              <input
                id="descripcion"
                v-model="form.descripcion_contenido"
                type="text"
                placeholder="Ej: Documentos, ropa, electrónicos..."
                required
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="precio">Precio del Envío (Bs.) *</label>
              <input
                id="precio"
                v-model="form.precio"
                type="number"
                step="0.01"
                min="1"
                placeholder="0.00"
                required
              />
              <span class="help-text">Este será el monto que el cliente deberá pagar</span>
            </div>
          </div>
        </div>

        <!-- Información de Entrega -->
        <div class="form-section">
          <h2>📍 Dirección de Entrega</h2>
          
          <div class="form-group">
            <label for="entrega">Dirección de Entrega *</label>
            <input
              id="entrega"
              v-model="form.direccion_entrega"
              type="text"
              placeholder="Calle, número, zona..."
              required
            />
          </div>
        </div>

        <!-- Información del Destinatario -->
        <div class="form-section">
          <h2>👤 Destinatario</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="nombre_dest">Nombre del Destinatario *</label>
              <input
                id="nombre_dest"
                v-model="form.nombre_destinatario"
                type="text"
                placeholder="Nombre completo"
                required
              />
            </div>

            <div class="form-group">
              <label for="telefono_dest">Teléfono del Destinatario *</label>
              <input
                id="telefono_dest"
                v-model="form.telefono_destinatario"
                type="tel"
                placeholder="70123456"
                required
              />
            </div>
          </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
          <button type="submit" class="btn-primary" :disabled="form.processing">
            {{ form.processing ? 'Guardando...' : '💾 Guardar Paquete' }}
          </button>
          <Link :href="`${$page.props.appUrl}/paquetes`" class="btn-cancel">
            Cancelar
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  rutas: Array,
  vehiculos: Array,
  clientes: Array,
});

const page = usePage();

const form = useForm({
  id_remitente: '',
  codigo_seguimiento: '',
  id_ruta: '',
  id_vehiculo: '',
  descripcion_contenido: '',
  precio: '',
  tipo_paquete: 'GENERAL',
  prioridad: 'NORMAL',
  direccion_entrega: '',
  nombre_destinatario: '',
  telefono_destinatario: '',
});

const submit = () => {
  form.post(`${page.props.appUrl}/paquetes`);
};
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: var(--bg-secondary);
  color: var(--text-color);
  text-decoration: none;
  border-radius: var(--border-radius);
  border: 2px solid var(--border-color);
  font-weight: bold;
}

.form-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 2px solid var(--border-color);
}

.form-section:last-of-type {
  border-bottom: none;
}

.form-section h2 {
  color: var(--primary);
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: bold;
  color: var(--text-color);
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--bg-secondary);
  color: var(--text-color);
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
}

.error {
  color: #e74c3c;
  font-size: 0.875rem;
}

.help-text {
  color: var(--text-secondary);
  font-size: 0.875rem;
  font-style: italic;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-primary {
  padding: 1rem 2rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel {
  padding: 1rem 2rem;
  background: transparent;
  color: var(--text-color);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  text-decoration: none;
  font-weight: bold;
}
</style>
