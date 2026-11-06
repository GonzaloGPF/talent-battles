import Amplitude from 'amplitudejs';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import Utils from '@/objects/Utils';

export const usePlayer = defineStore('player', () => {
    const tracks = ref([]);
    const track = ref(null);
    const playing = ref(false);
    const volume = ref(100);
    const mute = ref(false);
    const progress = ref(0);
    const playedSeconds = ref(0);
    const repeat = ref(false);
    const initialized = computed(() => !! tracks.value?.length);
    const src = computed(() => Utils.getFileURL(track.value?.audio));
    const progressPercent = computed(() => progress.value / 100);
    const author = computed(() => track.value?.createdBy);
    const volumeIcon = computed(() => {
        if (! volume.value || mute.value) {
            return 'mdi-volume-off';
        }
        if (volume.value <= 25) {
            return 'mdi-volume-low';
        }
        if (volume.value <= 50) {
            return 'mdi-volume-medium';
        }
        return 'mdi-volume-high'
    });
    // const playingIndex = computed(() => tracks.value?.indexOf(state.track));
    const init = (track) => {
        initialize(track, volume.value, {
            setActiveTrack,
            setPlaying,
            setProgress,
            setSeconds,
        });
        tracks.value?.push(track);
        track.value = track;

        return 0; // index of first track
    }

    const pushTrack = (track) => {
        tracks.value?.push(track)

        return tracks.value?.findIndex(t => t.id === track.id);
    };

    const setActiveTrack = (index) => track.value = tracks.value[index];

    const setProgress = (value) => progress.value = value || 0;

    const setPlaying = (value) => playing.value = value;

    const playPause = (index) => {
        if (Amplitude.getActiveIndex() !== index) {
            Amplitude.playSongAtIndex(index)
        } else if (Amplitude.getPlayerState() === 'playing') {
            Amplitude.pause();
        } else {
            Amplitude.play();
        }
        playing.value = true;
    }

    const stop = () => Amplitude.stop();

    const setVolume = (value) => {
        Amplitude.setVolume(value);
        volume.value = value;
    }

    const muteUnMute = () => {
        mute.value = ! mute.value;

        if (mute.value) {
            Amplitude.setVolume(0);
        } else {
            Amplitude.setVolume(volume.value);
        }
    }

    const setSeconds = (seconds) => playedSeconds.value = seconds || 0;

    const setPlayedPercentage = (index, percent) => {
        if (Amplitude.getActiveIndex() !== index) {
            Amplitude.playSongAtIndex(index);
            setTimeout(() => Amplitude.setSongPlayedPercentage(percent), 200);
        } else {
            Amplitude.setSongPlayedPercentage(percent)
        }
    }
    const next = () => Amplitude.next();

    const prev = () => Amplitude.prev();

    const toggleRepeat = () => {
        repeat.value = ! repeat.value;
        Amplitude.setRepeat(repeat.value);
        Amplitude.setRepeatSong(repeat.value);
    }

    const addTrack = (track) => {
        if (tracks.value?.map(track => track.id).includes(track.id)) {
            return tracks.value?.findIndex(t => t.id === track.id); // returns index
        }

        if (! tracks.value?.length) {
            init(track);

            return 0;
        } else {
            const index = pushTrack(track);

            Amplitude.addSong(trackToSong(track));

            return index;
        }
    }

    // watch(tracks, () => {
    //     nextTick(() => Amplitude.bindNewElements());
    // })

    const closeTrack = () => {
        stop();
        track.value = null;
    };

    return {
        tracks,
        track,
        playing,
        volume,
        mute,
        progress,
        playedSeconds,
        repeat,
        initialized,
        src,
        progressPercent,
        author,
        volumeIcon,
        init,
        pushTrack,
        setActiveTrack,
        setProgress,
        setPlaying,
        playPause,
        stop,
        setVolume,
        muteUnMute,
        setSeconds,
        setPlayedPercentage,
        next,
        prev,
        toggleRepeat,
        addTrack,
        closeTrack,
    }
})

function initialize(track, volume, callbacks) {
    const {
        setActiveTrack,
        setPlaying,
        setProgress,
        setSeconds,
    } = callbacks;

    Amplitude.init({
        songs: [trackToSong(track)], // If no song, Amplitude won't initialize
        volume: volume,
        preload: 'none',
        callbacks: {
            play() {
                setActiveTrack(Amplitude.getActiveIndex());
                setPlaying(true);
            },
            timeupdate() {
                setProgress(Amplitude.getSongPlayedPercentage());
                setSeconds(Amplitude.getSongPlayedSeconds());
            },
            pause() {
                setPlaying(false);
            },
            stop() {
                setPlaying(false);
            },
            // song_change() {
            //
            // }
        }
    });
}

/**
 * Converts a Track object to Amplitude Song
 * @param track
 * @returns {{artist: undefined, album: undefined, name, covert_art_url: (string|null), url: string}}
 */
function trackToSong(track) {
    return {
        name: track.name,
        artist: track?.createdBy?.nick_name,
        album: track?.album?.name,
        url: Utils.getFileURL(track.audio) + '?stream=1',
        covert_art_url: Utils.getFileURL(track.image),
    }
}
