# WooCommerce Dashboard

This is a feature plugin for a modern, javascript-driven dashboard for WooCommerce.

## Prerequisites

[Gutenberg](https://wordpress.org/plugins/gutenberg/) and [WooCommerce](https://wordpress.org/plugins/woocommerce/) should be installed prior to activating the WooCommerce Dashboard feature plugin.

## Development

After cloning the repo, install dependencies with `npm install`. Now you can build the files using one of these commands:

 - `npm run build` : Build a production version
 - `npm run dev` : Build a development version
 - `npm run watch` : Build a development version, watch files for changes

There are also some helper scripts:

 - `npm run lint` : Run eslint over the javascript files
 - `npm run i18n` : Create a PHP file with the strings from the javascript files, [used to get around lack of JS support in WordPress.org](https://github.com/WordPress/packages/tree/master/packages/i18n#build).