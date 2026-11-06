import qs from 'qs';

const Query = {
    parseQuery: (query) => {
        query = qs.parse(query);

        Object.keys(query)
            .forEach(key => query[key] = tryParseInt(query[key]));

        return JSON.parse(JSON.stringify(query));
    },
    stringifyQuery(query) {
        return qs.stringify(query, { encode: false });
    },
}

/**
 * Parsing values to int
 * @param value
 * @returns {number|*}
 */
function tryParseInt(value) {
    return Array.isArray(value)
        ? value.map(toInt)
        : toInt(value);
}

function toInt(value) {
    const parsed = parseInt(value);

    return isNaN(parsed)
        ? value
        : parsed;
}

export default Query;
