export default {
    beforeMount: (el, binding) => {
        el.flashMessage = () => {
            const span = buildSpan(binding.value);

            el.appendChild(span);

            setTimeout(() => span.remove(), 1000);
        }

        el.addEventListener('click', el.flashMessage);
    },
    unmounted: el => {
        el.removeEventListener('click', el.flashMessage);
    },
}

function buildSpan(text) {
    const span = document.createElement('span');
    span.classList.add('text-caption', 'inline-block', 'px-2', 'py-1', 'rounded-lg', 'bg-grey-darken-4', 'whitespace-nowrap', 'animate__animated', 'animate__fadeOutUp');
    span.style.position = 'absolute';
    span.style.right = '0';
    span.innerText = text;

    return span;
}
