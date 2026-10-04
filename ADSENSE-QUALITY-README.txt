Uptime Fixer 3.9.1 — Search Visibility Recovery Update
=================================================================

WHAT THIS UPDATE CHANGES

* Keeps all 238 registered theme tools visible and usable in public directories.
  With UptimeFixer Overall Tools Suite 2.3.1 active, the complete public registry
  contains 346 distinct working tools. Published registered tools use normal
  WordPress search visibility and XML sitemap inclusion by default.
* Applies noindex,follow only when an editor explicitly holds a specific tool or
  article back. Ordinary published tools and blog posts keep WordPress's normal
  archive, feed, search and sitemap visibility.
* Suppresses conventionally enqueued AdSense scripts on quality-gated pages so
  unfinished tools are not treated as monetized inventory by the theme.
* Replaces the old CSP host list that blocked AdSense with an HTTPS-compatible,
  filterable policy. Advanced sites can supply a nonce policy via the documented
  alltools_content_security_policy filter.
* Keeps Search visibility and Editorial review controls in the WordPress editor
  for specific pages that need to be held back during correction.
* Redirects duplicate tool posts and the duplicate CPT archive.
* Creates missing Privacy Policy and Terms pages without replacing an existing
  site's legal text, URL, publication status or manual edits.
* Adds the supported AdSense account verification meta tag and keeps the safe
  domain-root ads.txt installer for publisher pub-9901210156464210.
* Removes repeated generated copy, fake uptime history, invented Core Web Vitals,
  and other unmeasured performance claims.
* Hides only genuinely empty categories and an empty blog from navigation.

INSTALLATION

1. Back up the website files and database.
2. Upload this ZIP in Appearance > Themes > Add New > Upload Theme and replace
   the existing Uptime Fixer theme when WordPress asks.
3. Open the WordPress Dashboard once. This creates missing support/legal pages
   without editing existing ones and flushes rewrite rules.
4. Upload and activate UptimeFixer Overall Tools Suite 2.3.1, then open the
   Dashboard once more so its 108 missing tool pages are created safely.
5. Clear WordPress, server, CDN, and browser caches.
6. Confirm that /privacy-policy/, /terms/, /about/, /contact/, /robots.txt,
   /ads.txt, and the active XML sitemap return HTTP 200.

EDITORIAL WORKFLOW

* Tool editor: published registered tools use normal indexing. Select Draft
  quality only when a specific interface or its guidance needs correction.
* Post editor: normal WordPress visibility is the default. Select the explicit
  hold-back option only when an article must be removed from public listings and
  sitemaps during an editorial correction.
* Merge or redirect near-duplicates and add screenshots, examples, limitations,
  authorship, and sources where relevant.

BEFORE REQUESTING AN ADSENSE REVIEW

* Inspect the site while logged out on desktop and mobile.
* Check the XML sitemap and verify that only intentional URLs are listed.
* Test representative tools from every category with normal, empty, invalid and
  edge-case inputs. Hold back any specific page that fails its checks.
* Fix broken links, placeholder interfaces, inaccurate claims, and console errors.
* Use Google Search Console URL Inspection on the homepage and several reviewed
  tool pages, then allow time for Google to recrawl the cleaner site.
* Request an AdSense review only after the changes are live and recrawled.

No theme or content change can guarantee AdSense approval. Google makes the
final decision after reviewing the live site, policy compliance, usefulness,
navigation, trust signals, and overall content quality.
