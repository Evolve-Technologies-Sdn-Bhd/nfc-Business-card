// server/middleware/trailing-slash.ts
// Server-side handler for trailing slashes in static mode
// Ensures both /path and /path/ resolve correctly

export default defineEventHandler((event) => {
  const url = event.node.req.url;

  // Skip API routes, assets, and files with extensions
  if (!url || url.startsWith('/api') || url.startsWith('/_nuxt') || url.match(/\.[^/]+$/)) {
    return;
  }

  // Skip root path
  if (url === '/') {
    return;
  }

  // Extract path and query string
  const [path, queryString] = url.split('?');

  // If path has trailing slash (and it's not root), redirect without it
  if (path.endsWith('/')) {
    const pathWithoutSlash = path.slice(0, -1);
    const newUrl = queryString ? `${pathWithoutSlash}?${queryString}` : pathWithoutSlash;

    // Send permanent redirect
    event.node.res.writeHead(301, {
      Location: newUrl,
    });
    event.node.res.end();
    return;
  }
});
