# Uptime Fixer security notes

Version 4.4.0 validates and rate-limits public diagnostics, rejects private and reserved network targets, limits permitted ports and response sizes, verifies TLS certificates, disables XML-RPC and the dashboard file editor, prevents public database writes, and sends restrictive browser security headers. PageSpeed reports are compacted before caching, sitemap DOCTYPE declarations are rejected, and browser dependency loads have bounded timeouts and retry-safe failure handling.

OpenAI, DataForSEO and PageSpeed credentials are never sent to visitors. Values entered in Theme Settings are encrypted with keys derived from the site's WordPress authentication salt; wp-config.php constants can override stored values. Commercial endpoints have separate per-IP hourly limits and global daily limits to reduce abuse and unexpected provider charges.

No WordPress theme can guarantee that a complete site will never be attacked. Site security also depends on WordPress core, plugins, hosting, credentials and server configuration. Keep WordPress, Yoast SEO and all plugins updated; remove unused plugins/themes; use unique administrator passwords and 2FA; keep tested off-site backups; and enable a host/CDN firewall and login-rate limiting.

File conversions run in the visitor's browser where possible. Public website diagnostics may contact WordPress mShots, Google PageSpeed/Public DNS, Cloudflare DNS, crt.sh, RDAP, the W3C validator, ipwho.is or Frankfurter only when the visitor requests the corresponding tool. AI/API-labelled tools may send the submitted content to the site owner's configured provider. Review and publish an accurate privacy/cookie notice for the services actually enabled on the live site.

Pinned browser libraries are loaded only on tool pages from jsDelivr. The bundled Content-Security-Policy permits HTTPS subresources because Google states that AdSense resource hosts can change over time. A site using a nonce-based CSP can replace the policy through the `alltools_content_security_policy` filter. If the site owner requires a zero-third-party-code policy, download, review and self-host the browser libraries, disable third-party advertising/analytics, and then narrow that filter.

Report a security issue privately to the site administrator. Do not include passwords, personal files or exploit payloads in a public report.
