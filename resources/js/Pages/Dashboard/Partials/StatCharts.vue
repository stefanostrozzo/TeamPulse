<script setup>
import { defineAsyncComponent } from 'vue';

const VueApexCharts = defineAsyncComponent(() => import('vue3-apexcharts'));

const props = defineProps({
    myTasksStats: Object,
    tasksByPriority: Object
});

/**
 * Shared configuration to ensure charts blend seamlessly with the dark UI.
 * Setting background to 'transparent' removes the default gray canvas.
 */
const transparentChart = { background: 'transparent' };

/**
 * Configuration for the Task Status Donut Chart.
 * Labels are localized to Italian.
 */
const pieOptions = {
    chart: {
        ...transparentChart,
        type: 'donut',
        fontFamily: 'inherit'
    },
    theme: { mode: 'dark' },
    // Localized labels for the pie chart segments
    labels: ['Completati', 'In Corso'],
    colors: ['#10b981', '#07b4f6'],
    stroke: { show: false },
    legend: {
        position: 'bottom',
        labels: {
            colors: '#9ca3af' // gray-400
        }
    },
    dataLabels: { enabled: false },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Totali', // Center label for the donut
                        color: '#ffffff'
                    }
                }
            }
        }
    }
};

/**
 * Configuration for the Task Priority Bar Chart.
 * Categories are localized to Italian.
 */
const barOptions = {
    chart: {
        ...transparentChart,
        type: 'bar',
        toolbar: { show: false }
    },
    theme: { mode: 'dark' },
    plotOptions: {
        bar: {
            borderRadius: 8,
            distributed: true,
            columnWidth: '50%'
        }
    },
    colors: ['#ef4444', '#f59e0b', '#3b82f6'],
    xaxis: {
        // Localized categories for the X-axis
        categories: ['Alta', 'Media', 'Bassa'],
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    grid: {
        borderColor: '#374151',
        strokeDashArray: 4
    },
    legend: { show: false }, // Hidden as colors are mapped to x-axis labels
    tooltip: {
        y: {
            title: {
                formatter: () => 'Attività:' // Localized tooltip title
            }
        }
    }
};

/**
 * Series data mapping for the Bar Chart.
 */
const barSeries = [{
    name: 'Attività',
    data: props.tasksByPriority.series
}];
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-white mb-6">Stato delle mie attività</h3>
            <VueApexCharts width="100%" height="300" :options="pieOptions" :series="myTasksStats.series" />
        </div>

        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-white mb-6">Attività per priorità</h3>
            <VueApexCharts width="100%" height="300" :options="barOptions" :series="barSeries" />
        </div>
    </div>
</template>
