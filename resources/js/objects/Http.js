import axios from '@/vendor/axios';
import qs from 'qs';
import Translator from '@/objects/Translator';
import { useFlashMessages } from '@/stores/flashMessages';

const Http = {
    /**
     * Basic GET request
     *
     * @param {string} url
     * @param {null|{}} config
     * @returns {Promise<T>}
     */
    getJson: (url, config = {}) => axios.get(url, config),

    /**
     * Basic PUT request
     *
     * @param {string} url
     * @param {*} data
     * @param {null|{}} config
     * @returns {Promise}
     */
    putJson: (url, data = {}, config = null) => axios.put(url, data, config),

    /**
     * Basic POST request
     *
     * @param {string} url
     * @param {*} data
     * @param {null|{}} config
     * @returns {Promise}
     */
    postJson: (url, data = {}, config = null) => axios.post(url, data, config),

    /**
     * Basic DELETE request
     *
     * @param {string} url
     * @param {null|{}} config
     * @returns {Promise}
     */
    deleteJson: (url, config = null) => axios.delete(url, config),

    /**
     * Open a new tab with specified url
     *
     * @param {string} url
     * @param {*} params
     */
    download: (url, params = {}) => {
        let query = qs.stringify(params);

        return axios.get(`${ url }?${ query }`, {
            responseType: 'blob',
        }).then(response => {
            let blob = new Blob([response], { type: response.type || 'application/octet-stream' });

            let blobURL = (window.URL && window.URL.createObjectURL)
                ? window.URL.createObjectURL(blob)
                : window.webkitURL.createObjectURL(blob);

            let tempLink = document.createElement('a');
            tempLink.style.display = 'none';
            tempLink.href = blobURL;
            tempLink.setAttribute('download', response.filename);

            document.body.appendChild(tempLink);

            tempLink.click();

            // Fixes "webkit blob resource error 1"
            setTimeout(() => {
                document.body.removeChild(tempLink);
                window.URL.revokeObjectURL(blobURL);
            }, 200)
        });
    },

    /**
     *
     * @param file
     * @param {*|null} params
     * @returns {Promise<AxiosResponse<any>>}
     */
    downloadFile: (file, params = {}) => {
        const { pushFlashMessage } = useFlashMessages();

        if (! file) {
            const noFileText = Translator.t('exceptions.without_data', { data: Translator.modelTitle('file') });

            return pushFlashMessage(noFileText, { type: 'warning' });
        }

        if (typeof file === 'number') {
            return Http.download(`/files/${ file }`, params);
        }

        return Http.download(`/files/${ file.id }`, params);
    },

    /**
     * @param fileIds
     * @returns {Promise<AxiosResponse<any>>}
     */
    downloadZip: (fileIds = []) => {
        const { pushFlashMessage } = useFlashMessages();

        if (! Array.isArray(fileIds)) {
            const noDataText = Translator.t('help.without_data', { data: Translator.modelTitle('file') });

            return pushFlashMessage(noDataText, { type: 'warning' });
        }

        return Http.download('/files/multiple', { file_ids: fileIds });
    },
}

export default Http;
