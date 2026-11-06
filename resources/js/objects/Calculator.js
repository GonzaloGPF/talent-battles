const Calculator = {
    getProductTotals(price, quantity = 1, discount = 0, tax = 0) {
        const subtotal = price * quantity;
        const discountAmount = subtotal * discount / 100;
        const total = subtotal - discountAmount;
        const taxAmount = subtotal * tax / 100;
        const totalWithTaxes = total + taxAmount;

        return {
            price,
            quantity,
            subtotal,
            discount,
            discount_amount: discountAmount,
            total,
            tax,
            tax_amount: taxAmount,
            total_with_taxes: totalWithTaxes,
        }
    },

    getPromoDiscount(promoData, months = 0) {
        if (! months) return 0;

        const minMonths = promoData['min_months_discount'];
        const maxMonths = promoData['max_months_discount'];
        const maxDiscount = promoData['max_discount'];
        const discount = promoData['discount'];

        if (months <= minMonths) {
            return 0;
        }

        if (months >= maxMonths) {
            return maxDiscount;
        }

        return (months - minMonths) * discount;
    },

    getPointsDiscount(points, discounts) {
        points = points || 0;

        const discountsValue = discounts || {};

        const key = Object.keys(discountsValue)
            .map((minPoints) => parseFloat(minPoints))
            .sort((a, b) => a - b)
            .filter((minPoints) => points <= minPoints)
            .shift();

        return discountsValue[key] || 0;
    }
}

export default Calculator;
