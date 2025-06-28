document.addEventListener("DOMContentLoaded", function () {
  let uploadedFiles = [];
  const maxFiles = 5;
  const dropZone = document.getElementById("dropZone");
  const fileInput = document.getElementById("images");

  // Handle drag and drop
  dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.classList.add("dragover");
  });

  dropZone.addEventListener("dragleave", (e) => {
    e.preventDefault();
    dropZone.classList.remove("dragover");
  });

  dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZone.classList.remove("dragover");
    const files = e.dataTransfer.files;
    handleFiles(files);
  });

  // Handle click to upload
  dropZone.addEventListener("click", () => {
    fileInput.click();
  });

  fileInput.addEventListener("change", (e) => {
    handleFiles(e.target.files);
  });

  function handleFiles(files) {
    if (files.length + uploadedFiles.length > maxFiles) {
      alert(`Maximum ${maxFiles} images allowed`);
      fileInput.value = "";
      return;
    }

    // Validate file types and sizes
    const validFiles = Array.from(files).filter((file) => {
      const isValidType = ["image/jpeg", "image/png"].includes(file.type);
      const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB

      if (!isValidType) {
        alert(`${file.name} is not a valid image type`);
      }
      if (!isValidSize) {
        alert(`${file.name} exceeds 5MB size limit`);
      }

      return isValidType && isValidSize;
    });

    // Filter out duplicates based on filename
    const newFiles = validFiles.filter((newFile) => {
      return !uploadedFiles.some(
        (existingFile) => existingFile.name === newFile.name
      );
    });

    uploadedFiles = [...uploadedFiles, ...newFiles];
    updateImagePreview();
  }

  function updateImagePreview() {
    const preview = document.getElementById("imagePreview");
    preview.innerHTML = "";

    const promises = uploadedFiles.map((file, index) => {
      return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          const div = document.createElement("div");
          div.className = "preview-item";
          div.innerHTML = `
                          <img src="${e.target.result}" alt="Preview">
                          <button type="button" class="remove-image" data-index="${index}">×</button>
                      `;

          div
            .querySelector(".remove-image")
            .addEventListener("click", function () {
              uploadedFiles.splice(index, 1);
              updateImagePreview();
            });

          resolve(div);
        };
        reader.readAsDataURL(file);
      });
    });

    Promise.all(promises).then((divs) => {
      console.log(divs);
      divs.forEach((div) => preview.appendChild(div));
    });
  }

  // Form submission handling
  document
    .getElementById("showSubmissionStep1")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      uploadedFiles.forEach((file) => {
        formData.append("images[]", file);
      });

      // Display review
      const review = generateReview(formData);
      document.getElementById("submissionReview").innerHTML = review;
      showStep(2);
    });

  function generateReview(formData) {
    let html = '<div class="review-content"><div class="review-fields">';

    // Get organizer value and handle it safely
    const organizerValue = formData.get("organizer");
    if (organizerValue) {
      const organizerSelect = document.getElementById("organizer");
      let organizerDisplay = organizerValue; // fallback to value if can't get name

      if (organizerSelect && organizerSelect.options) {
        const selectedOption =
          organizerSelect.options[organizerSelect.selectedIndex];
        if (selectedOption) {
          organizerDisplay = selectedOption.text;
        }
      }

      html += `<div class="review-item">
                <strong>ORGANIZER:</strong>
                <span data-name="${organizerDisplay}" data-id="${organizerValue}">${organizerDisplay}</span>
            </div>`;
    }

    // Get organizer value and handle it safely
    const bookingNameValue = formData.get("booking_name");
    if (bookingNameValue) {
      const bookingNameSelect = document.getElementById("booking_name");
      let bookingNameDisplay = bookingNameValue; // fallback to value if can't get name

      if (bookingNameSelect && bookingNameSelect.options) {
        const selectedOption =
          bookingNameSelect.options[bookingNameSelect.selectedIndex];
        if (selectedOption) {
          bookingNameDisplay = selectedOption.text;
        }
      }

      html += `<div class="review-item">
                <strong>ORGANIZER:</strong>
                <span data-name="${bookingNameDisplay}" data-id="${organizerValue}">${bookingNameDisplay}</span>
            </div>`;
    }

    // Get venue value and handle it safely
    const venueValue = formData.get("venue_name");
    if (venueValue) {
      const venueSelect = document.getElementById("venue_name");
      let venueDisplay = venueValue; // fallback to value if can't get name

      if (venueSelect && venueSelect.options) {
        const selectedOption = venueSelect.options[venueSelect.selectedIndex];
        if (selectedOption) {
          venueDisplay = selectedOption.text;
        }
      }

      html += `<div class="review-item">
                <strong>VENUE NAME:</strong>
                <span data-name="${venueDisplay}" data-id="${venueValue}">${venueDisplay}</span>
            </div>`;
    }

    // Only show New Organizer Name if it has a value
    const newOrganizerName = formData.get("new_organizer_name");
    if (newOrganizerName) {
      html += `<div class="review-item">
                <strong>NEW ORGANIZER NAME:</strong>
                <span>${newOrganizerName}</span>
            </div>`;
    }

    // Only show Booking Email if it has a value
    const bookingEmail = formData.get("booking_email");
    if (bookingEmail) {
      html += `<div class="review-item">
                <strong>BOOKING EMAIL:</strong>
                <span>${bookingEmail}</span>
            </div>`;
    }

    // Handle door time formatting
    const doorTime = formData.get("door_time");
    if (doorTime) {
      const formattedTime = new Date(`2000-01-01T${doorTime}`)
        .toLocaleString("en-US", {
          hour: "numeric",
          minute: "2-digit",
          hour12: true,
        })
        .toLowerCase();
      html += `<div class="review-item">
                <strong>DOOR TIME:</strong>
                <span data-time="${doorTime}" data-formatted-time="${formattedTime}">${formattedTime}</span>
            </div>`;
    }

    // Handle ticket price with dollar sign
    const ticketPrice = formData.get("ticket_price");
    if (ticketPrice) {
      html += `<div class="review-item">
                <strong>PRICE:</strong>
                <span>$${ticketPrice}</span>
            </div>`;
    }

    // Handle ticket link as clickable
    const showLink = formData.get("show_link");
    if (showLink) {
      html += `<div class="review-item">
                <strong>LINK:</strong>
                <span><a href="${showLink}" target="_blank" rel="noopener noreferrer">${showLink}</a></span>
            </div>`;
    }

    // Handle show link as clickable
    const showDate = formData.get("show_date");
    const timeZone = formData.get("time_zone");
    // const dateObj = new Date(showDate);

    // Create a date string with the time set to noon in the selected timezone
    // This prevents any date shifting due to timezone conversion
    const dateString = `${showDate}T12:00:00`;
    const formattedDate = new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
      timeZone: timeZone
    });

    if (showDate) {
      html += `<div class="review-item">
                <strong>SHOW DATE:</strong>
                <span>${formattedDate}</span>
            </div>`;
    }

    // Add other form fields
    for (let [key, value] of formData.entries()) {
      if (
        key !== "images[]" &&
        key !== "nonce" &&
        key !== "submitter_name" &&
        key !== "submitter_email" &&
        key !== "door_price" &&
        key !== "door_time" &&
        key !== "show_link" &&
        key !== "organizer" &&
        key !== "new_organizer_name" &&
        key !== "booking_email" &&
        key !== "booking_name" &&
        key !== "venue_name" &&
        key !== "ticket_price" &&
        key !== "show_date" &&
        key !== "time_zone" &&
        value !== ""
      ) {
        html += `<div class="review-item">
                    <strong>${key.replace(/_/g, " ").toUpperCase()}:</strong>
                    <span ${
                      key === "performers"
                        ? 'style="display: block; white-space: pre-wrap;"'
                        : ""
                    }>${value}</span>
                </div>`;
      }
    }

    // Add images
    html += '</div><div class="review-images">';
    uploadedFiles.forEach((file) => {
      const url = URL.createObjectURL(file);
      html += `<img src="${url}" alt="Upload preview">`;
    });
    html += "</div></div>";
    return html;
  }

  window.submitFinal = function () {
    const formData = new FormData(
      document.getElementById("showSubmissionStep1")
    );
    formData.append("action", "submit_show");
    formData.append("_ajax_nonce", showSubmissions.nonce);
    formData.append("nonce", showSubmissions.nonce);

    fetch(showSubmissions.ajaxurl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Show submitted successfully!");
          location.reload();
        } else {
          alert("Error submitting show: " + data.data);
        }
      })
      .catch(() => {
        alert("Error submitting show. Please try again.");
      });
  };

  window.showStep = function (step) {
    document
      .querySelectorAll(".form-step")
      .forEach((el) => (el.style.display = "none"));
    document.getElementById(`step${step}`).style.display = "block";
  };

  // Initialize Google Places Autocomplete
  function initializeAutocomplete() {
    const input = document.getElementById("venue_address_autocomplete");
    const autocomplete = new google.maps.places.Autocomplete(input, {
      types: ["address"],
      componentRestrictions: { country: "us" },
    });

    autocomplete.addListener("place_changed", function () {
      const place = autocomplete.getPlace();
      let street = "",
        city = "",
        state = "",
        zip = "";

      // Extract address components
      for (const component of place.address_components) {
        const type = component.types[0];

        switch (type) {
          case "street_number":
            street = component.long_name + " ";
            break;
          case "route":
            street += component.long_name;
            break;
          case "locality":
            city = component.long_name;
            break;
          case "administrative_area_level_1":
            state = component.short_name;
            break;
          case "postal_code":
            zip = component.long_name;
            break;
        }
      }

      // Populate hidden fields
      document.getElementById("venue_street").value = street;
      document.getElementById("venue_city").value = city;
      document.getElementById("venue_state").value = state;
      document.getElementById("venue_zip").value = zip;
    });
  }

  // Initialize autocomplete if Google Maps API is loaded
  if (typeof google !== "undefined" && google.maps && google.maps.places) {
    initializeAutocomplete();
  }
});
