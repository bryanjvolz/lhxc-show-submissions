async function initGooglePlacesAutocomplete() {
  const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");

  const autocompleteElement = document.querySelector('gmp-autocomplete');
  if (!autocompleteElement) return;

  const input = autocompleteElement.querySelector('input');

  autocompleteElement.addEventListener("gmp-placechange", (event) => {
    const place = event.detail.place;
    let street = "",
      city = "",
      state = "",
      zip = "";

    if (!place.addressComponents) return;

    for (const component of place.addressComponents) {
      const type = component.types[0];
      switch (type) {
        case "street_number":
          street = component.longText + " ";
          break;
        case "route":
          street += component.longText;
          break;
        case "locality":
          city = component.longText;
          break;
        case "administrative_area_level_1":
          state = component.shortText;
          break;
        case "postal_code":
          zip = component.longText;
          break;
      }
    }

    document.getElementById("venue_street").value = street;
    document.getElementById("venue_city").value = city;
    document.getElementById("venue_state").value = state;
    document.getElementById("venue_zip").value = zip;
    document.getElementById("venue_address").value = place.formattedAddress;

    if (input) {
      input.value = place.formattedAddress;
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  if (typeof google !== "undefined" && google.maps && google.maps.places) {
    initGooglePlacesAutocomplete();
  }

  // ... (rest of your existing DOMContentLoaded code)
});
