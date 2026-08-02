# Estatein Child Theme

Estatein Child is a custom WordPress child theme developed for the Estatein real estate website.

## Figma Design

The website is based on the [Estatein Real Estate Business Website UI Template](https://www.figma.com/design/KmOPVpxwqcz7H2PqMGUnjJ/Real-Estate-Business-Website-UI-Template---Dark-Theme-%7C-Produce-UI--Community---Copy-?node-id=45-2&p=f&t=psuHtxpAlU9Pijze-0).

## Parent Theme

This child theme requires the following parent theme:

- **Hello Elementor** (`hello-elementor`)

Install and activate Hello Elementor before activating Estatein Child. Using a child theme keeps the project customizations separate from the parent theme and prevents parent-theme updates from overwriting them.

## Plugins Used

- **Elementor** - Builds and manages editable page layouts and saved templates.
- **Advanced Custom Fields (ACF)** - Manages custom post types and editable fields for properties, testimonials, and FAQs.
- **Contact Form 7** - Provides the property inquiry form.
- **Yoast SEO** - Manages page titles and meta descriptions.
- **WebP Uploads** - Supports optimized WebP image handling.
- **WP Super Cache** - Manages page caching.
- **Query Monitor** - Helps check PHP and database errors during development.

## Content Management

Page layouts are managed with Elementor. Dynamic property, testimonial, and FAQ content is managed through WordPress Admin and ACF. Contact form content and email settings are managed under **Contact > Contact Forms**.

## Notes

- WordPress database content and plugin settings are not stored in this child-theme directory.
