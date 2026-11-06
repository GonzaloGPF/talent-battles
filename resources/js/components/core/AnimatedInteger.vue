<template>
  <span>{{ tweeningValue }}</span>
</template>
<script>
// import {TweenMax} from 'gsap';

export default {
    // props: ['value'],
    //
    // data() {
    //     return {
    //         tweeningValue: 0
    //     }
    // },
    //
    // watch: {
    //     value: function (newValue, oldValue) {
    //         this.tween(oldValue, newValue)
    //     }
    // },
    // mounted: function () {
    //     this.tween(0, this.value)
    // },
    // methods: {
    //     tween: function (startValue, endValue) {
    //         function animate() {
    //             if (TweenMax.update()) {
    //                 requestAnimationFrame(animate)
    //             }
    //         }
    //
    //         new TweenMax.Tween({tweeningValue: startValue})
    //             .to({tweeningValue: endValue}, 500)
    //             .onUpdate(function (object) {
    //                 this.tweeningValue = object.tweeningValue.toFixed(0)
    //             })
    //             .start();
    //
    //         animate()
    //     }
    // }
}
</script>