import * as d3 from 'd3';
import WaveformData from 'waveform-data';

export default {
    draw(json, element, color = '#0D47A1') {
        if (!element) return;

        if (!Array.isArray(json?.data)) return;

        // Clean up old waveform, useful when resizing window
        d3.select(element)
            .selectAll('svg')
            .remove();

        let waveform = WaveformData.create(json);
        let channel = waveform.channel(0);
        let width = element.offsetWidth;
        let height = element.offsetHeight;

        const offsetX = height / 2;

        const min = channel.min_array();
        const max = channel.max_array();

        const x = d3.scaleLinear()
            .domain([0, waveform.length])
            .rangeRound([0, width]);

        const y = d3.scaleLinear()
            .domain([d3.min(min), d3.max(max)])
            .rangeRound([offsetX, -offsetX]);

        const area = d3.area()
            .x((d, i) => x(i))
            .y0((d, i) => y(min[i]))
            .y1((d) => y(d));

        if (d3.select(element).node()) {
            d3.select(element)
                .append('svg')
                .style('width', width + 'px')
                .style('height', height + 'px')
                .style('fill', color)
                .attr('class', 'waveform')
                .datum(max)
                .append('path')
                .attr('transform', () => `translate(0, ${offsetX})`)
                .attr('d', area);
        }
    },

    // chunkAverage(data, samples) {
    //     let blockSize = Math.floor(data.length / samples); // the number of samples in each subdivision
    //     if (!blockSize) {
    //         blockSize = 1;
    //     }
    //     let filteredData = [];
    //     for (let i = 0; i < samples; i++) {
    //         let blockStart = blockSize * i; // the location of the first sample in the block
    //         let sum = 0;
    //         for (let j = 0; j < blockSize; j++) {
    //             sum = sum + Math.abs(data[blockStart + j]) // find the sum of all the samples in the block
    //         }
    //         filteredData.push(sum / blockSize); // divide the sum by the block size to get the average
    //     }
    //     return filteredData;
    // },
}
