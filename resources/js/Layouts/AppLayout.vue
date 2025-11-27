<template>
  <div>
    <!-- Panel de Accesibilidad Flotante -->
    <AccessibilityPanel />

    <header>
      <div class="header-content">
        <h1>🚚 Trans Velasco</h1>
        <div class="user-controls">
          <span>Bienvenido, {{ $page.props.auth.user.nombre }}</span>
          <Link :href="`${$page.props.appUrl}/logout`" method="post" as="button" class="btn-logout">Salir</Link>
        </div>
      </div>
    </header>

    <nav>
      <div v-for="item in $page.props.menuItems" :key="item.id_menu" class="nav-item-wrapper">
        <!-- Menú simple -->
        <Link 
          v-if="!item.hijos || item.hijos.length === 0" 
          :href="item.url" 
          class="nav-link"
        >
          {{ item.nombre }}
        </Link>

        <!-- Menú desplegable -->
        <div v-else class="dropdown">
          <button class="nav-link dropdown-toggle">
            {{ item.nombre }} ▾
          </button>
          <div class="dropdown-menu">
            <Link 
              v-for="hijo in item.hijos" 
              :key="hijo.id_menu" 
              :href="hijo.url"
              class="dropdown-item"
            >
              {{ hijo.nombre }}
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <main>
      <slot />
    </main>

    <footer>
      <p>© 2025 Trans Velasco - Grupo09sc</p>
      <p>Visitas: {{ $page.props.visitCount }}</p>
    </footer>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AccessibilityPanel from '@/Components/AccessibilityPanel.vue';
</script>

<style scoped>
header {
  background: var(--primary);
  color: white;
  padding: 1rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.user-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.theme-select {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  border: none;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
}

.theme-select option {
  background: var(--bg-primary);
  color: var(--text-color);
}

.btn-logout {
  background: rgba(255, 0, 0, 0.2);
  border: 1px solid rgba(255, 0, 0, 0.4);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-logout:hover {
  background: rgba(255, 0, 0, 0.4);
}

nav {
  background: var(--bg-secondary);
  padding: 0 1rem;
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  border-bottom: 1px solid var(--border-color);
  position: relative;
  z-index: 100;
}

.nav-item-wrapper {
  position: relative;
}

.nav-link {
  color: var(--text-color);
  text-decoration: none;
  padding: 1rem;
  display: inline-block;
  font-weight: bold;
  transition: all 0.3s;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  font-family: inherit;
}

.nav-link:hover {
  background: var(--primary);
  color: white;
}

.dropdown {
  position: relative;
}

.dropdown-menu {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  background: var(--card-bg);
  min-width: 200px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  border-radius: 0 0 4px 4px;
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.dropdown:hover .dropdown-menu {
  display: block;
}

.dropdown-item {
  color: var(--text-color);
  padding: 0.75rem 1rem;
  text-decoration: none;
  display: block;
  transition: background 0.2s;
}

.dropdown-item:hover {
  background: var(--bg-secondary);
  color: var(--primary);
}

main {
  min-height: calc(100vh - 200px);
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

footer {
  background: var(--bg-secondary);
  padding: 1rem;
  text-align: center;
  border-top: 1px solid var(--border-color);
  margin-top: auto;
}
</style>
