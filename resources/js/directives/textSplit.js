import { nextTick } from 'vue';

export default {
    beforeMount: (el, binding) => {
        const text = el.innerHTML;
        const className = binding.value;

        if (typeof text !== 'string') return;

        el.originalText = text;
        el.innerHTML = null;

        text.split('')
            .map(char => toSpan(char, className))
            .forEach(span => el.appendChild(span));

        nextTick(() => el.dispatchEvent(new Event('split')));
    },
    unmounted: el => {
        el.innerHTML = el.originalText;
    },
}

function toSpan(char, className) {
    const node = document.createElement('span');

    if (className) {
        className.split(' ')
            .forEach(classString => node.classList.add(classString));
    }

    if (char === ' ') {
        node.classList.add('mx-3');
    }

    const content = document.createTextNode(char);

    node.appendChild(content);

    return node;
}
