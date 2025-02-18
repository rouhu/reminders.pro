import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import ResetPassword from '../views/ResetPassword.vue'
import ReminderList from '../views/ReminderList.vue'
import ReminderForm from '../views/ReminderForm.vue'
import ReminderDetail from '../views/ReminderDetail.vue'
import { supabase } from '../lib/supabase'

const routes = [
  {
    path: '/',
    component: Login
  },
  {
    path: '/register',
    component: Register
  },
  {
    path: '/reset-password',
    component: ResetPassword
  },
  {
    path: '/reminders',
    component: ReminderList,
    meta: { requiresAuth: true }
  },
  {
    path: '/reminders/new',
    component: ReminderForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/reminders/:id',
    component: ReminderDetail,
    meta: { requiresAuth: true }
  },
  {
    path: '/reminders/edit/:id',
    component: ReminderForm,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, _, next) => {
  const { data: { session } } = await supabase.auth.getSession()
  
  if (to.meta.requiresAuth && !session) {
    next('/')
  } else if (session && to.path === '/') {
    next('/reminders')
  } else {
    next()
  }
})

export default router
