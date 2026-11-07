import { START_LOCATION } from 'vue-router';
import global from '@/config/global';
import Translator from '@/objects/Translator';
import { useAlbum } from '@/stores/albums';
import { useDesign } from '@/stores/designs';
import { useTrack } from '@/stores/tracks';
import { useUser } from '@/stores/users';

const SECTIONS = ['beats', 'songs', 'samples', 'designs', 'battles', 'users', 'contributors'];

/**
 * Title of the browser tab, built with the same translations as the meta tags of the server
 * (PageMetaService). The first page keeps the title that the server rendered.
 */
export default (to, from, failure) => {
    if (failure || from === START_LOCATION) {
        return;
    }

    const app = global.getAppName();
    const page = getPageTitle(to);

    if (page) {
        document.title = Translator.t('seo.title', { page, app });
    } else {
        document.title = to.name === 'welcome' ? Translator.t('seo.home_title', { app }) : app;
    }
}

const getPageTitle = (to) => {
    const [section, action] = String(to.name ?? '').split('.');

    if (action === 'index' && SECTIONS.includes(section)) {
        return Translator.t(`seo.pages.${ section }.title`);
    }

    switch (to.name) {
        case 'beats.show':
        case 'songs.show':
        case 'samples.show': {
            const { track } = useTrack();

            return Translator.t('seo.track_title', {
                name: track.name,
                type: Translator.t(`seo.track_types.${ track.track_type }`),
                author: getAuthor(track),
            });
        }

        case 'designs.show': {
            const { design } = useDesign();

            return Translator.t('seo.design_title', { name: design.name, author: getAuthor(design) });
        }

        case 'albums.show': {
            const { album } = useAlbum();

            return Translator.t('seo.album_title', { name: album.name, author: getAuthor(album) });
        }

        case 'users.show':
            return useUser().user.nick_name;
    }
}

const getAuthor = (creation) => creation.createdBy?.nick_name || Translator.t('seo.unknown_author');
