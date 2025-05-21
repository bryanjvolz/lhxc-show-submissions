jQuery(document).ready(function($) {
    let uploadedFiles = [];
    const maxFiles = 5;
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('images');

    // Handle drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');

        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Handle click to upload
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        if (files.length > maxFiles) {
            alert(`Maximum ${maxFiles} images allowed`);
            fileInput.value = '';
            return;
        }

        // Validate file types and sizes
        const validFiles = Array.from(files).filter(file => {
            const isValidType = ['image/jpeg', 'image/png'].includes(file.type);
            const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB

            if (!isValidType) {
                alert(`${file.name} is not a valid image type`);
            }
            if (!isValidSize) {
                alert(`${file.name} exceeds 5MB size limit`);
            }

            return isValidType && isValidSize;
        });

        uploadedFiles = validFiles;
        updateImagePreview();
    }

    function updateImagePreview() {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';

        uploadedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" data-index="${index}">×</button>
                `;
                preview.appendChild(div);

                // Add remove button handler
                div.querySelector('.remove-image').addEventListener('click', function() {
                    uploadedFiles.splice(index, 1);
                    updateImagePreview();
                });
            };
            reader.readAsDataURL(file);
        });
    }

    // Form submission handling
    $('#showSubmissionStep1').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        uploadedFiles.forEach(file => {
            formData.append('images[]', file);
        });

        // Display review
        const review = generateReview(formData);
        $('#submissionReview').html(review);
        showStep(2);
    });

    function generateReview(formData) {
        let html = '<div class="review-content">';
        for (let [key, value] of formData.entries()) {
            // Skip nonce and images from review display
            if (key !== 'images[]' && key !== 'nonce') {
                html += `<div class="review-item">
                    <strong>${key.replace('_', ' ').toUpperCase()}:</strong>
                    <span>${value}</span>
                </div>`;
            }
        }
        html += '<div class="review-images">';
        uploadedFiles.forEach(file => {
            const url = URL.createObjectURL(file);
            html += `<img src="${url}" alt="Upload preview">`;
        });
        html += '</div></div>';
        return html;
    }

    window.submitFinal = function() {
        const formData = new FormData($('#showSubmissionStep1')[0]);
        formData.append('action', 'submit_show');
        formData.append('_ajax_nonce', showSubmissions.nonce);  // Add this line if not present
        formData.append('nonce', showSubmissions.nonce);
        console.log(showSubmissions.ajaxurl);
        $.ajax({
            url: showSubmissions.ajaxurl,  // Use the localized URL
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Show submitted successfully!');
                    location.reload();
                } else {
                    alert('Error submitting show: ' + response.data);
                }
            },
            error: function() {
                alert('Error submitting show. Please try again.');
            }
        });
    };

    window.showStep = function(step) {
        $('.form-step').hide();
        $(`#step${step}`).show();
    };

    // Initialize Google Places Autocomplete
    function initializeAutocomplete() {
        const input = document.getElementById('venue_address_autocomplete');
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['address'],
            componentRestrictions: { country: 'us' }
        });

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            let street = '', city = '', state = '', zip = '';

            // Extract address components
            for (const component of place.address_components) {
                const type = component.types[0];

                switch (type) {
                    case 'street_number':
                        street = component.long_name + ' ';
                        break;
                    case 'route':
                        street += component.long_name;
                        break;
                    case 'locality':
                        city = component.long_name;
                        break;
                    case 'administrative_area_level_1':
                        state = component.short_name;
                        break;
                    case 'postal_code':
                        zip = component.long_name;
                        break;
                }
            }

            // Populate hidden fields
            document.getElementById('venue_street').value = street;
            document.getElementById('venue_city').value = city;
            document.getElementById('venue_state').value = state;
            document.getElementById('venue_zip').value = zip;

            // Populate the original venue_address field for database storage
            document.getElementById('venue_address').value =
                `${street}\n${city}, ${state} ${zip}`;
        });
    }

    // Initialize autocomplete when document is ready
    if (typeof google !== 'undefined') {
        google.maps.event.addDomListener(window, 'load', initializeAutocomplete);
    }

    // Handle venue selection
    const venueSelect = document.getElementById('venue_name');
    const venueAddressGroup = document.getElementById('venue_address_group');
    const addressInput = document.getElementById('venue_address_autocomplete');
    let autocomplete;

    if (venueSelect) {
        venueSelect.addEventListener('change', function() {
            if (this.value === 'new') {
                venueAddressGroup.style.display = 'block';
                // Initialize autocomplete when the input becomes visible
                if (!autocomplete && typeof google !== 'undefined') {
                    initVenueAutocomplete();
                }
            } else {
                venueAddressGroup.style.display = 'none';
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    document.getElementById('venue_street').value = selectedOption.dataset.street || '';
                    document.getElementById('venue_city').value = selectedOption.dataset.city || '';
                    document.getElementById('venue_state').value = selectedOption.dataset.state || '';
                    document.getElementById('venue_zip').value = selectedOption.dataset.zip || '';
                }
            }
        });
    }

    function initVenueAutocomplete() {
        if (!addressInput) return;

        autocomplete = new google.maps.places.Autocomplete(addressInput, {
            types: ['address'],
            componentRestrictions: { country: 'us' }
        });

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            let street = '', city = '', state = '', zip = '';

            for (const component of place.address_components) {
                const type = component.types[0];
                switch (type) {
                    case 'street_number':
                        street = component.long_name + ' ';
                        break;
                    case 'route':
                        street += component.long_name;
                        break;
                    case 'locality':
                        city = component.long_name;
                        break;
                    case 'administrative_area_level_1':
                        state = component.short_name;
                        break;
                    case 'postal_code':
                        zip = component.long_name;
                        break;
                }
            }

            document.getElementById('venue_street').value = street;
            document.getElementById('venue_city').value = city;
            document.getElementById('venue_state').value = state;
            document.getElementById('venue_zip').value = zip;
        });
    }

    // Initialize Google Places when the API is loaded
    if (typeof google === 'object' && typeof google.maps === 'object') {
        // Only initialize if the "new venue" option is selected
        if (venueSelect && venueSelect.value === 'new') {
            initVenueAutocomplete();
        }
    } else {
        // If Google Maps isn't loaded yet, wait for it
        window.initVenueAutocomplete = initVenueAutocomplete;
    }
});