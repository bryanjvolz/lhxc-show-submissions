import { defineConfig } from 'cypress'
import dotenv from 'dotenv'

dotenv.config()

export default defineConfig({
  projectId: '4jvazm',
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:1004',
    supportFile: 'cypress/support/e2e.js',
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    setupNodeEvents(on, config) {
      // implement node event listeners here
      return config
    },
    env: {
      wpUsername: process.env.CYPRESS_WP_USERNAME,
      wpPassword: process.env.CYPRESS_WP_PASSWORD,
      adminUsername: process.env.CYPRESS_ADMIN_USERNAME,
      adminPassword: process.env.CYPRESS_ADMIN_PASSWORD
    }
  },
})