import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';

/**
 * The build of the frontend this page was loaded with, written by the server in the page.
 * There is none without a build or while Vite is running hot.
 */
const loadedVersion = window.app_version || null;

let notified = false;

const AppVersion = {
    /**
     * The server says which build it has in every API response and in the broadcast of a deployment.
     * A different one means a deployment has changed the frontend since this page was loaded.
     *
     * @param {string|null|undefined} serverVersion
     */
    check(serverVersion) {
        if (loadedVersion && serverVersion && serverVersion !== loadedVersion) {
            AppVersion.notify();
        }
    },

    /**
     * The user is told once, whichever of them notices it first.
     */
    notify() {
        if (notified) return;

        notified = true;

        EventBus.emit(EventTypes.backend.NewDeploy);
    },
};

export default AppVersion;
