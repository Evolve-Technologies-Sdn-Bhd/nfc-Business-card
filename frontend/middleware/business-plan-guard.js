// middleware/businessPlanGuard.js
// Blocks login/register access for users who have submitted a Business Plan request
// These users must wait for admin to create their account

export default defineNuxtRouteMiddleware((to, from) => {
  // Only run on client-side
  if (process.server) return

  // Check if user has submitted a business plan request
  const businessPlanRequested = localStorage.getItem('businessPlanRequested')
  
  if (businessPlanRequested === 'true') {
    // Block access to login and register pages
    const blockedRoutes = ['/UserAccount/login', '/UserAccount/register']
    
    if (blockedRoutes.some(route => to.path.toLowerCase().includes(route.toLowerCase()))) {
      // Redirect to homepage with a flag to show the alert
      return navigateTo('/Homepage?businessPlanPending=true')
    }
  }
})
