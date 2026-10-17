import { createRouter, createWebHistory } from 'vue-router'
import LoginView from './views/LoginView.vue'
import EquipmentView from './views/EquipmentView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView },
    { path: '/', name: 'equipment', component: EquipmentView, meta: { requiresAuth: true } },
  ],
})

router.beforeEach((to) => {
  const loggedIn = !!localStorage.getItem('token')
  if (to.meta.requiresAuth && !loggedIn) return { name: 'login' }
  if (to.name === 'login' && loggedIn) return { name: 'equipment' }
})

export default router
