import { onBeforeUnmount, ref } from 'vue';

export default (initialValue, step = 1000) => {
    const timer = ref(initialValue);
    const started = ref(false);
    const paused = ref(false);
    const finish = ref(false);
    let interval = null;

    function start() {
        started.value = true;
        interval = setInterval(() => {
            if (paused.value || finish.value) return;

            if (timer.value <= 0) {
                finish.value = true;
            } else {
                timer.value -= 1;
            }
        }, step);
    }

    function togglePause() {
        if (! started.value) {
            start();
        } else {
            paused.value = !paused.value;
        }
    }

    function reset(finished = false) {
        timer.value = initialValue;
        started.value = false;
        finish.value = finished;
        clear();
    }

    function clear() {
        clearInterval(interval);
    }

    onBeforeUnmount(clear);

    return {
        start,
        timer,
        togglePause,
        reset,
        finish,
    }
}
