<template>
  <span
    class="whitespace-nowrap"
    v-text="Formatter.date(modelValue, humanize, formatMap[format])"
  />
</template>
<script setup>
import Formatter from '@/objects/Formatter';

/**
 * @type {{hour: string, short: string, long: string}}
 */
const formatMap = {
    short: 'dd-MM-yyyy',
    long: 'd MMMM yyyy',
    longer: 'dd MMMM yyyy, HH:mm',
    datetime: 'dd-MM-yyyy HH:mm:ss',
    hour: 'H:mm',
}
defineProps({
    modelValue: {
        type: [String, Number],
        // required: true,
        default: null,
    },
    humanize: {
        type: Boolean,
        default: false
    },
    format: {
        type: String,
        default: 'short'
    }
});
/*
mounted() {
    if (!this.humanize) return;

    this.interval = setInterval(() => {
        this.formattedDate = this.getFormattedDate()
    }, 60 * 1000) // each minute
},
destroyed() {
    clearInterval(this.interval);
},
*/
</script>
