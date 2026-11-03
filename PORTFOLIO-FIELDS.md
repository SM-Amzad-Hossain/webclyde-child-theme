# Portfolio Case-Study Fields

Adds a complete, structured case-study system to the **Portfolio** custom post
type — no extra plugins required. Everything is native WordPress (meta boxes,
the media library, and the REST API).

## What you get

When you edit a Portfolio (`Portfolios → Add New / Edit`), six field panels
appear below the content editor:

| Panel | Fields |
| --- | --- |
| **Project Details** | Logo, Site URL, Description, Organization, Services, Timeline, Industry, Platform, Location |
| **Image Carousel** | An ordered, multi-image gallery (drag thumbnails to reorder) |
| **The Challenge** | Challenge title, Challenge description, **The main problems** (add as many as you like) |
| **The Approach** | Approach image, Approach title, **Step-by-step approach** (add as many as you like) |
| **The Results** | Result title, Result description, Mobile load time, Google PageSpeed score (mobile), Completed checkouts after launch |
| **Client Review** | Review image, What the client said, Client avatar, Client name, Client designation |

### Working with the fields

- **Images** (logo, approach, review, avatar): click **Select image** to open the
  WordPress media library, or **Remove** to clear.
- **Image carousel**: click **Add / edit images**, pick multiple images, then drag
  the thumbnails to reorder. Click the **×** on a thumbnail to remove it.
- **Repeatable lists** (main problems / approach steps): click **+ Add problem** /
  **+ Add step** to add a row, **×** to remove one, and drag the **⋮⋮** handle to
  reorder. Press **Enter** in a row to add the next one.
- **Industry / Platform / Timeline**: pick a value from the dropdown — no typing
  needed. If nothing fits, choose **Other (type your own)…** and a text box
  appears for a custom value. Existing custom values reopen in that box, so
  nothing is lost.
- **Services**: tick the checkboxes for the services you provided — each renders
  as a badge. Anything not in the list can go in the **Other services** box
  (comma-separated). Checked presets and custom entries are merged, de-duplicated
  and stored together.
- **PageSpeed score**: a number from 0–100.

Only the fields you fill in are shown on the front end — empty sections are
skipped automatically, so a partial case study still looks clean.

## Front-end display

The case study is **appended automatically** to every single Portfolio page
(`single-portfolio.php`), rendered in this order:

1. Header — logo, title, description, “Visit live site” button
2. Project info grid — organization, services, timeline, industry, platform, location
3. Image carousel — swipeable, with arrows, dots and a counter
4. The Challenge — title, description, problems list
5. The Approach — image + numbered steps
6. The Results — title, description, and stat cards (load time / PageSpeed / checkouts)
7. Client Review — review image, quote, avatar, name, designation

### Shortcode

Place the case study anywhere (pages, posts, Elementor “Shortcode” widget,
Text/HTML blocks). `id` defaults to the current post.

```
[portfolio_case_study]
[portfolio_case_study id="123"]
[portfolio_case_study id="123" sections="results,review"]
```

Available `sections` (comma-separated, in any order):
`header`, `carousel`, `challenge`, `approach`, `results`, `review`.

### Elementor widget

A **“Portfolio Case Study”** widget is registered (in the *Basic* category). Drop
it into a Single Portfolio template (or any template) and toggle which sections to
show. Leave **Portfolio ID** empty to use the current post.

> The renderer detects manual placement (shortcode / widget) and will **not**
> also auto-append the case study, so you never get it twice.

### Template tag (developers)

```php
<?php echo wpc_render_case_study( get_the_ID() ); ?>
<?php echo wpc_render_case_study( 123, [ 'sections' => [ 'results', 'review' ] ] ); ?>
```

## Customizing

### Change the dropdown choices

The preset lists for **Industry**, **Platform**, **Services** and **Timeline**
are filterable, so you can add, remove or reorder options without editing the
theme. Add this to your own plugin or the child theme's `functions.php`:

```php
add_filter( 'wpc_industry_options', function ( $options ) {
    $options[] = 'Aerospace';      // add one
    return $options;
} );

add_filter( 'wpc_services_options', function ( $options ) {
    return [ 'Web Design', 'WordPress Development', 'SEO' ];  // replace the list
} );
```

Available filters: `wpc_industry_options`, `wpc_platform_options`,
`wpc_services_options`, `wpc_timeline_options`. Each receives the current list of
strings and must return an array of strings. The **Other / custom** box is always
available regardless of the presets, so editors can still enter anything.

### Turn off automatic display

```php
add_filter( 'wpc_auto_append_case_study', '__return_false' );
```

### Re-brand the look

All styles are scoped to `.wcp-case-study` and driven by CSS variables. Override
them from the Customizer, Elementor custom CSS, or your own stylesheet:

```css
.wcp-case-study {
  --wcp-accent: #e11d48;      /* buttons, links, highlights */
  --wcp-accent-soft: #ffe4e6; /* chips, step numbers */
  --wcp-dark: #111827;        /* results band */
  --wcp-radius: 18px;
  --wcp-max: 1120px;
}
```

## Meta keys (REST / headless)

Every field is exposed in the REST API under `meta`, prefixed with `_wpc_`:

```
_wpc_logo, _wpc_site_url, _wpc_description, _wpc_organization, _wpc_services,
_wpc_timeline, _wpc_industry, _wpc_platform, _wpc_location, _wpc_gallery,
_wpc_challenge_title, _wpc_challenge_description, _wpc_problems,
_wpc_approach_image, _wpc_approach_title, _wpc_approach_steps,
_wpc_results_title, _wpc_results_description, _wpc_mobile_load_time,
_wpc_pagespeed_mobile, _wpc_checkouts,
_wpc_review_image, _wpc_review_quote, _wpc_client_avatar, _wpc_client_name,
_wpc_client_designation
```

Image fields store attachment IDs; `_wpc_gallery` is an ordered array of IDs;
`_wpc_problems` and `_wpc_approach_steps` are arrays of strings.

The dropdown fields store plain strings, so they are unchanged for REST /
headless consumers: `_wpc_industry`, `_wpc_platform` and `_wpc_timeline` hold the
chosen (or custom) value, and `_wpc_services` holds a **comma-separated** string
(e.g. `"UX/UI Design, WooCommerce Development"`). When writing via REST you may
send `_wpc_services` as either a comma-separated string or an array of strings —
both are normalized to the same comma-separated format.

## Files

```
inc/portfolio-meta.php          Field registry, meta boxes, save + sanitize, REST
inc/portfolio-frontend.php      Renderer, shortcode, Elementor widget, auto-append
assets/css/portfolio-admin.css  Admin meta-box styles
assets/js/portfolio-admin.js    Media pickers, gallery + repeater + dropdown controls
assets/css/portfolio.css        Front-end case-study styles
assets/js/portfolio.js          Carousel behaviour
single-portfolio.php            Single portfolio template
```
