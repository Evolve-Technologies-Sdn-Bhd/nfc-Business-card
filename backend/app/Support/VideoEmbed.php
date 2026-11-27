<?php

namespace App\Support;

/**
 * YouTube / Vimeo links → privacy-friendly embed URLs.
 *
 * Used both to validate what the Profile Builder saves (intro_video,
 * company_video) and to build the player URL the public profile shows.
 * Only these two providers are accepted: a profile never embeds an arbitrary
 * third-party page. YouTube uses youtube-nocookie.com and Vimeo uses dnt=1,
 * so no tracking cookies are set before the visitor presses play.
 *
 * Keep in step with frontend/utils/videoEmbed.js.
 */
class VideoEmbed
{
    /** @return array{provider: string, id: string, embed_url: string}|null */
    public static function parse(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '' || strlen($url) > 500) {
            return null;
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        $host = strtolower(preg_replace('/^(www\.|m\.)/i', '', $parts['host'] ?? ''));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        // YouTube: watch?v=, youtu.be/, /shorts/, /embed/, /live/
        $youtubeId = null;
        if ($host === 'youtu.be') {
            $youtubeId = trim($path, '/');
        } elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com', 'music.youtube.com'], true)) {
            if (!empty($query['v'])) {
                $youtubeId = $query['v'];
            } elseif (preg_match('#^/(?:shorts|embed|live|v)/([^/?]+)#', $path, $m)) {
                $youtubeId = $m[1];
            }
        }
        if ($youtubeId !== null) {
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId)
                ? ['provider' => 'youtube', 'id' => $youtubeId, 'embed_url' => "https://www.youtube-nocookie.com/embed/{$youtubeId}"]
                : null;
        }

        // Vimeo: vimeo.com/123, vimeo.com/channels/x/123, player.vimeo.com/video/123
        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)
            && preg_match('#/(\d{6,12})(?:/|$)#', $path, $m)) {
            return ['provider' => 'vimeo', 'id' => $m[1], 'embed_url' => "https://player.vimeo.com/video/{$m[1]}?dnt=1"];
        }

        return null;
    }

    public static function embedUrl(?string $url): ?string
    {
        return self::parse($url)['embed_url'] ?? null;
    }

    /** Validation rule: empty, or a link parse() understands. */
    public static function rule(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if ($value !== null && $value !== '' && self::parse((string) $value) === null) {
                $fail('Use a YouTube or Vimeo link (for example https://youtu.be/… or https://vimeo.com/…).');
            }
        };
    }
}
