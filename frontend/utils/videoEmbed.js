// YouTube / Vimeo links → privacy-friendly embed URLs.
// Mirrors backend App\Support\VideoEmbed (keep the two in step). Used by the
// builder's video input (validation + preview) and by the profile page's
// Live Preview, which never round-trips through the API.

const YT_ID = /^[A-Za-z0-9_-]{11}$/;

export function parseVideoUrl(input) {
  let url = String(input ?? "").trim();
  if (!url || url.length > 500) return null;
  if (!/^https?:\/\//i.test(url)) url = `https://${url}`;

  let parsed;
  try {
    parsed = new URL(url);
  } catch {
    return null;
  }
  const host = parsed.hostname.toLowerCase().replace(/^(www\.|m\.)/, "");
  const path = parsed.pathname;

  let youtubeId = null;
  if (host === "youtu.be") {
    youtubeId = path.replace(/^\/+|\/+$/g, "");
  } else if (["youtube.com", "youtube-nocookie.com", "music.youtube.com"].includes(host)) {
    youtubeId = parsed.searchParams.get("v") || path.match(/^\/(?:shorts|embed|live|v)\/([^/?]+)/)?.[1] || null;
  }
  if (youtubeId !== null) {
    return YT_ID.test(youtubeId)
      ? { provider: "youtube", id: youtubeId, embedUrl: `https://www.youtube-nocookie.com/embed/${youtubeId}` }
      : null;
  }

  if (["vimeo.com", "player.vimeo.com"].includes(host)) {
    const id = path.match(/\/(\d{6,12})(?:\/|$)/)?.[1];
    if (id) return { provider: "vimeo", id, embedUrl: `https://player.vimeo.com/video/${id}?dnt=1` };
  }

  return null;
}

export const videoEmbedUrl = (url) => parseVideoUrl(url)?.embedUrl || null;

/** True for an empty value or a supported link. */
export const isValidVideoUrl = (url) => !String(url ?? "").trim() || parseVideoUrl(url) !== null;
