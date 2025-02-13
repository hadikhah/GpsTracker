<!-- resources/js/Pages/GpsTrack/Index.vue -->
<template>
    <div class="min-h-screen bg-gray-100">
        <div class="grid grid-cols-3">
            <div
                class="w-full p-2 bg-gray-700 min-h-2 text-white"
                v-for="device in devices"
            >
                <Link :href="route('gps-tracks.index', { imei: device.imei })">
                    {{ device.imei }}
                </Link>
            </div>
        </div>
        <div class="py-12">
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
    // Initialize map
    map = L.map(mapContainer.value).setView(
        [props.originalTracks[0].latitude, props.originalTracks[0].longitude],
        15
    );

    // Add tile layer
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(map);

    // Draw original path
    const originalCoords = props.originalTracks.map((point) => [
        point.latitude,
        point.longitude,
    ]);
    const originalPath = L.polyline(originalCoords, {
        color: "red",
        weight: 3,
        opacity: 0.7,
    }).addTo(map);

    // Draw smoothed path
    const smoothedCoords = props.smoothedTracks.map((point) => [
        point.latitude,
        point.longitude,
    ]);
    const smoothedPath = L.polyline(smoothedCoords, {
        color: "blue",
        weight: 3,
        opacity: 0.7,
    }).addTo(map);

    // Add legend
    const legend = L.control({ position: "bottomright" });
    legend.onAdd = function () {
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

    // Fit bounds to show all points
    map.fitBounds(originalPath.getBounds());
});
</script>

<style>
.leaflet-container {
    z-index: 1;
}
</style>
