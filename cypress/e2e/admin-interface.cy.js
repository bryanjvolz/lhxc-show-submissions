describe('Show Submissions Admin Interface', () => {
  beforeEach(() => {
    // Login as admin (you'll need to customize this)
    // cy.loginAsAdmin()
    
    // Visit the admin page
    cy.visit('/wp-admin/admin.php?page=show-submissions')
  })

  it('should display submissions list', () => {
    cy.get('.wp-list-table').should('be.visible')
    cy.get('tr[data-id]').should('have.length.at.least', 1)
  })

  it('should toggle submission approval', () => {
    // Find first unapproved submission
    cy.get('.approval-toggle:not(:checked)').first().as('toggleButton')
    
    // Click the toggle
    cy.get('@toggleButton').click()
    
    // Verify the toggle state changed
    cy.get('@toggleButton').should('be.checked')
  })

  it('should view submission details', () => {
    // Click view details button
    cy.get('.button').contains('View Details').first().click()
    
    // Verify details page loaded
    cy.get('.wrap h1').should('contain', 'Submission Details')
  })
})