let map;
let geocoder;

function initMap() {
  const defaultLoc = { lat: -26.2041, lng: 28.0473 }; // Johannesburg
  map = new google.maps.Map(document.getElementById("map"), {
    center: defaultLoc,
    zoom: 12,
  });

  geocoder = new google.maps.Geocoder();

  // Button to search by address
  document.getElementById("searchBtn").addEventListener("click", () => {
    const address = document.getElementById("location").value;
    if (address.trim() !== "") {
      geocodeAddress(address);
    } else {
      alert("Please enter a location.");
    }
  });

  // Button to use geolocation
  document.getElementById("geoBtn").addEventListener("click", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition, () => {
        alert("Could not get your location.");
      });
    } else {
      alert("Geolocation not supported by this browser.");
    }
  });

  // Fetch and show all reports from your backend
  fetch("get-reports.php")
    .then(res => res.json())
    .then(data => {
      data.forEach(report => {
        geocoder.geocode({ address: report.Location }, (results, status) => {
          if (status === "OK" && results[0]) {
            const marker = new google.maps.Marker({
              map: map,
              position: results[0].geometry.location,
              title: report.Description,
            });

            const infoWindow = new google.maps.InfoWindow({
              content: `
                <div>
                  <strong>Description:</strong> ${report.Description}<br>
                  <strong>Status:</strong> ${report.Status}
                </div>
              `
            });

            marker.addListener("click", () => {
              infoWindow.open(map, marker);
            });
          } else {
            console.error("Geocode failed for:", report.Location, status);
          }
        });
      });
    })
    .catch(err => console.error("Error loading reports:", err));
}  // <--- end of initMap

// Helper functions outside initMap

function geocodeAddress(address) {
  geocoder.geocode({ address: address }, (results, status) => {
    if (status === "OK") {
      map.setCenter(results[0].geometry.location);
      new google.maps.Marker({
        map: map,
        position: results[0].geometry.location,
      });
    } else {
      alert("Geocode failed: " + status);
    }
  });
}

function showPosition(position) {
  const userLoc = {
    lat: position.coords.latitude,
    lng: position.coords.longitude,
  };
  map.setCenter(userLoc);
  map.setZoom(14);
  new google.maps.Marker({
    map: map,
    position: userLoc,
    title: "You are here!",
  });
}
