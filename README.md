# Webclyde Child Theme — Portfolio Case Study System & 3D Frame Mockup

> **A high-performance WordPress Child Theme for Hello Elementor**, purpose-built for digital agencies and web design portfolios. It provides a native **Portfolio Case Study Management Engine** (CPT, custom admin workflow, Elementor Dynamic Tags, and bespoke widgets) paired with an advanced **3D Perspective Device Mockup ("Frame") Widget**.

---

## 📑 Table of Contents

1. [Architecture & System Overview](#-architecture--system-overview)
2. [Directory & File Structure](#-directory--file-structure)
3. [Installation & Requirements](#-installation--requirements)
4. [Portfolio Case Study System (CPT & Admin)](#-portfolio-case-study-system-cpt--admin)
   - [Custom Post Type & Taxonomies](#custom-post-type--taxonomies)
   - [Unified Under-Title Meta Panel](#unified-under-title-meta-panel)
   - [The 6 Data Tabs & Field Breakdown](#the-6-data-tabs--field-breakdown)
   - [Database Schema & Meta Keys](#database-schema--meta-keys)
5. [Elementor Dynamic Tags Integration](#-elementor-dynamic-tags-integration)
   - [Registered Dynamic Tags](#registered-dynamic-tags)
   - [Live Editor Fallback Mode](#live-editor-fallback-mode)
6. [Elementor Custom Widgets](#-elementor-custom-widgets)
   - [1. Portfolio: The Main Problems Widget](#1-portfolio-the-main-problems-widget)
   - [2. Portfolio: Approach & Steps Widget](#2-portfolio-approach--steps-widget)
   - [Additional Render Engine Widgets & Shortcodes](#additional-render-engine-widgets--shortcodes)
7. [The "Frame" 3D Mockup System (Frame Everything)](#-the-frame-3d-mockup-system-frame-everything)
   - [Concept & Pure CSS 3D Perspective](#concept--pure-css-3d-perspective)
   - [Mathematical Perspective & Container Queries](#mathematical-perspective--container-queries)
   - [Frame Image Resolution Hierarchy](#frame-image-resolution-hierarchy)
   - [Elementor Widget Controls & Features](#elementor-widget-controls--features)
   - [Responsive Behavior & Mobile Safety](#responsive-behavior--mobile-safety)
8. [Frontend Rendering & Assets Pipeline](#-frontend-rendering--assets-pipeline)
9. [Step-by-Step Workflow Guide](#-step-by-step-workflow-guide)
   - [1. Creating a Case Study](#1-creating-a-case-study)
   - [2. Building an Elementor Single Portfolio Template](#2-building-an-elementor-single-portfolio-template)
   - [3. Framing Screenshots with Dynamic Tags](#3-framing-screenshots-with-dynamic-tags)
10. [Security, Performance & Best Practices](#-security-performance--best-practices)

---

## 🔭 Architecture & System Overview

The theme extends **Hello Elementor** (`hello-elementor`) and cleanly decouples presentation from data storage. It is structured into two core subsystems:

```
                      ┌──────────────────────────────────────────────┐
                      │             Webclyde Child Theme             │
                      │               (functions.php)                │
                      └──────────────────────┬───────────────────────┘
                                             │
             ┌───────────────────────────────┴───────────────────────────────┐
             ▼                                                               ▼
┌──────────────────────────────┐                              ┌──────────────────────────────┐
│    Portfolio Case Study      │                              │      Frame 3D Mockup         │
│         Subsystem            │                              │          Widget              │
├──────────────────────────────┤                              ├──────────────────────────────┤
│ • CPT 'portfolio'            │                              │ • Elementor 'tfw_frame'      │
│ • Taxonomies: Category, Tag  │                              │ • CSS Container Query 3D     │
│ • 6-Tab Under-Title Admin UI │                              │ • Dynamic ResizeObserver     │
│ • Elementor Dynamic Tags     │                              │ • Auto-loads frame.webp      │
│ • Bespoke Elementor Widgets  │                              │ • Dynamic Tag screen inputs  │
│ • Vanilla JS Slider & CSS    │                              │ • Zero-overlap mobile layout │
└──────────────────────────────┘                              └──────────────────────────────┘
```

1. **Portfolio Case Study Engine (`/inc/`)**: Replaces bloated third-party custom field plugins (ACF/Pods/Metabox) with a native, ultra-fast post meta management panel. It exposes all project metrics, challenge points, step-by-step approach cards, and client testimonials directly to **Elementor Dynamic Tags** and dedicated widgets.
2. **Frame 3D Perspective Mockup Engine (`/frame/`)**: An Elementor widget that takes any screenshot (static or dynamically bound to the post's featured image) and wraps it inside an isometric, hyper-realistic tablet/device bezel with authentic perspective, tilt, and drop shadow.

---

## 📁 Directory & File Structure

```
webclyde/
├── style.css                      # Child theme declaration (Template: hello-elementor)
├── functions.php                  # Bootloader enqueuing styles & requiring sub-modules
├── single-portfolio.php           # Native out-of-the-box single portfolio case study template
│
├── inc/                           # Portfolio Case Study Module
│   ├── portfolio-cpt.php          # CPT & taxonomies registration, Elementor CPT support
│   ├── portfolio-metaboxes.php    # Under-title admin panel, tabs, repeaters & meta saving
│   ├── portfolio-dynamic-tags.php # Elementor Dynamic Tags (Text, Image, URL, Gallery)
│   ├── portfolio-widgets.php      # Elementor custom widgets (Problems, Approach, etc.)
│   └── portfolio-render.php       # Shared HTML render functions, post-ID resolver & frontend assets
│
├── assets/                        # Portfolio Scripts & Stylesheets
│   ├── portfolio-admin.css        # Tabbed admin styling, repeaters, image upload slots
│   ├── portfolio-admin.js         # WP Media frame integration, Gutenberg sync, tab persistence
│   ├── portfolio-frontend.css     # Frontend styling for problems, approach grid, metrics & reviews
│   └── portfolio-frontend.js      # Touch-friendly autoplay slider & infinite marquee ticker
│
└── frame/                         # Frame 3D Device Mockup Module
    ├── frame.php                  # TFW_Frame_Widget Elementor registration & perspective sync
    └── assets/
        ├── frame.css              # Container-query 3D perspective transforms & mobile fallbacks
        ├── frame.webp             # High-resolution transparent isometric device frame
        └── tablet-frame.webp      # Fallback tablet frame mockup
```

---

## ⚙️ Installation & Requirements

### Minimum Requirements
- **WordPress**: 6.0 or higher
- **PHP**: 7.4 or higher (PHP 8.0+ recommended)
- **Parent Theme**: [Hello Elementor](https://wordpress.org/themes/hello-elementor/)
- **Elementor**: Free or Elementor Pro (Required for Dynamic Tags & Theme Builder single templates)

### Setup
1. Clone or upload this folder to your WordPress installation:
   ```bash
   wp-content/themes/webclyde/
   ```
2. Activate **Hello Elementor** (Parent Theme) and then activate **webclyde-child-theme** in **Appearance > Themes**.
3. Activate the **Elementor** plugin. The Portfolio post type will automatically appear in the WordPress sidebar with a dedicated dashboard icon (`dashicons-portfolio`).

---

## 🗃️ Portfolio Case Study System (CPT & Admin)

### Custom Post Type & Taxonomies
Defined in [`inc/portfolio-cpt.php`](file:///home/amzad/Desktop/webclyde/inc/portfolio-cpt.php):
- **Post Type**: `portfolio`
  - Slug: `/portfolio/`
  - Menu Icon: `dashicons-portfolio`
  - Supports: `title`, `editor`, `thumbnail`, `excerpt`, `custom-fields`, `revisions`, `author`, `page-attributes`, `elementor`.
- **Taxonomies**:
  - `portfolio_category` (Hierarchical categories, slug: `/portfolio-category/`)
  - `portfolio_tag` (Non-hierarchical tags, slug: `/portfolio-tag/`)
- **Elementor Compatibility**:
  - Automatically appended to the `elementor_cpt_support` option so Elementor is instantly available for portfolio posts.
  - Classic editor screen enabled for `portfolio` post type (`use_block_editor_for_post_type -> false`) to allow the custom details box to sit flush under the post title.

---

### Unified Under-Title Meta Panel
Instead of burying fields in low-priority meta boxes at the bottom of the page, [`inc/portfolio-metaboxes.php`](file:///home/amzad/Desktop/webclyde/inc/portfolio-metaboxes.php) hooks into:
```php
add_action('edit_form_after_title', 'webclyde_render_portfolio_under_title');
```
This renders the **"Portfolio Case Study Details & Elements"** control room directly beneath the post title.

#### Key Admin UX Features:
- **Persistent Tab Memory**: Active tabs are synced to an internal hidden input (`_portfolio_active_tab`), browser `sessionStorage`, and the URL hash (`#tab-overview`, `#tab-approach`). Saving or refreshing keeps you on the exact tab you were editing.
- **Scroll Preservation**: When the post saves and the page reloads with `?message=1`, the viewport automatically animates straight to the meta box.
- **Gutenberg / TinyMCE Autosave Compatibility**: TinyMCE WYSIWYG editors (`wp_editor`) cleanly trigger `tinyMCE.triggerSave()` on submit and mark the post dirty so no edits are lost.

---

### The 6 Data Tabs & Field Breakdown

```
┌────────────────────────────────────────────────────────────────────────┐
│               Portfolio Case Study Details & Elements                  │
├───────────────┬────────────────────────────────────────────────────────┤
│ ⚙️ Overview    │ • Logo, Site URL, Client / Organization                │
│ 🖼️ Gallery     │ • Services, Timeline, Industry, Platform, Location     │
│ ⚠️ Challenge   │ • Long-form Rich Text Description                      │
│ 🔨 Approach    │ • Native WordPress Multi-Image Carousel / Gallery      │
│ 📊 Results     │ • Challenge Title, Description & Dynamic Problems List │
│ 💬 Review      │ • Approach Title, Diagram, Repeatable Steps & Badge    │
│               │ • Results Title, Description & 3 Core Benchmark Stats  │
│               │ • Client Quote, Name, Title, Avatar & Verification Pic │
└───────────────┴────────────────────────────────────────────────────────┘
```

#### 1. ⚙️ Overview & Meta (`#tab-overview`)
- **Project / Brand Logo**: Media selector with live image preview and remove button.
- **Site URL**: External live URL (`https://...`).
- **Organization / Client**: Company name (e.g. *Acme Corp*).
- **Services Provided**: Comma-separated list (e.g. *Web Design, UI/UX, WooCommerce*).
- **Timeline / Duration**: Delivery timeframe (e.g. *6 Weeks / Jan - Mar 2024*).
- **Industry**: Niche (e.g. *E-Commerce, SaaS, Healthcare*).
- **Platform / Tech Stack**: Technologies used (e.g. *WordPress, Shopify, Next.js*).
- **Location**: Geographic market (e.g. *New York, USA*).
- **Project Overview / Description**: Full TinyMCE rich text editor.

#### 2. 🖼️ Image Carousel (`#tab-gallery`)
- Integrates with the **native WordPress Media Gallery Modal** (`wp.media.gallery.edit`).
- Allows drag-and-drop reordering, uploading multiple mockups at once, and deletion of individual items.
- Stored as comma-separated IDs (`_portfolio_gallery`) and an indexed array (`_portfolio_gallery_array`).

#### 3. ⚠️ The Challenge (`#tab-challenge`)
- **Challenge Title**: Section header (defaults to *"The Challenge"*).
- **Challenge Description**: Full TinyMCE rich text editor explaining background context.
- **The Main Problems (Dynamic Repeater)**:
  - Add unlimited problem items with the **"+ Add Problem"** button.
  - Drag-handle ready with individual row deletion.
  - Stored as an indexed PHP array of strings.

#### 4. 🔨 The Approach (`#tab-approach`)
- **Approach Title**: Section header (defaults to *"Our Approach"*).
- **Approach Image**: Diagram, architecture scheme, or wireframe screenshot.
- **Step-by-Step Approach Cards (Dynamic Repeater)**:
  - Add unlimited execution milestones (**"+ Add Step"**).
  - Each step contains:
    - **Step Badge**: Auto-numbered badge (`Step 1`, `Step 2`, `Step 3`, etc.).
    - **Step Title**: e.g., *"A custom theme, built for speed"*.
    - **Step Description**: Multi-line explanation of the tactical solution.
- **Approach Grid Extra Image / Badge (6th Slot Utility)**:
  - In a standard 3-column grid, 5 approach steps leave the 6th slot vacant.
  - Toggle `Show Badge / Image in Grid?` (`yes` / `no`).
  - Allows placing a partner badge, certification (e.g. *WordPress Certified Developer*), or trust seal, along with an optional outbound URL.

#### 5. 📊 Results & Metrics (`#tab-results`)
- **Result Title**: e.g., *"The Results: Faster Speed & 150% Checkout Growth"*.
- **Result Description**: Textarea explaining post-launch impact.
- **Performance Benchmarks**:
  - **Mobile Load Time**: e.g. `0.9s (-62% faster)`
  - **Google PageSpeed Score (Mobile)**: e.g. `98/100`
  - **Completed Checkouts After Launch**: e.g. `+185% Increase / 2,400+ checkouts`

#### 6. 💬 Client Review (`#tab-review`)
- **What the Client Said**: Testimonial quote.
- **Client Name**: Reviewer's full name.
- **Client Designation / Title / Company**: e.g., *Founder & CEO, BrandCo*.
- **Client Avatar / Photo**: Thumbnail profile photo.
- **Review Proof Image**: Trust verification badge, Clutch rating screenshot, or Google review snip.

---

### Database Schema & Meta Keys
All fields are saved under both `_key` (protected) and `key` (public) in `wp_postmeta` to ensure 100% compatibility with the REST API, WP-CLI, Elementor, and third-party plugins:

| Field Label | Storage Meta Key | Type | Description |
|---|---|---|---|
| Active Tab | `_portfolio_active_tab` | `string` | Tab persistence state |
| Project Logo | `_portfolio_logo` | `integer` | Media Attachment ID |
| Site URL | `_portfolio_site_url` | `string` | Validated URL |
| Organization | `_portfolio_organization` | `string` | Client name |
| Services | `_portfolio_services` | `string` | Services string |
| Timeline | `_portfolio_timeline` | `string` | Project duration |
| Industry | `_portfolio_industry` | `string` | Client industry |
| Platform | `_portfolio_platform` | `string` | CMS or Tech stack |
| Location | `_portfolio_location` | `string` | Location string |
| Description | `_portfolio_description` | `string (HTML)` | Rich text overview |
| Gallery IDs | `_portfolio_gallery` | `string` | Comma-separated attachment IDs |
| Gallery Array | `_portfolio_gallery_array` | `array` | PHP array of attachment IDs |
| Challenge Title | `_portfolio_challenge_title` | `string` | Header text |
| Challenge Description | `_portfolio_challenge_description` | `string (HTML)` | Challenge narrative |
| Main Problems | `_portfolio_main_problems` | `array` | Array of problem strings |
| Approach Title | `_portfolio_approach_title` | `string` | Header text |
| Approach Image | `_portfolio_approach_image` | `integer` | Media Attachment ID |
| Approach Steps | `_portfolio_approach_steps` | `array` | Array of `['title' => ..., 'desc' => ...]` |
| Approach Badge Toggle | `_portfolio_approach_has_badge` | `string` | `'yes'` or `'no'` |
| Approach Badge Image | `_portfolio_approach_badge_image` | `integer` | Media Attachment ID |
| Approach Badge Link | `_portfolio_approach_badge_link` | `string` | Outbound certification link |
| Results Title | `_portfolio_result_title` | `string` | Header text |
| Results Description | `_portfolio_result_description` | `string` | Narrative results |
| Mobile Load Time | `_portfolio_mobile_load_time` | `string` | Stat metric |
| PageSpeed Score | `_portfolio_pagespeed_score` | `string` | Stat metric |
| Completed Checkouts | `_portfolio_completed_checkouts` | `string` | Stat metric |
| Client Quote | `_portfolio_client_said` | `string (HTML)` | Testimonial quote |
| Client Name | `_portfolio_client_name` | `string` | Person's name |
| Client Designation | `_portfolio_client_designation` | `string` | Title / Role / Company |
| Client Avatar | `_portfolio_client_avatar` | `integer` | Media Attachment ID |
| Review Proof Image | `_portfolio_review_image` | `integer` | Media Attachment ID |

---

## 🏷️ Elementor Dynamic Tags Integration

Registered in [`inc/portfolio-dynamic-tags.php`](file:///home/amzad/Desktop/webclyde/inc/portfolio-dynamic-tags.php) under the group **"Portfolio Case Study"**:

```
Elementor Dynamic Tags Group: [Portfolio Case Study]
 ├── 📝 Portfolio Text Field   (Webclyde_Portfolio_Text_Tag)
 ├── 🖼️ Portfolio Image Field  (Webclyde_Portfolio_Image_Tag)
 ├── 🔗 Portfolio: Site URL    (Webclyde_Portfolio_URL_Tag)
 └── 🗂️ Portfolio Gallery Images (Webclyde_Portfolio_Gallery_Tag)
```

### Registered Dynamic Tags

#### 1. Portfolio Text Field (`portfolio-text-field`)
Works inside any standard Elementor text widget (Heading, Text Editor, Icon Box, Button, etc.):
- Selectable dropdown:
  - `Organization`, `Services`, `Timeline`, `Industry`, `Platform`, `Location`, `Project Overview / Description`
  - `Challenge Title`, `Challenge Description`
  - `Approach Title`
  - `Result Title`, `Result Description`
  - `Mobile Load Time`, `Google PageSpeed Score`, `Completed Checkouts`
  - `What the Client Said (Quote)`, `Client Name`, `Client Designation`

#### 2. Portfolio Image Field (`portfolio-image-field`)
Works inside Elementor Image widgets, Background Images, and the Frame Widget:
- Selectable sources:
  - `Default Featured Image` (with automatic fallback to latest portfolio item in editor mode)
  - `Portfolio Logo`
  - `Approach Image (Diagram / Mockup)`
  - `Approach Grid Badge Image`
  - `Review Proof Image`
  - `Client Avatar`

#### 3. Portfolio Site URL (`portfolio-url-field`)
Binds any link control directly to the project's live URL.

#### 4. Portfolio Gallery Images (`portfolio-gallery-field`)
Binds native Elementor Image Carousel or Gallery widgets directly to the case study's multi-image showcase gallery.

### Live Editor Fallback Mode
When designing a Single Portfolio Template in Elementor Theme Builder, dynamic tags automatically detect if a post has missing data and pull preview samples from other published portfolio posts or media library attachments. This ensures you never have to design with broken, empty widgets in the Elementor preview.

---

## 🧩 Elementor Custom Widgets

Registered in [`inc/portfolio-widgets.php`](file:///home/amzad/Desktop/webclyde/inc/portfolio-widgets.php) in the **"Portfolio Case Study"** (`webclyde-portfolio`) category.

### 1. Portfolio: The Main Problems Widget
- **Widget ID**: `webclyde_portfolio_problems`
- **Icon**: `eicon-editor-list-ol`
- **Purpose**: Displays the client's obstacles as a sleek card featuring **numbered circle badges**.
- **Controls**:
  - **Data Source**: Switch between `Dynamic (From Post Meta)` and `Custom Repeater List`.
  - **Card Title**: Customizable title text (default: *"THE MAIN PROBLEMS"*) and HTML tag (`h2`-`h6`, `div`, `p`).
  - **Full Styling Controls**:
    - Card background, border, box-shadow, and responsive padding.
    - Title typography, color, and spacing.
    - Badges background color, active text color, and border-radius.
    - List item row typography, margins, and separator dividers.

### 2. Portfolio: Approach & Steps Widget
- **Widget ID**: `webclyde_portfolio_approach`
- **Icon**: `eicon-flow`
- **Purpose**: Renders the execution roadmap as a **responsive card grid with mint badges** and an optional certificate badge.
- **Controls**:
  - **Data Source**: `Dynamic (From Post Meta)` or `Custom Repeater Cards`.
  - **Responsive Columns**: 1 to 4 columns across Desktop, Tablet, and Mobile.
  - **Approach Cards Styling**:
    - Card background, hover lift translateY animation, hover shadow, and border radius.
    - Mint Step Badge styling (size, font size, background color, text color).
    - Step Title and Description typography, line-height, and margins.
  - **6th Slot Badge Controls**:
    - Toggle badge on/off, custom image override, and external link assignment.

### Additional Render Engine Functions & Shortcodes
[`inc/portfolio-render.php`](file:///home/amzad/Desktop/webclyde/inc/portfolio-render.php) contains modular rendering functions and native WordPress shortcodes for all sections. You can embed any component into standard WordPress content, Classic Editor pages, or Elementor Shortcode widgets:

| Shortcode | Parameters | Output Component |
|---|---|---|
| `[portfolio_case_study]` or `[webclyde_portfolio]` | `id="123"` | Full case study layout with all sections |
| `[portfolio_meta]` | `id="123"` `show_logo="1"` `show_button="1"` `show_description="1"` | Overview meta card (logo, site link, client specs) |
| `[portfolio_carousel]` | `id="123"` `layout="slider"` | Smooth autoplay multi-image showcase carousel |
| `[portfolio_challenge]` | `id="123"` `show_title_desc="1"` | Challenge narrative and problem list |
| `[portfolio_problems]` | `id="123"` `title="THE MAIN PROBLEMS"` `title_tag="h3"` | Numbered circle badges problem card |
| `[portfolio_approach]` | `id="123"` `title_tag="h3"` | Milestone execution cards grid with mint badges |
| `[portfolio_results]` | `id="123"` | Results narrative & 3 benchmark metric boxes |
| `[portfolio_review]` | `id="123"` | Client quote, author details, avatar & verification proof |

*Note: If `id` is omitted, the shortcode automatically resolves the current post ID in single templates, Elementor Theme Builder previews, and query loops.*

---

## 🖼️ The "Frame" 3D Mockup System (Frame Everything)

Located in `/frame/`, this module allows any image or dynamic screenshot to be presented inside a realistic device/tablet mockup.

```
┌─────────────────────────────────────────────────────────────┐
│                 .tfw (Frame Container Box)                  │
│                                                             │
│   ┌───────────────────────────────────────────────────┐     │
│   │ .tfw-shot (Screen capture)                        │     │
│   │ • Transformed with 3D perspective                 │     │
│   │ • Rotated & warped to match the display angle     │     │
│   │ • Dynamic Featured Image via Elementor Tags       │     │
│   └───────────────────────────────────────────────────┘     │
│                                                             │
│   ┌───────────────────────────────────────────────────┐     │
│   │ .tfw-frame (Transparent PNG/WebP Bezel Overlay)   │     │
│   │ • Positioned absolute inset: 0 (z-index: 2)       │     │
│   │ • Prevents image spill; creates authentic bezel   │     │
│   └───────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

### Concept & Pure CSS 3D Perspective
Unlike clunky canvas or WebGL mockups that degrade site performance, the **Frame Widget** (`tfw_frame`) utilizes **hardware-accelerated CSS 3D transforms** and **Container Queries (`cqw`)** paired with a synchronized JavaScript fallback.

The screenshot (`.tfw-shot`) is rendered *behind* a transparent frame overlay (`.tfw-frame`). The frame bezel sits on top with `pointer-events: none; z-index: 2`, ensuring the screenshot never bleeds outside the physical screen boundaries.

---

### Mathematical Perspective & Container Queries
In [`frame/assets/frame.css`](file:///home/amzad/Desktop/webclyde/frame/assets/frame.css):

```css
.tfw {
  position: relative;
  display: block;
  width: 100%;
  aspect-ratio: 1683 / 987;
  container-type: inline-size;
  isolation: isolate;

  /* Perspective distance = 207.962% of widget width */
  --tfw-p: 2400px;
  --tfw-dx: 5px;
  --tfw-dy: -6px;
}

@supports (width: 1cqw) {
  .tfw {
    --tfw-p: 207.962cqw;
    --tfw-dx: 0.4545cqw;
    --tfw-dy: -0.5455cqw;
  }
}

.tfw .tfw-shot {
  position: absolute;
  left: 2px;
  top: -4px;
  width: 87.9382%;
  height: 100%;
  transform-origin: 0 0;
  backface-visibility: hidden;
  transform: perspective(209.9620cqw)
             rotateX(28.49632deg)
             rotateY(-12.02312deg)
             rotateZ(-1.90820deg)
             translate(26.53616%, 9.18692%)
             matrix(0.72954337, 0.14695745, -0.38727305, 0.69813302, 0, 0);
  border-radius: 6px;
}
```

#### Synchronized JS Fallback:
For browsers or environments where CSS Container Query units are not fully supported, [`frame/frame.php`](file:///home/amzad/Desktop/webclyde/frame/frame.php) injects a footer script running `ResizeObserver` that dynamically calculates:
$$\text{Perspective (px)} = \text{Element Width} \times 2.07962$$
and sets the `--tfw-p` CSS custom property in real time.

---

### Frame Image Resolution Hierarchy
The function `tfw_frame_src()` determines the frame mockup bezel automatically via four graceful fallbacks:
1. **PHP Constant**: Checks `TFW_FRAME_URL` (if defined in `wp-config.php` or `functions.php`).
2. **Local Theme Asset**: Looks for `frame/assets/frame.webp`.
3. **Fallback Theme Asset**: Looks for `frame/assets/tablet-frame.webp`.
4. **Uploads Directory**: Checks `/uploads/2026/09/tablet-mockup-1.webp`.
5. **Graceful Fallback Mode (`.tfw-plain`)**: If no frame bezel file is located, the widget automatically disables 3D transformation and renders a flat, undistorted responsive image to ensure the layout never breaks.

---

### Elementor Widget Controls & Features

#### 1. Screen Image Section
- **Screen Image**: Media picker with **Dynamic Tag** support (bind to *Featured Image*, *Portfolio Image Field*, or static upload).
- **Image Size**: Choose full, large, medium_large, etc.
- **Fit**:
  - `Cover`: Fills the device screen completely (auto-cropped).
  - `Contain`: Shows the full image without clipping.
  - `Fill`: Stretches to fit the exact viewport dimensions.
- **Position**: Adjust alignment (`Top Center`, `Center`, `Top Left`, etc.) when in cover/contain modes.
- **Show Frame**: Toggle the frame bezel on or off.

#### 2. Frame Section
- **Frame Image**: Overwrite the default bezel with your own transparent PNG/WebP (e.g. iPhone, MacBook, iPad, or custom monitor frame).

#### 3. Layout Section
- **Max Width**: Responsive slider with `px` and `%` units (default: `1100px`).
- **Alignment**: Quick alignment (`Left`, `Center`, `Right`) with margin auto calculations.
- **Image Link**: Wrap the entire frame in a link (supports Dynamic Tags & `is_external` / `nofollow`).

---

### Responsive Behavior & Mobile Safety
Mockups with angled 3D transforms often cause overflow or text overlapping on tablets and mobile devices. The Frame module eliminates this with dedicated responsive CSS overrides:

```css
@media (max-width: 1024px) {
  .elementor-widget-tfw_frame,
  .elementor-widget-tfw_tablet_frame {
    position: relative !important;
    inset: auto !important;
    transform: none !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 24px 0 !important;
    clear: both;
  }
}
```
This forces the mockup into a clean, relative flow below any introductory copy, maintaining the perspective scale without breaking horizontal viewport boundaries.

---

## 🎨 Frontend Rendering & Assets Pipeline

1. **CSS Assets**:
   - `assets/portfolio-frontend.css`: Modular styles for cards, mint badges, typography, quote callouts, and performance stat boxes.
   - `frame/assets/frame.css`: Container query rules, perspective matrix calculations, and mobile overrides.
2. **JS Assets**:
   - `assets/portfolio-frontend.js`: Lightweight, dependency-free vanilla/jQuery carousel supporting touch swipe gestures, continuous marquee flow (`smooth_ticker`), and auto-pause on hover.
3. **Cache Busting**: All assets use `filemtime()` versioning during development to guarantee instant cache invalidation upon saving changes.

---

## 🚀 Step-by-Step Workflow Guide

### 1. Creating a Case Study
1. In the WordPress admin dashboard, navigate to **Portfolio > Add New Project**.
2. Enter the **Project Title** (e.g., *Artisan Roasters — E-Commerce Redesign*).
3. Set the **Featured Image** (this will be the screenshot framed in mockups).
4. Assign **Portfolio Categories** (e.g., *UI/UX Design, WooCommerce*) and **Tags**.
5. Navigate through the 6 tabs under the title:
   - **Overview**: Upload the brand logo, live site URL, and fill in client details.
   - **Image Carousel**: Click **Add** to upload 3–6 project screenshots into the gallery.
   - **The Challenge**: Enter the challenge summary and add bulleted problems.
   - **The Approach**: Add step-by-step methodology cards and optional certification badges.
   - **Results**: Fill in load time improvements, Google PageSpeed scores, and conversion gains.
   - **Client Review**: Paste the client's testimonial and upload their avatar.
6. Click **Publish**.

---

### 2. Building an Elementor Single Portfolio Template
1. Go to **Templates > Theme Builder > Single Post** (or Single Page).
2. Create a new template and set the Display Condition to:
   ```
   Include: Portfolios > All
   ```
3. Add a Hero Section:
   - Add a Heading widget and set dynamic tag to `Post Title`.
   - Add an Elementor Image widget and set dynamic tag to `Portfolio Case Study > Portfolio Image Field > Portfolio Logo`.
   - Add an Elementor Text widget and set dynamic tag to `Portfolio Case Study > Portfolio Text Field > Organization`.
4. Add the Frame Device Mockup:
   - Drag the **Frame** widget into your column.
   - In **Screen Image**, click the Dynamic Tag icon and select **Featured Image**.
   - Watch the project's hero screenshot render inside the 3D tablet mockup!
5. Add The Main Problems:
   - Drag the **Portfolio: The Main Problems** widget.
   - Keep data source as `Dynamic (From Post Meta)`.
6. Add The Approach Grid:
   - Drag the **Portfolio: Approach & Steps** widget.
   - Set Columns to `3` for desktop.
7. Add the Results & Review:
   - Use standard Elementor widgets with dynamic tags, or drop in `[portfolio_results]` / `[portfolio_review]`.

---

### 3. Framing Screenshots with Dynamic Tags
To showcase multiple device frames across your case study:
1. Drag the **Frame** widget into your layout.
2. Set **Screen Image** to:
   - **Dynamic Tag > Portfolio Image Field > Approach Image**, or
   - Any uploaded image from the Media Library.
3. In **Layout**, set Max Width to `900px` and Alignment to `Center`.
4. To test how it appears on tablets or phones, toggle Elementor's Responsive Mode; the frame scales down smoothly.

---

## 🔒 Security, Performance & Best Practices

- **Security & Sanitization**:
  - All text inputs pass through `sanitize_text_field` or `sanitize_textarea_field`.
  - Rich text WYSIWYG inputs are sanitized using `wp_kses_post`.
  - URLs pass through `esc_url_raw` upon saving and `esc_url` upon output.
  - Nonce verification is strictly enforced on save (`webclyde_portfolio_nonce` and `update-post_{$id}`).
  - User permissions are validated with `current_user_can('edit_post', $post_id)`.
- **Performance**:
  - Pure CSS 3D rendering avoids heavy JavaScript libraries (such as Three.js or GSAP 3D plugins) for device mockups.
  - Image files use modern `.webp` compression.
  - Images utilize native `loading="lazy"` and `decoding="async"` attributes to maintain top Google PageSpeed scores.
  - Scripts and styles are enqueued conditionally and versioned via `filemtime`.

---

## 👨‍💻 Developer Notes & Extension Points

- **Filter CPT Registration**: Filter `webclyde_register_portfolio_cpt` on `init` priority 5.
- **Custom Post Meta Registration**: Add additional fields via `register_post_meta()` in `inc/portfolio-metaboxes.php`.
- **Extend Dynamic Tags**: Register custom Dynamic Tag classes in `inc/portfolio-dynamic-tags.php` under the `'portfolio_case_study'` group.
- **Mockup Frame Assets**: To supply custom frames globally, define `TFW_FRAME_URL` in `wp-config.php`:
  ```php
  define('TFW_FRAME_URL', 'https://your-cdn.com/assets/custom-frame.webp');
  ```

---

*Built with performance, precision, and modern web standards by **Webclyde**.*
