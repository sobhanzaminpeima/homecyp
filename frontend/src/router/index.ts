import { createRouter, createWebHistory } from 'vue-router'
import { useCityStore } from '@/stores/city'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'splash', component: () => import('@/views/SplashView.vue') },
    { path: '/city', name: 'city-select', component: () => import('@/views/CitySelectView.vue') },
    {
      path: '/home',
      name: 'home',
      component: () => import('@/views/HomeView.vue'),
      meta: { needsCity: true },
    },
    {
      path: '/explore',
      name: 'explore',
      component: () => import('@/views/ExploreView.vue'),
      meta: { needsCity: true },
    },
    { path: '/discover', name: 'discover', component: () => import('@/views/DiscoverView.vue'), meta: { needsCity: true } },
    { path: '/ai', name: 'ai-chat', component: () => import('@/views/AiChatView.vue') },
    {
      path: '/business/:slug',
      name: 'business-detail',
      component: () => import('@/views/BusinessDetailView.vue'),
      meta: { needsCity: true },
    },
    {
      path: '/project/:id',
      name: 'project-detail',
      component: () => import('@/views/ProjectDetailView.vue'),
      meta: { needsCity: true },
    },
    { path: '/login', name: 'login', component: () => import('@/views/auth/LoginView.vue') },
    { path: '/register', name: 'register', component: () => import('@/views/auth/RegisterView.vue') },
    { path: '/forgot-password', name: 'forgot-password', component: () => import('@/views/auth/ForgotPasswordView.vue') },
    { path: '/reset-password', name: 'reset-password', component: () => import('@/views/auth/ResetPasswordView.vue') },
    {
      path: '/favorites',
      name: 'favorites',
      component: () => import('@/views/FavoritesView.vue'),
      meta: { needsCity: true, requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/ProfileView.vue'),
      meta: { needsCity: true, requiresAuth: true },
    },
    { path: '/notifications', name: 'notifications', component: () => import('@/views/NotificationsView.vue'), meta: { requiresAuth: true } },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/dashboard/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    { path: '/admin/login', name: 'admin-login', component: () => import('@/views/admin/AdminLoginView.vue') },
    {
      path: '/admin',
      component: () => import('@/views/admin/AdminLayout.vue'),
      meta: { requiresAdmin: true },
      children: [
        { path: '', redirect: '/admin/dashboard' },
        { path: 'dashboard', name: 'admin-dashboard', component: () => import('@/views/admin/AdminDashboardView.vue') },
        { path: 'businesses', name: 'admin-businesses', component: () => import('@/views/admin/AdminBusinessesView.vue') },
        { path: 'reviews', name: 'admin-reviews', component: () => import('@/views/admin/AdminReviewsView.vue') },
        { path: 'projects', name: 'admin-projects', component: () => import('@/views/admin/AdminProjectsView.vue') },
        { path: 'categories', name: 'admin-categories', component: () => import('@/views/admin/AdminCategoriesView.vue') },
        { path: 'cities', name: 'admin-cities', component: () => import('@/views/admin/AdminCitiesView.vue') },
        { path: 'advertisements', name: 'admin-ads', component: () => import('@/views/admin/AdminAdsView.vue') },
        { path: 'packages', name: 'admin-packages', component: () => import('@/views/admin/AdminPackagesView.vue') },
        { path: 'subscription-requests', name: 'admin-subscription-requests', component: () => import('@/views/admin/AdminSubscriptionRequestsView.vue') },
        { path: 'users', name: 'admin-users', component: () => import('@/views/admin/AdminUsersView.vue') },
        { path: 'settings', name: 'admin-settings', component: () => import('@/views/admin/AdminSettingsView.vue') },
        { path: 'growth', name: 'admin-growth', component: () => import('@/views/admin/AdminGrowthView.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const cityStore = useCityStore()
  const authStore = useAuthStore()

  if (to.meta.requiresAdmin) {
    if (!authStore.isAuthenticated || !authStore.isAdmin) {
      return { name: 'admin-login' }
    }
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.needsCity && !cityStore.hasCity) {
    return { name: 'city-select' }
  }

  return true
})

export default router
