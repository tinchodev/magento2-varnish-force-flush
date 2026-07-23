# Nx6_VarnishPurge

Magento 2 module that adds a manual "force flush" action for Varnish full
page cache, exposed in two places in the admin panel:

- **Stores > Configuration > Advanced > System > Full Page Cache**, as a
  "Force Purge" button next to the Varnish settings.
- **System > Tools > Cache Management**, as a "Force Varnish Flush" button
  placed right after "Flush JavaScript/CSS Cache". This button only appears
  when Varnish is the configured caching application
  (`system/full_page_cache/caching_application` = Varnish) and the current
  admin user has permission for it.

Both buttons trigger the same purge operation: an HTTP `PURGE` request sent
to the configured Varnish backend host/port
(`system/full_page_cache/varnish/backend_host` and `backend_port`) with an
`X-Magento-Tags-Pattern: .*` header, which tells Varnish to discard its
entire cache. The request/response is logged for troubleshooting.

## How it works

- `Model/VarnishPurger.php` contains the actual purge logic (the cURL
  `PURGE` request) and is shared by both entry points below.
- `Controller/Adminhtml/Varnish/Purge.php` handles the config-page button:
  it's called via AJAX (`fetch`) and returns a JSON `{success, message}`
  response, rendered inline next to the button
  (`view/adminhtml/templates/system/config/purge_button.phtml`,
  `Block/Adminhtml/System/Config/PurgeButton.php`).
- `Controller/Adminhtml/Cache/Purge.php` handles the Cache Management page
  button: it's a plain GET action that performs the purge, sets a
  success/error admin notice via the message manager, and redirects back to
  the Cache Management page — matching the behavior of Magento's own cache
  flush buttons.
- `Block/Adminhtml/Cache/Additional.php` extends Magento's core
  `Magento\Backend\Block\Cache\Additional` block to add the Varnish
  visibility/ACL checks and purge URL used by the overridden
  `view/adminhtml/templates/system/cache/additional.phtml` template (swapped
  in via `view/adminhtml/layout/adminhtml_cache_index.xml`, no core files
  are modified).
- Access to both buttons is gated by the `Nx6_VarnishPurge::varnish_purge`
  ACL resource (`etc/acl.xml`).

## Installation

Copy the `Nx6/VarnishPurge` directory into your Magento installation's
`app/code/` directory, then run:

```
bin/magento module:enable Nx6_VarnishPurge
bin/magento setup:upgrade
```

Configure the Varnish backend host/port under **Stores > Configuration >
Advanced > System > Full Page Cache** (standard Magento Varnish settings)
before using either purge button.

## Structure

```
Nx6/VarnishPurge/
├── Block/
│   └── Adminhtml/
│       ├── Cache/Additional.php           # Extends core cache management block
│       └── System/Config/PurgeButton.php  # Config-page button block
├── Controller/
│   └── Adminhtml/
│       ├── Cache/Purge.php                # Cache Management page action (redirect)
│       └── Varnish/Purge.php              # Config-page action (AJAX/JSON)
├── Model/
│   ├── VarnishPurger.php                  # Shared purge logic
│   └── VarnishPurgeResult.php             # Purge outcome value object
├── etc/
│   ├── acl.xml
│   ├── adminhtml/
│   │   ├── routes.xml
│   │   └── system.xml
│   └── module.xml
├── view/adminhtml/
│   ├── layout/adminhtml_cache_index.xml   # Overrides core cache.additional block
│   └── templates/
│       ├── system/cache/additional.phtml
│       └── system/config/purge_button.phtml
└── registration.php
```
