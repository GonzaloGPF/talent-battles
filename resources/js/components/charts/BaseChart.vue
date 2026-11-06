<template>
  <canvas
    v-if="hasData"
    ref="canvasElement"
    width="400"
    height="400"
  />
</template>
<script setup>
import { Chart } from 'chart.js'
import { computed, nextTick, onMounted, ref, toRefs, watch } from 'vue';
import Formatter from '@/objects/Formatter';

const props = defineProps({
    type: {
        type: String,
        default: 'bar',
    },
    chartData: {
        type: Object,
        default: null
    },
    title: {
        type: String,
        default: null,
    },
    subTitle: {
        type: String,
        default: null,
    },
    isMoney: {
        type: Boolean,
        default: false
    }
});
const hasData = computed(() => {
    return props.chartData?.datasets?.find(dataset => dataset.data.length && dataset.data.reduce((value, result) => value + result, 0) > 0)
});
const { type, chartData, title } = toRefs(props);
const canvasElement = ref(null);
const chartInstance = ref(null);
const scales = computed(() => {
    if (props.type === 'pie') return;

    return {
        y: {
            grid: {
                color: 'rgba(255, 255, 255, 0.05)',
                borderColor: '#FFFFFF',
                tickColor: '#FFF',
            },
            ticks: {
                color: '#FFF',
                callback: value => {
                    if (props.isMoney) {
                        return Formatter.money(value)
                    }
                    return value;
                }
            }
        },
        x: {
            grid: {
                color: 'rgba(255, 255, 255, 0.05)',
                borderColor: '#FFFFFF',
                tickColor: '#FFF',
            },
            ticks: {
                color: '#FFF',
            }
        },
        y1: {
            display: true,
            position: 'right',

            // grid line settings
            grid: {
                drawOnChartArea: false, // only want the grid lines for one axis to show up
            },
        }
    }
});
const options = computed(() => ({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: 'rgb(255,255,255)'
                }
            },
            title: {
                display: true,
                text: props.title,
                color: '#EEFFFF',
                font: {
                    size: 16,
                },
            },
            subtitle: {
                display: props.subTitle,
                text: props.subTitle,
                color: '#EEFFFF',
                font: {
                    size: 14,
                    family: 'tahoma',
                    weight: 'normal',
                },
                padding: {
                    bottom: 10
                },
            },
        },
        scales: scales.value
    })
);

watch(type, drawChart);
watch(chartData, drawChart);
watch(title, drawChart);

onMounted(() => drawChart());

function drawChart () {
    nextTick(() => {
        if (! canvasElement.value) return;

        if (chartInstance.value) {
            chartInstance.value.destroy();
        }

        chartInstance.value = new Chart(canvasElement.value.getContext('2d'), {
            type: props.type,
            data: props.chartData,
            options: options.value
        });
    })
}
</script>
