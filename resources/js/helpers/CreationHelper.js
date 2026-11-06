import TrackHelper from '@/helpers/TrackHelper';
import DesignHelper from '@/helpers/DesignHelper';
import Formatter from '@/objects/Formatter';

const CreationHelper = {
    isTrack(creation) {
        if (! creation) return false;

        return creation.model === 'track';
    },

    isDesign(creation) {
        if (! creation) return false;

        return creation.model === 'design';
    },

    getCreationIcon(creation) {
        if (! creation) return '';

        if (CreationHelper.isTrack(creation)) {
            return `$${creation.track_type}`;
        }

        return '$design';
    },

    getCreationShowRoute(creation) {
        if (! creation) return {};

        if (CreationHelper.isTrack(creation)) {
            return TrackHelper.showRoute(creation);
        }

        if (CreationHelper.isDesign(creation)) {
            return DesignHelper.showRoute(creation);
        }
    },

    routeNameToType(routeName) {
        if (! routeName) return;

        const base = routeName.split('.')[0];
        return Formatter.singular(base);
    },

    ratingColor(value) {
        if (value <= 10) {
            return 'red'
        }
        if (value <= 20) {
            return 'deep-orange'
        }
        if (value <= 30) {
            return 'orange'
        }
        if (value <= 40) {
            return 'amber'
        }
        if (value <= 50) {
            return 'yellow'
        }
        if (value <= 75) {
            return 'blue'
        }
        if (value <= 100) {
            return 'green'
        }
    }
}

export default CreationHelper;
