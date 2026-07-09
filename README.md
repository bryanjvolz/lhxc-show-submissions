# LHXC Show Submissions

This Wordpress plugin allows users to submit their shows & flyers on the Louisville Hardcore Show Submissions page for use with The Events Calendar plugin.

## Features

- Submit shows with a title, date, and flyer attachment
- Submissions are reviewed by an admin before being added to the Flyer Archive or Shows Calendar. Approvals and additions can be done separately
- Once approved, the show is added to The Events Calendar automatically. Refinement can be done before publishing the new post
- Options are available to dispatch email notifications upon either submission or approval. At current, only a hardcoded list of emails will be used. Future versions may use the submitter's info or the promoters.

## Installation
Download a ZIP file, unzip and either upload to your Wordpress site or drop it into your wp-content/plugins folder.

## To Build
Run `npm run build` to build the plugin.

## To Test
Run `npm run cypress:open` to open the Cypress test runner.
Run `npm run test:e2e` to run headless Cypress tests.

## To Lint
Run `npm run lint:php` to check for PHP coding standards violations.
Run `npm run lint:php:fix` to automatically fix PHP coding standards violations.