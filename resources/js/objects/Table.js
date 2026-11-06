import Translator from '@/objects/Translator';
import _ from 'lodash';

const Table = {
    addCommonHeaders(headers, withActions, withExpand, isAdmin) {
        if(!Array.isArray(headers)) {
            headers = []
        } else {
            headers = [...headers];
        }

        if (withActions) {
            headers.push({
                title: Translator.tl('actions'),
                key: 'action',
            });
        }

        if (isAdmin) {
            headers.unshift({
                title: 'ID',
                key: 'id',
                sortable: true
            });
        }

        if (withExpand) {
            headers.unshift({
                title: '',
                key: 'data-table-expand',
            });
        }

        return headers;
    },

    getCustomCells(headers) {
        return (headers || [])
            .filter(header => header.formatter || header.markup)
            .map(header => ({
                header,
                name: `item.${ header.key }`,
            }));
    },

    getItemContent(header, item) {
        const value = _.get(item, header.key);

        if (header.formatter) {
            return header.formatter(value, item);
        }

        if (header.markup) {
            return header.markup(value, item);
        }

        return value;
    }
}

export default Table;
