<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar (Desktop) -->
    <aside
      class="hidden md:flex flex-col w-64 h-screen fixed top-0 left-0 
             bg-gradient-to-b from-yellow-800 via-yellow-700 to-yellow-600 
             shadow-2xl transition-all duration-300 overflow-y-auto"
      :class="{ 'w-20': collapsed }"
    >
      <!-- Logo -->
      <div class="flex items-center justify-center p-6 border-b border-yellow-700">
        <img src="/logo.png" alt="Logo" class="h-12 w-12 rounded-full border-2 border-white shadow-lg" />
        <h2 class="text-lg font-bold text-white ml-3" v-if="!collapsed">CPMS - FIVD</h2>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-6 space-y-6 overflow-y-auto">
        <!-- platforms -->
        <div>
          <p v-if="!collapsed" class="mb-2 text-xs font-semibold text-yellow-200 uppercase tracking-wider">
            Platforms
          </p>
          <ul class="space-y-2">
            <li v-for="item in platforms" :key="item.label">
              <Link
                :href="route(item.route)"
                :class="[
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-yellow-100 transition hover:bg-yellow-900 hover:text-white',
                  $page.component === item.component ? 'bg-yellow-900 text-white shadow font-bold ring-2 ring-yellow-400' : ''
                ]"
              >
                <span class="material-icons">{{ item.icon }}</span>
                <span v-if="!collapsed" class="font-medium">{{ item.label }}</span>
              </Link>
            </li>
          </ul>
        </div>

        <hr class="border-yellow-700" />

        <!-- Reports -->
        <div>
          <p v-if="!collapsed" class="mb-2 text-xs font-semibold text-yellow-200 uppercase tracking-wider">
            Reports
          </p>
          <ul class="space-y-2">
            <li v-for="item in reports" :key="item.label">
              <Link
                :href="route(item.route)"
                :class="[
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-yellow-100 transition hover:bg-yellow-900 hover:text-white',
                  $page.component === item.component ? 'bg-yellow-900 text-white shadow font-bold ring-2 ring-yellow-400' : ''
                ]"
              >
                <span class="material-icons">{{ item.icon }}</span>
                <span v-if="!collapsed" class="font-medium">{{ item.label }}</span>
              </Link>
            </li>
          </ul>
        </div>

        <hr class="border-yellow-700" />

        <!-- Tables -->
        <div>
          <p v-if="!collapsed" class="mb-2 text-xs font-semibold text-yellow-200 uppercase tracking-wider">
            Tables
          </p>
          <ul class="space-y-2">
            <li v-for="item in tables" :key="item.label">
              <Link
                :href="route(item.route)"
                :class="[
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-yellow-100 transition hover:bg-yellow-900 hover:text-white',
                  $page.component === item.component ? 'bg-yellow-900 text-white shadow font-bold ring-2 ring-yellow-400' : ''
                ]"
              >
                <span class="material-icons">{{ item.icon }}</span>
                <span v-if="!collapsed" class="font-medium">{{ item.label }}</span>
              </Link>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Footer (Logout) -->
      <div class="p-4 border-t border-yellow-700">
        <button
          @click="logout"
          class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-yellow-100 hover:bg-yellow-900 hover:text-white"
        >
          <span class="material-icons">logout</span>
          <span v-if="!collapsed" class="font-medium">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main 
      class="flex-1 p-4 md:p-8 md:ml-64 transition-all duration-300"
      :class="{ 'md:ml-20': collapsed }"
    >
      <div class="space-y-4 md:space-y-8">
        <slot />
        <ToastContainer />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'

const page = usePage()
const user = ref(page.props.user || { name: 'Guest', role: 'guest' })

const sidebarOpen = ref(false)
const collapsed = ref(false)

// Management links
const platforms = [
  { icon: 'dashboard', label: 'Dashboard', route: 'admin.dashboard', component: 'admin/AdminDashboard' },
  { icon: 'home', label: 'Homepage', route: 'home', component: 'Homepage' },
  { icon: 'engineering', label: 'Projects', route: 'admin.projects', component: 'projects/ProjectsTable' },
  // { icon: 'group', label: 'Users', route: 'admin.users', component: 'Admin/Users/Index' },
  // { icon: 'settings', label: 'Settings', route: 'admin.settings', component: 'Admin/Settings/Index' },
]

// Reports links
const reports = [
  // { icon: 'bar_chart', label: 'Analytics', route: 'admin.analytics', component: 'Admin/Analytics/Index' },
  // { icon: 'description', label: 'Logs', route: 'admin.logs', component: 'Admin/Logs/Index' },
]

// Tables links
const tables = [
  { icon: 'table_chart', label: 'Users Table', route: 'admin.users', component: 'users/UsersTable' },
  { icon: 'table_chart', label: 'Project Managers', route: 'admin.project-managers', component: 'project_managers/ProjectManagersTable' },
  { icon: 'table_chart', label: 'Development Teams', route: 'admin.development-teams', component: 'development_teams/DevelopmentTeamsTable' },
  { icon: 'table_chart', label: 'Development Tools', route: 'admin.development-tools', component: 'development_tools/DevelopmentToolsTable' },
  { icon: 'table_chart', label: 'Testing Teams', route: 'admin.testing-teams', component: 'testing_teams/TestingTeamsTable' },
  { icon: 'table_chart', label: 'Testing Tools', route: 'admin.testing-tools', component: 'testing_tools/TestingToolsTable' },
]

const logout = () => {
  router.post(route('logout'))
}
</script>
