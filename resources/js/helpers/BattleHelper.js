import TrackHelper from '@/helpers/TrackHelper';
import DesignHelper from '@/helpers/DesignHelper';
import { unref } from 'vue';

export default {
    indexParams() {
        const params = {
            with: [
                'winner',
                'votes'
            ],
            waveform: true
        };

        TrackHelper.indexParams.with
            .forEach(param => params.with.push(`tracks.${ param }`));

        DesignHelper.indexParams.with
            .forEach(param => params.with.push(`designs.${ param }`));

        return params;
    },

    sumVotes(battle, subject) {
        battle = unref(battle);
        subject = unref(subject);

        if (! subject || ! battle) return 0;

        return (battle.votes || [])
            .filter(vote => vote.votable_id === subject.id)
            .map(vote => vote.value)
            .reduce((a, b) => a + b, 0);
    },

    isWinner(battle, subject) {
        battle = unref(battle);
        subject = unref(subject);

        const winner = battle?.winner;

        if (! subject || ! winner) return false;

        return winner.id === subject.id;
    }
}
