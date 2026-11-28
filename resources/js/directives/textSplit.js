import { nextTick } from 'vue';

/**
 * Wraps each character of the text in a span, so they can be animated one by one. Screen readers would read the
 * spans as separate letters, so they are hidden from them and the element is labelled with the whole text.
 */
export default {
    beforeMount: (el, binding) => {
        const text = el.textContent;
        const className = binding.value;

        if (! text) return;

        el.originalText = text;
        el.setAttribute('aria-label', text);
        el.textContent = '';

        [...text]
            .map(char => toSpan(char, className))
            .forEach(span => el.appendChild(span));

        nextTick(() => el.dispatchEvent(new Event('split')));
    },
    unmounted: el => {
        el.textContent = el.originalText;
    },
}

function toSpan(char, className) {
    const node = document.createElement('span');

    node.setAttribute('aria-hidden', 'true');

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
