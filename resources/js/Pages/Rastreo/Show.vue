<template>
  <div class="rastreo-container">
    <div class="rastreo-header">
      <h1>🔍 Rastreo de Paquete</h1>
      <p class="codigo">{{ paquete.codigo_seguimiento }}</p>
    </div>

    <!-- Mapa -->
    <div class="map-wrapper">
      <div id="map" class="map-container"></div>
    </div>

    <!-- Panel de Información -->
    <div class="info-panels">
      <!-- Información del Paquete -->
      <div class="info-card">
        <h3>📦 Información del Paquete</h3>
        <div class="info-grid">
          <div class="info-item">
            <span class="label">Contenido:</span>
            <span class="value">{{ paquete.contenido }}</span>
          </div>
          <div class="info-item">
            <span class="label">Peso:</span>
            <span class="value">{{ paquete.peso }} kg</span>
          </div>
          <div class="info-item">
            <span class="label">Remitente:</span>
            <span class="value">{{ paquete.remitente?.nombre }} {{ paquete.remitente?.apellido }}</span>
          </div>
          <div class="info-item">
            <span class="label">Destinatario:</span>
            <span class="value">{{ paquete.nombre_destinatario }}</span>
          </div>
        </div>
      </div>

      <!-- Información de la Ruta -->
      <div class="info-card">
        <h3>🗺️ Ruta</h3>
        <div class="ruta-info">
          <div class="ruta-punto">
            <span class="icon">📍</span>
            <div>
              <strong>Origen</strong>
              <p>{{ paquete.ruta?.origen?.nombre_destino }}</p>
            </div>
          </div>
          <div class="ruta-linea"></div>
          <div class="ruta-punto">
            <span class="icon">🏁</span>
            <div>
              <strong>Destino</strong>
              <p>{{ paquete.ruta?.destino?.nombre_destino }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Estado del Vehículo -->
      <div class="info-card">
        <h3>🚚 Estado del Vehículo</h3>
        <div v-if="vehiculoUbicacion" class="vehiculo-activo">
          <p class="status-activo">✅ En tránsito</p>
          <p class="last-update">Última actualización: {{ formatTime(lastUpdate) }}</p>
        </div>
        <div v-else class="vehiculo-inactivo">
          <p class="status-inactivo">📍 Paquete entregado</p>
        </div>
        <div v-if="paquete.vehiculo" class="vehiculo-info">
          <p><strong>Vehículo:</strong> {{ paquete.vehiculo.placa }}</p>
          <p><strong>Chofer:</strong> {{ paquete.vehiculo.chofer?.nombre }} {{ paquete.vehiculo.chofer?.apellido }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
  paquete: Object,
  origen: Object,
  destino: Object,
  vehiculoUbicacion: Object,
});

let map = null;
let vehiculoMarker = null;
let routeLine = null;
const lastUpdate = ref(new Date());
let updateInterval = null;

onMounted(() => {
  initMap();
  
  // Si hay ubicación del vehículo, iniciar polling cada 10 segundos
  if (props.vehiculoUbicacion && props.paquete.vehiculo) {
    startLocationPolling();
  }
});

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval);
  }
});

const initMap = () => {
  // Crear mapa centrado entre origen y destino
  const centerLat = (props.origen.lat + props.destino.lat) / 2;
  const centerLng = (props.origen.lng + props.destino.lng) / 2;

  map = L.map('map').setView([centerLat, centerLng], 6);

  // Agregar capa de OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Marcador de origen (verde)
  L.marker([props.origen.lat, props.origen.lng], {
    icon: L.divIcon({
      className: 'custom-marker',
      html: '<div style="background: #27ae60; color: white; padding: 8px; border-radius: 50%; font-size: 20px;">📍</div>',
      iconSize: [40, 40],
      iconAnchor: [20, 20]
    })
  }).addTo(map).bindPopup(`<strong>Origen:</strong> ${props.paquete.ruta.origen.nombre_destino}`);

  // Marcador de destino (rojo)
  L.marker([props.destino.lat, props.destino.lng], {
    icon: L.divIcon({
      className: 'custom-marker',
      html: '<div style="background: #e74c3c; color: white; padding: 8px; border-radius: 50%; font-size: 20px;">🏁</div>',
      iconSize: [40, 40],
      iconAnchor: [20, 20]
    })
  }).addTo(map).bindPopup(`<strong>Destino:</strong> ${props.paquete.ruta.destino.nombre_destino}`);

  // Línea de ruta
  routeLine = L.polyline([
    [props.origen.lat, props.origen.lng],
    [props.destino.lat, props.destino.lng]
  ], {
    color: '#3498db',
    weight: 3,
    opacity: 0.7,
    dashArray: '10, 10'
  }).addTo(map);

  // Marcador del vehículo (si está activo)
  if (props.vehiculoUbicacion) {
    updateVehicleMarker(props.vehiculoUbicacion);
  }

  // Ajustar vista para mostrar toda la ruta
  map.fitBounds([
    [props.origen.lat, props.origen.lng],
    [props.destino.lat, props.destino.lng]
  ], { padding: [50, 50] });
};

const updateVehicleMarker = (ubicacion) => {
  if (vehiculoMarker) {
    // Actualizar posición existente
    vehiculoMarker.setLatLng([ubicacion.lat, ubicacion.lng]);
  } else {
    // Crear nuevo marcador
    vehiculoMarker = L.marker([ubicacion.lat, ubicacion.lng], {
      icon: L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #3498db; color: white; padding: 8px; border-radius: 50%; font-size: 20px;">🚚</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
      })
    }).addTo(map).bindPopup('<strong>Vehículo en tránsito</strong>');
  }
  
  lastUpdate.value = new Date();
};

const startLocationPolling = () => {
  // Actualizar ubicación cada 10 segundos
  updateInterval = setInterval(async () => {
    try {
      const response = await fetch(`/api/gps/vehiculo/${props.paquete.vehiculo.id_vehiculo}`);
      const data = await response.json();
      
      if (data.success && data.gps_activo && data.latitud && data.longitud) {
        updateVehicleMarker({
          lat: data.latitud,
          lng: data.longitud
        });
      } else {
        // GPS inactivo, detener polling y remover marcador
        if (vehiculoMarker) {
          map.removeLayer(vehiculoMarker);
          vehiculoMarker = null;
        }
        clearInterval(updateInterval);
      }
    } catch (error) {
      console.error('Error al obtener ubicación:', error);
    }
  }, 10000); // 10 segundos
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('es-BO');
};
</script>

<style scoped>
.rastreo-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.rastreo-header {
  text-align: center;
  margin-bottom: 2rem;
}

.rastreo-header h1 {
  color: var(--primary);
  margin-bottom: 0.5rem;
}

.codigo {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--text-color);
}

.map-wrapper {
  margin-bottom: 2rem;
}

.map-container {
  height: 500px;
  border-radius: var(--border-radius);
  border: 2px solid var(--border-color);
  overflow: hidden;
}

.info-panels {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.info-card {
  background: var(--card-bg);
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius);
  padding: 1.5rem;
}

.info-card h3 {
  color: var(--primary);
  margin-bottom: 1rem;
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 0.5rem;
}

.info-grid {
  display: grid;
  gap: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border-color);
}

.info-item .label {
  font-weight: bold;
  color: var(--text-secondary);
}

.ruta-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.ruta-punto {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.ruta-punto .icon {
  font-size: 2rem;
}

.ruta-linea {
  width: 2px;
  height: 30px;
  background: var(--border-color);
  margin-left: 1rem;
}

.vehiculo-activo, .vehiculo-inactivo {
  padding: 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
}

.vehiculo-activo {
  background: var(--success-light);
}

.vehiculo-inactivo {
  background: var(--bg-secondary);
}

.status-activo {
  color: var(--success);
  font-weight: bold;
  font-size: 1.1rem;
  margin: 0 0 0.5rem 0;
}

.status-inactivo {
  color: var(--text-secondary);
  font-weight: bold;
  font-size: 1.1rem;
  margin: 0;
}

.last-update {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin: 0;
}

.vehiculo-info {
  margin-top: 0.5rem;
}

.vehiculo-info p {
  margin: 0.25rem 0;
}

@media (max-width: 768px) {
  .map-container {
    height: 350px;
  }
  
  .info-panels {
    grid-template-columns: 1fr;
  }
}
</style>
