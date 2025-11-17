// middleware/trailing-slash.global.ts
// This middleware ensures consistent handling of trailing slashes
// Both /path and /path/ will work and redirect to the canonical version

export default defineNuxtRouteMiddleware((to) => {
  // Skip if it's a file (has extension)
  if (to.path.match(/\.[^/]+$/)) {
    return;
  }

  // Skip root path
  if (to.path === '/') {
    return;
  }

  // If path has trailing slash (except root), remove it with redirect
  if (to.path.endsWith('/')) {
    const pathWithoutSlash = to.path.slice(0, -1);
    const query = to.query;
    const hash = to.hash;

    // Redirect to version without trailing slash
    return navigateTo({
      path: pathWithoutSlash,
      query,
      hash,
    }, {
      redirectCode: 301, // Permanent redirect for SEO
    });
  }
});
