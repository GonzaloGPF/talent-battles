import LaravelEcho from '@/vendor/laravel-echo';
import global from '@/config/global';
import EventBus from '@/events/EventBus';
import eventTypes from '@/events/EventTypes';
import Http from '@/objects/Http';
import Utils from '@/objects/Utils';
import CookiesHelper from '@/objects/Cookies';

let WebSocket = null;
let options = null;
let connectedUserId = null;
let channelName = null;

const Echo = {
    /**
     *
     * @returns {Echo|null}
     */
    getInstance() {
        if (WebSocket) return WebSocket;

        options = Echo.parseOptions(global.env);

        if (! options) return null;

        WebSocket = new LaravelEcho(options);

        return WebSocket;
    },

    /**
     * @returns {Promise<string>|void}
     */
    connect() {
        const webSockets = Echo.getInstance();

        if (! webSockets) return;

        webSockets
            .channel('deployments')
            .listen('NewDeploy', (event) => EventBus.emit(eventTypes.backend.NewDeploy, event));

        return Echo.getSocketId(webSockets);
    },

    /**
     * @param userId
     */
    connectToPrivate(userId) {
        const webSockets = Echo.getInstance();

        if (! webSockets || ! userId || connectedUserId === userId) {
            return;
        }

        channelName = `App.Models.User.${ userId }`;

        webSockets
            .private(channelName)
            .notification(notification => EventBus.emit(eventTypes.NEW_NOTIFICATION, notification))
            .listen('.UserUpdated', event => EventBus.emit(eventTypes.backend['.UserUpdated'], event.model))
            .listen('.TrackUpdated', event => EventBus.emit(eventTypes.backend['.TrackUpdated'], event.model))
            .listen('.DesignUpdated', event => EventBus.emit(eventTypes.backend['.DesignUpdated'], event.model))
            .listen('BattleFinished', event => EventBus.emit(eventTypes.backend.BattleFinished, event))
            .listen('BattleInProgress', event => EventBus.emit(eventTypes.backend.BattleInProgress, event))
            .listen('CommentCreated', event => EventBus.emit(eventTypes.backend.CommentCreated, event))
            .listen('DownloadCreated', event => EventBus.emit(eventTypes.backend.DownloadCreated, event))
            .listen('FavoriteCreated', event => EventBus.emit(eventTypes.backend.FavoriteCreated, event))
            .listen('LikeCreated', event => EventBus.emit(eventTypes.backend.LikeCreated, event))
            .listen('OrderSuccess', event => EventBus.emit(eventTypes.backend.OrderSuccess, event))
            .listen('SalesCreated', event => EventBus.emit(eventTypes.backend.SalesCreated, event))
            .listen('ReportClosed', event => EventBus.emit(eventTypes.backend.ReportClosed, event));

        connectedUserId = userId;
    },

    /**
     * Disconnects from private channel
     */
    leavePrivate() {
        const webSockets = Echo.getInstance();

        if (! webSockets) return;

        if (channelName && connectedUserId) {
            try {
                webSockets.leave(channelName);
            } catch (e) {
                console.error(e)
            }
            channelName = null;
            connectedUserId = null;
        }
    },

    /**
     * Disconnects from WebSockets
     */
    disconnect() {
        if (! WebSocket) return;

        this.leavePrivate();

        WebSocket.disconnect();
        WebSocket = null;
        // auth.setSocketId(null);
        console.info('WebSockets: Disconnected');
    },

    /**
     *
     * @param {Echo} webSockets
     * @returns {Promise<string>}
     */
    getSocketId(webSockets) {
        return new Promise(resolve =>
            webSockets.connector.pusher.connection.bind('connected', () => {
                if (! Utils.isProduction()) {
                    console.info(`WebSockets: ${ options.wsHost }:${ options.wsPort }`);
                }
                resolve(WebSocket.socketId());
            }),
        );
    },

    /**
     * @param env
     * @returns {{wsHost: string, broadcaster: string, wssPort: number, authorizer: (function(*): {authorize: (function(*, *): void)}), auth: {headers: {Accept: string, 'X-XSRF-TOKEN'}}, wsPort: number, forceTLS: boolean, enabledTransports: string[], key: *}|null}
     */
    parseOptions(env) {
        if (env.broadcastDriver !== 'reverb') return null;

        return {
            broadcaster: 'reverb',
            key: env.reverbAppKey,
            wsHost: env.reverbHost || window.location.hostname,
            wsPort: env.reverbPort,
            wssPort: env.reverbPort,
            forceTLS: env.reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
            authorizer,
            auth: {
                headers: {
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': CookiesHelper.getCookie('XSRF-TOKEN')
                }
            }
        }
    },
}

/**
 * Login into Private and Presence Channels
 * @param channel
 * @returns {{authorize: authorize}}
 */
const authorizer = (channel) => ({
    authorize: (socketId, callback) => {
        Http.postJson('/broadcasting/auth', { // Will be prefixed with /api
            socket_id: socketId,
            channel_name: channel.name
        }, { quietly: true })
            .then(response => {
                callback(false, response); // Response from successful Channel
            })
            .catch(error => {
                callback(true, error);
            });
    }
});

export default Echo;
