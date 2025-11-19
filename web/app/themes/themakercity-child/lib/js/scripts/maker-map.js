document.addEventListener("DOMContentLoaded", function () {
  const { endpoint, mapId } = makerMapData;
  const mapElement = document.getElementById(mapId);
  if (!mapElement) return;

  // Map config
  const MAX_SINGLE_MARKER_ZOOM = 14;
  const FILTER_MODE = "AND"; // "AND" or "OR"

  const mapOptions = {
    zoom: 12,
    center: { lat: 35.9606, lng: -83.9207 },
    zoomControl: true,
    mapTypeControl: true,
    streetViewControl: true,
    fullscreenControl: true,
    gestureHandling: "auto",
  };

  const map = new google.maps.Map(mapElement, mapOptions);
  let clusterer = null;
  let allMakers = []; // store all makers for client-side filtering
  let markers = [];

  /**
   * Renders markers on the map based on filtered data.
   * @param {Array} makers Array of maker objects to render.
   */
  function renderMarkers(makers) {
    const bounds = new google.maps.LatLngBounds();

    // Clear previous markers
    if (clusterer) clusterer.clearMarkers();
    markers.forEach((m) => m.setMap(null));
    markers = [];

    makers.forEach((maker) => {
      const position = { lat: maker.lat, lng: maker.lng };
      const marker = new google.maps.Marker({
        position,
        title: maker.title,
      });

      const infowindow = new google.maps.InfoWindow({
        content: `
          <div class="maker-infowindow">
            <h3><a href="${maker.link}" target="_blank">${maker.title}</a></h3>
            <p>${maker.address}</p>
          </div>
        `,
      });

      marker.addListener("click", () => infowindow.open(map, marker));
      markers.push(marker);
      bounds.extend(position);
    });

    if (makers.length > 0) {
      map.fitBounds(bounds);

      // Cap zoom on single marker
      if (makers.length === 1) {
        google.maps.event.addListenerOnce(map, "bounds_changed", function () {
          if (map.getZoom() > MAX_SINGLE_MARKER_ZOOM) {
            map.setZoom(MAX_SINGLE_MARKER_ZOOM);
          }
        });
      }

      clusterer = new markerClusterer.MarkerClusterer({ map, markers });
    }
  }

  /**
   * Builds the filter UI using maker-space-types.
   * @param {Array} types List of taxonomy term objects
   */
  function buildFilterUI(types) {
    let wrapper = mapElement.closest(".maker-map-wrapper");
    if (!wrapper) {
      wrapper = document.createElement("div");
      wrapper.classList.add("maker-map-wrapper");
      mapElement.parentNode.insertBefore(wrapper, mapElement);
      wrapper.appendChild(mapElement);
    }

    const filterContainer = document.createElement("div");
    filterContainer.classList.add("maker-map-filters");
    filterContainer.innerHTML = `<strong>Filter by Type:</strong>`;

    // Show All option
    const allLabel = document.createElement("label");
    allLabel.classList.add("maker-filter-item");

    const allCheckbox = document.createElement("input");
    allCheckbox.type = "checkbox";
    allCheckbox.value = "all";
    allCheckbox.checked = true;

    allLabel.appendChild(allCheckbox);
    allLabel.append(" All Maker Spaces");
    filterContainer.appendChild(allLabel);

    // Individual type checkboxes
    types.forEach((type) => {
      const label = document.createElement("label");
      label.classList.add("maker-filter-item");

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.value = type.slug;
      checkbox.checked = false;

      label.appendChild(checkbox);

      // Show "Name (count)" using the count from the REST response
      const labelText =
        typeof type.count === "number"
          ? `${type.name} (${type.count})`
          : type.name;

      label.append(` ${labelText}`);
      filterContainer.appendChild(label);
    });

    wrapper.insertBefore(filterContainer, mapElement);

    /**
     * Filter change handler
     */
    filterContainer.addEventListener("change", (e) => {
      const checkboxes = filterContainer.querySelectorAll('input[type="checkbox"]');
      const selected = Array.from(checkboxes)
        .filter((cb) => cb.checked && cb.value !== "all")
        .map((cb) => cb.value);

      // Show All checked
      if (e.target.value === "all" && e.target.checked) {
        checkboxes.forEach((cb) => {
          if (cb.value !== "all") cb.checked = false;
        });
        renderMarkers(allMakers);
        return;
      }

      // If specific filters are selected
      if (selected.length > 0) {
        allCheckbox.checked = false;

        const has = (maker, slug) =>
          Array.isArray(maker.space_types) &&
          maker.space_types.includes(slug);

        const filtered = allMakers.filter((maker) =>
          FILTER_MODE === "AND"
            ? selected.every((slug) => has(maker, slug))
            : selected.some((slug) => has(maker, slug))
        );

        renderMarkers(filtered);
      } else {
        // No filters → revert to all
        allCheckbox.checked = true;
        renderMarkers(allMakers);
      }
    });
  }

  /**
   * Loads makers + maker-space-type terms.
   */
  function loadData() {
    fetch(endpoint)
      .then((res) => res.json())
      .then((data) => {
        if (!data || !data.makers) {
          console.warn("Invalid response:", data);
          return;
        }

        allMakers = data.makers;

        // Build filter UI using maker-space-types
        if (data["maker-space-types"] && data["maker-space-types"].length > 0) {
          buildFilterUI(data["maker-space-types"]);
        }

        // Initial map render
        renderMarkers(allMakers);
      })
      .catch((err) => console.error("Error loading maker locations:", err));
  }

  // Initial load
  loadData();
});
