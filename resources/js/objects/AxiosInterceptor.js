import Translator from '@/objects/Translator';
import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';

const NO_CONTENT = 204;
const CREATED = 201;
const ACCEPTED = 202;
// const PERMANENTLY_REDIRECT = 308;
const UNAUTHORIZED = 401;
// const FORBIDDEN = 403;
// const NOT_FOUND = 404;
const REQUEST_ENTITY_TOO_LARGE = 413;
const UNPROCESSABLE_ENTITY = 422;

export default class AxiosInterceptor {
    constructor(axiosInstance) {
        this.axiosInstance = axiosInstance;
    }

    async getRequestInterceptor() {
        return request => {
            request.headers.common['Accept-Language'] = Translator.getLocale();
            // request.headers['X-Socket-Id'] = authStore.sockedId;
        }
    }

    getResponseInterceptor() {
        return response => {
            const status = response.status;
            const { message } = response.data;

            if (! response.config.quietly) {
                if (status === NO_CONTENT) {
                    EventBus.emit(EventTypes.flash_message, {
                        message: Translator.t('help.no_content'),
                        type: 'warning'
                    });
                }

                if ([CREATED, ACCEPTED].includes(status)) {
                    EventBus.emit(EventTypes.flash_message, {
                        message,
                        type: 'success'
                    });
                }
            }

            const content = response?.headers?.['content-disposition'];
            if (content) {
                response.data.filename = content.replace('attachment; filename=', '', content);
            }

            EventBus.emit(EventTypes.errors, null);

            return response.data;
        }
    }

    getErrorInterceptor() {
        return error => {
            if (! error.response) throw 'no_response';

            const { status, data, config } = error.response;

            // if (status === PERMANENTLY_REDIRECT) {
            //     authStore.setUser(data.data);
            //     return Promise.resolve(data);
            // }

            // Validation Errors
            if (status === UNPROCESSABLE_ENTITY) {
                EventBus.emit(EventTypes.errors, data.data);
            }

            // Session expired
            if (status === UNAUTHORIZED && data.data?.action === 'reload') {
                return this.axiosInstance
                    .get('csrf-cookie', { quietly: true })
                    .then(() => this.axiosInstance(error.config)) // retry last request
                    .catch(() => window.location.reload())
            }

            if (status >= UNAUTHORIZED) {
                let message = data.message || error.response.statusText;

                if (status === REQUEST_ENTITY_TOO_LARGE) {
                    message = Translator.t('exceptions.too_large')
                }

                if (config && ! config.quietly) {
                    EventBus.emit(EventTypes.flash_message, {
                        message,
                        type: 'error'
                    });
                }
            }

            return Promise.reject(error);
        }
    }
}
