<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BotSeoMiddleware
{
    /**
     * Known social media bot user-agents.
     */
    protected array $bots = [
        'facebookexternalhit',
        'Facebot',
        'Twitterbot',
        'LinkedInBot',
        'WhatsApp',
        'Slackbot',
        'TelegramBot',
        'Discordbot',
        'ia_archiver',
        'Googlebot',
        'Bingbot',
        'Applebot',
        'Slurp',
        'MetaInspector',
        'curl',
        'wget',
        'python-requests',
        'SemrushBot',
    ];

    public function handle(Request $request, Closure $next)
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        $isBot = false;
        foreach ($this->bots as $bot) {
            if (str_contains($userAgent, strtolower($bot))) {
                $isBot = true;
                break;
            }
        }

        if (!$isBot) {
            return $next($request);
        }

        // Serve a clean, instant OG page to the bot — no sessions, no auth, no JS
        $appUrl   = config('app.url', 'https://spatialsync.isufstcict.com');
        $imageUrl = rtrim($appUrl, '/') . '/images/og-meta.png';
        $pageUrl  = $request->url();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <title>SpatialSync — Collaborative 3D Architecture</title>
    <meta name="description" content="Design, iterate, and collaborate on premium 3D blueprints in real-time. Built for the next generation of architects.">

    <meta property="og:type"              content="website">
    <meta property="og:url"               content="{$pageUrl}">
    <meta property="og:site_name"         content="SpatialSync">
    <meta property="og:title"             content="SpatialSync — Collaborative 3D Architecture">
    <meta property="og:description"       content="Design, iterate, and collaborate on premium 3D blueprints in real-time. Built for the next generation of architects.">
    <meta property="og:image"             content="{$imageUrl}">
    <meta property="og:image:secure_url"  content="{$imageUrl}">
    <meta property="og:image:type"        content="image/png">
    <meta property="og:image:alt"         content="SpatialSync — Collaborative 3D architecture and design platform">
    <meta property="og:image:width"       content="1200">
    <meta property="og:image:height"      content="630">
    <meta property="og:locale"            content="en_US">

    <meta name="twitter:card"             content="summary_large_image">
    <meta name="twitter:url"              content="{$pageUrl}">
    <meta name="twitter:title"            content="SpatialSync — Collaborative 3D Architecture">
    <meta name="twitter:description"      content="Design, iterate, and collaborate on premium 3D blueprints in real-time. Built for the next generation of architects.">
    <meta name="twitter:image"            content="{$imageUrl}">
</head>
<body>
    <h1>SpatialSync — Collaborative 3D Architecture</h1>
    <p>Design, iterate, and collaborate on premium 3D blueprints in real-time.</p>
</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
