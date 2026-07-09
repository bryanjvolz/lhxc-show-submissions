describe('Show Submission Form', () => {
  beforeEach(() => {
    cy.visit('/show-submissions/')
  })

  it('should submit a show successfully', () => {
    // Fill in the form with The Events Calendar integration
    cy.get('#booking_name').select('Camp Spaceman') // Select first organizer, 1st option is the 'Add' feature
    cy.get('#booking_name').trigger('change')
    cy.get('#booking_email').type('booker@example.com')
    cy.get('#venue_name').focus()
    // cy.get('select#venue_name').select('Portal');

    cy.get('select#venue_name').find('option[value="5667"]').should('exist');

    // cy.get('select#venue_name').then($select => {
    //   $select.val('Portal');
    //   $select.trigger('change');
    // });

    cy.get('select#venue_name')
    .select('5667') // select by value
    .should('have.value', '5667') // assert it was selected
    .then($select => {
      const selectedValue = $select.val();
      cy.log('Selected value is: ' + selectedValue);
    });

    // cy.get('select#venue_name').select('Portal');

    // Date and time fields
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    const formattedDate = tomorrow.toISOString().split('T')[0]

    cy.get('#show_date').type(formattedDate)
    cy.get('#door_time').type('18:00')
    cy.get('#time_zone').select('America/Kentucky/Louisville')

    // Price and links
    cy.get('#ticket_price').type('10')
    cy.get('#show_link').type('https://example.com/show')

    // Performers
    cy.get('#performers').type('Test Band 1\nTest Band 2')

    // Image upload
    cy.get('#dropZone').selectFile('cypress/fixtures/test-flyer.jpg', { action: 'drag-drop' })

    // Submit and review
    cy.get('button[type="submit"]').click()

    // Verify review step is shown
    cy.get('#step2').should('be.visible')

    // Submit final
    cy.get('button').contains('Submit Show').click()

    // Assert success message
    cy.on('window:alert', (alertText) => {
      expect(alertText).to.equal('Show submitted successfully!');
    });
    // cy.contains('Show submitted successfully!').should('be.visible')
  })

  it('should validate required fields', () => {
    // cy.get('button[type="submit"]').click()

    // Check for validation messages on required fields
    // cy.get('#booking_name:invalid').should('exist')
    // cy.get('#venue_name:invalid').should('exist')
    // cy.get('#show_date:invalid').should('exist')
    // cy.get('#door_time:invalid').should('exist')
    // cy.get('#time_zone:invalid').should('exist')
    // cy.get('#ticket_price:invalid').should('exist')
    // cy.get('#performers:invalid').should('exist')
  })

  it('should handle new organizer submission', () => {
    // Select "Add New Organizer"
    cy.get('#booking_name').select('+ Add New Organizer')
    // cy.get('#booking_name').should(($input) => {
    //   expect($input).to.not.have.attr('required');
    // });

    // Verify new organizer input appears
    cy.get('#new_organizer_name').should('be.visible')
    cy.get('#booking_name').should('be.hidden')

    // Fill in new organizer details
    cy.get('#new_organizer_name').type('New Test Organizer')
    cy.get('#booking_email').type('neworganizer@example.com')
  })

  it('should handle new venue submission', () => {
    // Wait for the venue select to be ready
    cy.get('select#venue_name').should('exist').and('be.visible');

    // Wait for options to be loaded
    cy.get('select#venue_name option').should('have.length.gt', 1);

    cy.get('select#venue_name').select('new');

    // Verify venue address input appears
    cy.get('#venue_address_group').should('be.visible');
    cy.get('#venue_address_input').should('be.visible').and('have.attr', 'required');

    //Add new Venue name
    cy.get('#new_venue_name').type('BeerCrusher 666');
    // Fill in venue address
    cy.get('#venue_address_input')
      .type('700 Central Avenue', { delay: 100 });

      cy.get('.pac-item')  // Google autocomplete suggestion item
      .should('exist')    // Wait for it to load
      .first().click();   // Select first result

    // Wait for Google Places to populate hidden fields
    cy.get('#venue_street').should('have.value', '700 Central Avenue');
    cy.get('#venue_city').should('have.value', 'Louisville');
    cy.get('#venue_state').should('have.value', 'KY');
    cy.get('#venue_zip').should('have.value', '40215');
    cy.get('#new_venue_name').should('have.value', 'BeerCrusher 666');
  })

  it('should handle image upload via drag and drop', () => {
    // Test drag and drop functionality
    cy.get('#dropZone').selectFile('cypress/fixtures/test-flyer.jpg', { action: 'drag-drop' })

    // Verify preview is shown
    cy.get('#imagePreview img').should('be.visible')
  })
})