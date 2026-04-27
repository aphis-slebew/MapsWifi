
const map = L.map('map', {
    zoomControl: false 
}).setView([-7.6453, 112.9075], 13);

L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);


L.control.zoom({ position: 'topright' }).addTo(map);


let isAddingMode = false;

const fabAdd = document.getElementById('fabAdd');
const bottomSheet = document.getElementById('bottomSheet');
const sheetOverlay = document.getElementById('sheetOverlay');
const btnCloseSheet = document.getElementById('btnCloseSheet');
const inputLat = document.getElementById('inputLat');
const inputLng = document.getElementById('inputLng');

fabAdd.addEventListener('click', () => {
    isAddingMode = true;
    document.getElementById('map').style.cursor = 'crosshair';
   
    alert("Mode Tambah: Silahkan ketuk lokasi rumah pelanggan di peta.");
});

function openBottomSheet(lat, lng) {
    inputLat.value = lat;
    inputLng.value = lng;
    bottomSheet.classList.add('active');
    sheetOverlay.classList.add('active');
}

function closeBottomSheet() {
    bottomSheet.classList.remove('active');
    sheetOverlay.classList.remove('active');
    isAddingMode = false;
    document.getElementById('map').style.cursor = '';
    document.getElementById('formPelanggan').reset();
}

btnCloseSheet.addEventListener('click', closeBottomSheet);
sheetOverlay.addEventListener('click', closeBottomSheet);

// Handle klik map
map.on('click', function(e) {
    if (isAddingMode) {
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);
        openBottomSheet(lat, lng);
    }
});

const dummyData = [
    { nama: "Budi Santoso", hp: "08123456789", status: "normal", lat: -7.6453, lng: 112.9075, foto: "https://via.placeholder.com/300x150" },
    { nama: "Rumah Agus", hp: "08987654321", status: "gangguan", lat: -7.6500, lng: 112.9100, foto: "https://via.placeholder.com/300x150" }
];

dummyData.forEach(p => {
    
    let markerColor = p.status === 'normal' ? 'green' : (p.status === 'gangguan' ? 'red' : 'orange');
    let badgeClass = p.status === 'normal' ? 'normal' : (p.status === 'gangguan' ? 'gangguan' : 'pemasangan');
    
    const customIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color:${markerColor}; width:16px; height:16px; border-radius:50%; border:2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>`,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    const waLink = `https://wa.me/62${p.hp.substring(1)}?text=Halo%20Bapak/Ibu%20${p.nama},%20ini%20dari%20Teknisi%20WiFi.%20Lokasi:%20https://maps.google.com/?q=${p.lat},${p.lng}`;

    const popupHtml = `
        <div class="custom-popup">
            <img src="${p.foto}" alt="Foto Rumah">
            <div class="info">
                <h4>${p.nama}</h4>
                <p>${p.hp}</p>
                <span class="badge ${badgeClass}">${p.status.replace('_', ' ').toUpperCase()}</span>
                <a href="${waLink}" target="_blank" class="btn-wa">
                    <i class='bx bxl-whatsapp'></i> Hubungi via WA
                </a>
            </div>
        </div>
    `;

    L.marker([p.lat, p.lng], {icon: customIcon})
     .addTo(map)
     .bindPopup(popupHtml);
});


document.getElementById('formPelanggan').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
      
    alert('Simulasi: Data pelanggan disimpan ke database!');
    closeBottomSheet();
});