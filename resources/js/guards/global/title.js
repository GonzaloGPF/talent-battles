import global from '@/config/global';
import { useTitle } from '@/stores/title';

/**
 * Title of the app bar. The title of the browser tab is set by guards/after/documentTitle.
 */
export default (to) => {
    const { setTitle } = useTitle();

    setTitle(to.meta.title || global.getAppName());
}
