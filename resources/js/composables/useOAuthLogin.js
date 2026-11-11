import { useRoute } from 'vue-router';

const PENDING_VOTE_KEY = 'pending_vote';
const PENDING_VOTE_TTL = 15 * 60 * 1000;

/**
 * Signs in with Google or Facebook in the same tab, coming back to the page the visitor was on.
 * A guest's vote waits in sessionStorage while they sign in, and never in the URL: a shared link cannot vote for anyone.
 */
export default () => {
    const route = useRoute();

    const loginWith = (driver, path = route.fullPath) => {
        window.location.assign(`/${ driver }/redirect?redirect=${ encodeURIComponent(path) }`);
    }

    const voteWithGoogle = (battle, creation) => {
        writePendingVote({ battleId: battle.id, modelId: creation.id, savedAt: Date.now() });

        loginWith('google', `/battles/${ battle.id }`);
    }

    /**
     * The vote a guest chose for that battle before signing in, if it is recent. It is forgotten once read.
     *
     * @param {number} battleId
     * @returns {{battleId: number, modelId: number, savedAt: number}|null}
     */
    const takePendingVote = (battleId) => {
        const pendingVote = readPendingVote();

        forgetPendingVote();

        if (pendingVote?.battleId !== battleId || Date.now() - pendingVote.savedAt > PENDING_VOTE_TTL) {
            return null;
        }

        return pendingVote;
    }

    return {
        loginWith,
        voteWithGoogle,
        takePendingVote,
    }
}

function readPendingVote() {
    try {
        return JSON.parse(sessionStorage.getItem(PENDING_VOTE_KEY));
    } catch {
        return null;
    }
}

function writePendingVote(pendingVote) {
    try {
        sessionStorage.setItem(PENDING_VOTE_KEY, JSON.stringify(pendingVote));
    } catch {
        // Without storage the guest signs in and votes again
    }
}

function forgetPendingVote() {
    try {
        sessionStorage.removeItem(PENDING_VOTE_KEY);
    } catch {
        // Nothing to forget
    }
}
