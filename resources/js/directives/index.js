import textSplit from '@/directives/textSplit';
import widthPercentage from '@/directives/widthPercentage';
import clipboard from '@/directives/clipboard';

export default app => {
    app.directive(textSplit);
    app.directive(widthPercentage);
    app.directive(clipboard);
}
