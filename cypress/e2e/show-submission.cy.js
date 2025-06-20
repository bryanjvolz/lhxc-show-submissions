describe('Show Submission Form', () => {
  beforeEach(() => {
    // Visit the page where the submission form is embedded
    cy.visit('/show-submissions/')

    // Login if needed (you'll need to customize this based on your setup)
    // cy.login()
  })

  it('should submit a show successfully', () => {
    // Fill in the form - venue address only visible when new Venue is chosen
    cy.get('[name="submitter_name"]').type('Test Submitter')
    cy.get('[name="submitter_email"]').type('test@example.com')
    cy.get('[name="booking_name"]').type('Test Booker')
    cy.get('[name="booking_email"]').type('booker@example.com')
    cy.get('[name="venue_name"]').type('Test Venue')
    cy.get('[name="venue_address_input"]').type('123 Test St')
    cy.get('[name="show_date"]').type('2024-01-01')
    cy.get('[name="door_time"]').type('19:00')
    cy.get('[name="music_start_time"]').type('20:00')
    cy.get('[name="performers"]').type('Test Band 1, Test Band 2')
    cy.get('[name="door_price"]').type('10')
    cy.get('[name="ticket_price"]').type('12')
    cy.get('[name="show_link"]').type('https://example.com/show')
    cy.get('[name="ticket_link"]').type('https://example.com/tickets')

    // Submit the form
    cy.get('form').submit()

    // Assert success message
    cy.contains('Show submitted successfully!').should('be.visible')
  })

  it('should validate required fields', () => {
    // Try to submit empty form
    cy.get('form').submit()

    // Check for validation messages
    cy.get('[name="submitter_name"]:invalid')
      .should('have.length', 1)
    cy.get('[name="submitter_email"]:invalid')
      .should('have.length', 1)
  })

  it('should handle image upload', () => {
    // Prepare a test image
    cy.fixture('test-flyer.jpg').as('testImage')

    // Upload the image
    cy.get('input[type="file"]').selectFile('@testImage')

    // Verify image preview is shown
    cy.get('.preview-item img').should('be.visible')
  })
})