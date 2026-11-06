import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

export default {
    badge: (model, stateName) => buildBadge(model, stateName),

    role: (name) => buildBadge('role', name),

    emailType: (name) => buildBadge('emailType', name),

    thematic: (name) => buildBadge('thematic', name),

    category: (name) => buildBadge('category', name),

    paymentState: (name) => buildBadge('paymentState', name),

    battleState: (name) => buildBadge('battleState', name),

    reportState: (name) => buildBadge('reportState', name),

    datetime: (value) => {
        const formattedDate = Formatter.dateTimeFormat(value) ?? '';

        let datePieces = formattedDate.split(' ');

        if (datePieces.length !== 2) {
            datePieces = ['-', null];
        }

        return buildTwoValues(datePieces[0], datePieces[1], true);
    },

    amountAndPercent: (value, percent) => {
        const formattedMoney = Formatter.money(value);

        const formattedPercent = Formatter.percent(percent);

        return buildTwoValues(formattedMoney, percent ? `(${formattedPercent})` : null, true, true);
    },
}

function buildTwoValues(firstValue, secondValue, stacked, centered) {
    const displayClass = stacked ? 'd-block' : 'd-inline';

    let element = `<span class="${displayClass} whitespace-nowrap">${ firstValue }</span>`;

    if (secondValue) {
        element += `<span class="${displayClass} text-caption font-weight-light whitespace-nowrap">${ secondValue }</span>`
    }

    const containerClass = centered ? 'text-center' : 'text-start';

    return `<div class="${containerClass}">${element}</div>`;
}

/**
 * @param {string} constant
 * @param {string} name
 * @returns {string}
 */
function buildBadge(constant, name) {
    if (! name) return '-';

    const translated = Translator.tConstName(constant, name);

    constant = Formatter.snakeCase(constant);
    name = Formatter.snakeCase(name);

    const bgColor = `bg-${constant}_${name}`;

    return `<span class="badge ${ bgColor }">${ translated }</span>`
}
