import axios from '@/vendor/axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import TrackHelper from '@/helpers/TrackHelper';

export const useTrack = defineStore('tracks', () => {
    const track = ref({});

    // const trackType = computed(() => {
    //     switch (name) {
    //         case 'beats.index':
    //             return 'beat';
    //         case 'songs.index':
    //             return 'song';
    //         case 'samples.index':
    //             return 'sample';
    //     }
    //         return null;
    //     }
    // );
    const fetch = (params) => axios.get('tracks', { params });
    const create = (form) => axios.post('tracks', form);
    const read = async (id) => {
        let params = TrackHelper.showParams;

        const { data } = await axios.get(`tracks/${ id }`, { params })

        track.value = data;

        return Promise.resolve(data);
    }
    const update = (form) => {
        form._method = 'PUT';
        return axios.post(`tracks/${ form.id }`, form);
    }
    const destroy = (id) => axios.delete(`tracks/${ id }`);

    return {
        track,
        fetch,
        create,
        read,
        update,
        destroy,
    }
});
