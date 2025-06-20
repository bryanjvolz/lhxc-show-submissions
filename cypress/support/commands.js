// Custom command for WordPress login
Cypress.Commands.add('login', (username = Cypress.env('wpUsername'), password = Cypress.env('wpPassword')) => {
  cy.visit('/wp-login.php')
  cy.get('#user_login').type(username)
  cy.get('#user_pass').type(password)
  cy.get('#wp-submit').click()
})

// Custom command for WordPress admin login
Cypress.Commands.add('loginAsAdmin', () => {
  cy.login(Cypress.env('adminUsername'), Cypress.env('adminPassword'))
})