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
  document.getElementById("showSubmissionStep1").addEventListener("submit", function (e) {
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
    let html = '<div class="review-content">';
    for (let [key, value] of formData.entries()) {
      if (
        key !== "images[]" &&
        key !== "nonce" &&
        key !== "submitter_name" &&
        key !== "submitter_email" &&
        key !== "door_price" &&
        key !== "music_start_time" &&
        key !== "ticket_link"
      ) {
        html += `<div class="review-item">
                    <strong>${key.replace("_", " ").toUpperCase()}:</strong>
                    <span>${value}</span>
                </div>`;
      }
    }
    html += '<div class="review-images">';
    uploadedFiles.forEach((file) => {
        console.log(uploadedFiles);
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
