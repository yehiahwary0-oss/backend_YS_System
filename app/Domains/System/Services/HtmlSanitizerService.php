<?php

namespace App\Domains\System\Services;

use Mews\Purifier\Facades\Purifier;

/**
 * HtmlSanitizerService — the single boundary where admin-authored rich-text
 * HTML (product long_desc, documentation article content, etc.) gets
 * cleaned before it ever touches the database.
 *
 * WHY THIS EXISTS:
 * Every one of these fields is later rendered on the public frontend via
 * `dangerouslySetInnerHTML` with zero sanitization on that side (by
 * design — the frontend trusts the API). That means THIS is the only
 * place in the entire system where untrusted/attacker-controlled markup
 * (a compromised or malicious admin account, a copy-pasted snippet with a
 * hidden payload, etc.) can be neutralized before it reaches every visitor
 * of a product or docs page. Sanitizing on write (here) instead of on read
 * (in the frontend) means every current AND future consumer of this data
 * — the public site, a future mobile app, a future partner API — inherits
 * the protection automatically, with nothing to remember to do on their end.
 *
 * Uses a config profile ('cms') defined in config/purifier.php with an
 * allow-list appropriate for a rich-text editor: basic formatting, lists,
 * headings, links, and images — no <script>, no inline event handlers, no
 * <iframe>, no <style>. See that config file for the exact allow-list.
 */
class HtmlSanitizerService
{
    public function sanitize(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        return Purifier::clean($html, 'cms');
    }
}
