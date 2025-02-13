<!-- resources/js/Pages/GpsTrack/Index.vue -->
<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Device List -->
        <div class="grid grid-cols-3 gap-2 p-4 bg-gray-800">
            <Link
                v-for="device in devices"
                :key="device.imei"
                :href="route('gps-tracks.show', { imei: device.imei })"
                class="p-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
            >
                {{ device.imei }}
            </Link>
        </div>

        <!-- Map Container -->
        <div class="py-12 px-4">
            <div class="max-w-full mx-auto">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="h-[600px]" ref="mapContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    devices: {
        type: Array,
        required: true,
    },
    originalTracks: {
        type: Array,
        required: true,
    },
    smoothedTracks: {
        type: Array,
        required: true,
    },
});

const mapContainer = ref(null);
let map = null;

onMounted(() => {
    initializeMap();
    addTileLayer();
    drawPaths();
    addLegend();
});

const initializeMap = () => {
    map = L.map(mapContainer.value).setView(
        [props.originalTracks[0].latitude, props.originalTracks[0].longitude],
        15
    );
};

const addTileLayer = () => {
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(map);
};

const drawPaths = () => {
    const originalCoords = props.originalTracks.map((point) => [
        point.latitude,
        point.longitude,
    ]);
    const smoothedCoords = props.smoothedTracks.map((point) => [
        point.latitude,
        point.longitude,
    ]);

    const originalPath = L.polyline(originalCoords, {
        color: "red",
        weight: 3,
        opacity: 0.7,
    }).addTo(map);

    const smoothedPath = L.polyline(smoothedCoords, {
        color: "blue",
        weight: 3,
        opacity: 0.7,
    }).addTo(map);

    map.fitBounds(originalPath.getBounds());
};

const addLegend = () => {
    const legend = L.control({ position: "bottomright" });

    legend.onAdd = () => {
        const div = L.DomUtil.create("div", "bg-white p-4 rounded shadow");
        div.innerHTML = `
            <div class="mb-2">
                <span class="inline-block w-5 h-0.5 bg-red-500 mr-2"></span>
                Original Path
            </div>
            <div>
                <span class="inline-block w-5 h-0.5 bg-blue-500 mr-2"></span>
                Smoothed Path
            </div>
        `;
        return div;
    };

    legend.addTo(map);
};
</script>

<style>
.leaflet-container {
    z-index: 1;
}
</style>
