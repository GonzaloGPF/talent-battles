export default {
    beforeMount: (el, binding) => {
        el.getWidthPercent = event => {
            let offset = el.getBoundingClientRect();
            let x = event.pageX - offset.left;
            let percent = x / parseFloat(el.offsetWidth);

            let expression = binding.value;

            if (typeof expression === 'function') {
                expression(percent * 100); // execute given expression
            }
        }
        el.addEventListener('click', el.getWidthPercent)
    },
    unmounted: el => {
        el.removeEventListener('click', el.getWidthPercent)
    },
}
