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
    </div>
</template>
<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    devices: {
        type: Array,
        required: true,
    },
});
</script>
