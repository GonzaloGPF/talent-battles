export default {
    beforeMount: (el, binding) => {
        /**
         * Where the click fell, as a percentage of the width of the element. The click and the element are both
         * measured from the viewport, so the result does not depend on how far the page is scrolled.
         */
        el.getWidthPercent = event => {
            let offset = el.getBoundingClientRect();
            let x = event.clientX - offset.left;
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
