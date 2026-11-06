const AddressHelper = {
    addressToStripe(address) {
        if (!address) {
            return null;
        }
        return {
            line1: address.line1,
            line2: address.line2,
            city: address.city,
            state: address.region_code,
            postal_code: address.postcode,
            country: address.country_code.toUpperCase(),
        }
    },

    stripeToAddress(stripeAddress) {
        return {
            // country: stripeAddress.country_name,
            country_code: stripeAddress.country,
            // region: stripeAddress.state_name,
            region_code: stripeAddress.state,
            city: stripeAddress.city,
            postcode: stripeAddress.postal_code,
            line1: stripeAddress.line1,
            line2: stripeAddress.line2,
        }
    }
}

export default AddressHelper;
