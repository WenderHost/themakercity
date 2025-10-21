document.addEventListener("DOMContentLoaded", function () {
  const { endpoint, category, mapId } = makerMapData;
  const mapElement = document.getElementById(mapId);
  if (!mapElement) return;

  // Map options
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

  function loadMarkers(categorySlug) {
    fetch(`${endpoint}?maker-category=${encodeURIComponent(categorySlug)}`)
      .then((res) => res.json())
      .then((data) => {
        const bounds = new google.maps.LatLngBounds();
        const markers = [];

        data.forEach((maker) => {
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

        if (clusterer) {
          clusterer.clearMarkers();
        }

        if (markers.length > 0) {
          map.fitBounds(bounds);
          // ✅ Use global MarkerClusterer from the enqueued script
          clusterer = new markerClusterer.MarkerClusterer({ map, markers });
        }
      })
      .catch((err) => console.error("Error loading maker locations:", err));
  }

  // Initial load
  loadMarkers(category);

  // Watch for category filter changes
  const filter = document.querySelector(".maker-map-filter");
  if (filter) {
    filter.addEventListener("change", (e) => {
      loadMarkers(e.target.value);
    });
  }
});
