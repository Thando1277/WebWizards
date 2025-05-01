let map;
let geocoder;

function initMap() {
  const defaultLoc = { lat: -33.9249, lng: 18.4241 };
  map = new google.maps.Map(document.getElementById("map"), {
    center: defaultLoc,
    zoom: 10,
  });

  geocoder = new google.maps.Geocoder();

  document.getElementById("searchBtn").addEventListener("click", () => {
    const address = document.getElementById("location").value;
    if (address.trim() !== "") {
      geocodeAddress(address);
    } else {
      alert("Please enter a location.");
    }
  });

  document.getElementById("geoBtn").addEventListener("click", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition, () => {
        alert("Could not get your location.");
      });
    } else {
      alert("Geolocation not supported by this browser.");
    }
  });
}

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
