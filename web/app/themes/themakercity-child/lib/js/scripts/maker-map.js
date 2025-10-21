document.addEventListener("DOMContentLoaded", function () {
  const { endpoint, category, mapId } = makerMapData;
  const mapElement = document.getElementById(mapId);
  if (!mapElement) return;

  // Map config
  const MAX_SINGLE_MARKER_ZOOM = 14;

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

      // ✅ Cap zoom if only one marker is visible
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
   * Builds the filter UI dynamically from maker-filters as checkboxes.
   * @param {Array} filters
   */
  function buildFilterUI(filters) {
    // Find or create container above map
    let wrapper = mapElement.closest(".maker-map-wrapper");
    if (!wrapper) {
      wrapper = document.createElement("div");
      wrapper.classList.add("maker-map-wrapper");
      mapElement.parentNode.insertBefore(wrapper, mapElement);
      wrapper.appendChild(mapElement);
    }

    // Create filters container
    const filterContainer = document.createElement("div");
    filterContainer.classList.add("maker-map-filters");
    filterContainer.innerHTML = `<strong>Filter by:</strong>`;

    // "Show All" checkbox
    const allLabel = document.createElement("label");
    allLabel.classList.add("maker-filter-item");
    const allCheckbox = document.createElement("input");
    allCheckbox.type = "checkbox";
    allCheckbox.value = "all";
    allCheckbox.checked = true;
    allLabel.appendChild(allCheckbox);
    allLabel.append(" All Maker Spaces");
    filterContainer.appendChild(allLabel);

    // Individual filter checkboxes
    filters.forEach((filter) => {
      const label = document.createElement("label");
      label.classList.add("maker-filter-item");
      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.value = filter.slug;
      checkbox.checked = false;
      label.appendChild(checkbox);
      label.append(` ${filter.name}`);
      filterContainer.appendChild(label);
    });

    wrapper.insertBefore(filterContainer, mapElement);

    // Checkbox change handling
    filterContainer.addEventListener("change", (e) => {
      const checkboxes = filterContainer.querySelectorAll('input[type="checkbox"]');
      const selected = Array.from(checkboxes)
        .filter((cb) => cb.checked && cb.value !== "all")
        .map((cb) => cb.value);

      // If "All" is selected or no filters are checked, show all makers
      if (e.target.value === "all" && e.target.checked) {
        checkboxes.forEach((cb) => {
          if (cb.value !== "all") cb.checked = false;
        });
        renderMarkers(allMakers);
        return;
      }

      // If any individual filters are selected, uncheck "All"
      if (selected.length > 0) {
        allCheckbox.checked = false;
        const filtered = allMakers.filter((maker) =>
          selected.some((slug) => maker.categories.includes(slug))
        );
        renderMarkers(filtered);
      } else {
        // No filters selected, show all again
        allCheckbox.checked = true;
        renderMarkers(allMakers);
      }
    });
  }

  /**
   * Load makers + filters from REST API.
   */
  function loadData(categorySlug) {
    fetch(`${endpoint}?maker-category=${encodeURIComponent(categorySlug)}`)
      .then((res) => res.json())
      .then((data) => {
        if (!data || !data.makers) {
          console.warn("No makers found or invalid response:", data);
          return;
        }

        // Store makers globally for filtering
        allMakers = data.makers;

        // Build filter UI
        if (data["maker-filters"] && data["maker-filters"].length > 0) {
          buildFilterUI(data["maker-filters"]);
        }

        // Initial render
        renderMarkers(allMakers);
      })
      .catch((err) => console.error("Error loading maker locations:", err));
  }

  // Initial load
  loadData(category);
});
