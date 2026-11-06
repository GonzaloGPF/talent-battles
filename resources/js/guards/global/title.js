import global from '@/config/global';
import { useTitle } from '@/stores/title';
// import { useTrack } from '@/stores/tracks';
// import { useDesign } from '@/stores/designs';
// import { useUser } from '@/stores/users';
// import { useAlbum } from '@/stores/albums';
// import Translator from '@/objects/Translator';
// import Formatter from '@/objects/Formatter';

export default (to) => {
    const { setTitle } = useTitle();

    if (to.meta.title) {
        return setTitle(to.meta.title);
    }

    // if (to.name.includes('.index')) {
    //     title = getIndexTitle(to.name);
    // } else {
    //     title = getShowTitle(to.name);
    // }

    let appName = global.getAppName();

    // if (title) {
    //     title = Formatter.text(title, 12);
    //     appName = `${ title } - ${ appName }`;
    // }

    // document.querySelector('meta[name="og:title"]').setAttribute("content", meta['og:title']);
    // document.querySelector('meta[name="description"]').setAttribute("content", meta.description);
    // document.querySelector('meta[name="og:description"]').setAttribute("content", meta['og:description']);
    // document.querySelector('meta[name="keywords"]').setAttribute("content", meta.keywords);

    setTitle(appName);
}

// const getIndexTitle = (routeName) => {
//     if (! routeName) return;
//
//     let beforeIndex = /(.*?)\.index/.exec(routeName);
//
//     if (! Array.isArray(beforeIndex)) return;
//
//     let pieces = beforeIndex[1].split('.');
//
//     return Translator.translate(pieces[pieces.length - 1], true);
// }
//
// const getShowTitle = (routeName) => {
//     if (! routeName) return;
//
//     const { track } = useTrack();
//     const { design } = useDesign();
//     const { user } = useUser();
//     const { album } = useAlbum();
//
//     switch (routeName) {
//         case 'designs.show':
//             return design.name;
//
//         case 'beats.show':
//         case 'samples.show':
//         case 'songs.show':
//             return track.name;
//
//         case 'albums.show':
//             return album.name;
//
//         case 'users.show':
//             return user.name;
//
//         case 'orders.success.show':
//             return Translator.modelTitle('order', true);
//     }
// }
