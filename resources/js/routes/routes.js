import guest from '@/guards/guest';
import auth from '@/guards/auth';
import onlyMe from '@/guards/onlyMe';
import fetchTrack from '@/guards/fetchTrack';
import fetchDesign from '@/guards/fetchDesign';
import fetchUser from '@/guards/fetchUser';
import internal from '@/guards/internal';
import fetchAlbum from '@/guards/fetchAlbum';
import fetchBattle from '@/guards/fetchBattle';

export default [
    {
        path: '/:pathMatch(.*)*',
        // component: NotFound
        redirect: { name: 'welcome' },
    },
    {
        path: '/',
        name: 'welcome',
        component: () => import('@/pages/WelcomeIndex.vue')
    },
    {
        path: '/password/reset/:token',
        name: 'password.reset',
        component: () => import('@/pages/login/PasswordReset.vue'),
        // beforeEnter: [guest]
    },
    {
        path: '/invitations/:id/claim',
        name: 'invitations.claim',
        component: () => import('@/pages/invitations/InvitationClaim.vue'),
        beforeEnter: [guest]
    },
    {
        path: '/users',
        name: 'users.index',
        component: () => import('@/pages/users/UserIndex.vue'),
    },
    {
        path: '/users/:id',
        name: 'users.show',
        component: () => import('@/pages/users/UserShow.vue'),
        beforeEnter: [fetchUser]
    },
    {
        path: '/users/:id/claim',
        name: 'users.claim',
        component: () => import('@/pages/users/ProfileClaim.vue'),
        beforeEnter: [guest]
    },
    {
        path: '/beats',
        name: 'beats.index',
        component: () => import('@/pages/tracks/TrackIndex.vue'),
    },
    {
        path: '/beats/:id',
        name: 'beats.show',
        component: () => import('@/pages/tracks/TrackShow.vue'),
        beforeEnter: [fetchTrack]
    },
    {
        path: '/songs',
        name: 'songs.index',
        component: () => import('@/pages/tracks/TrackIndex.vue'),
    },
    {
        path: '/songs/:id',
        name: 'songs.show',
        component: () => import('@/pages/tracks/TrackShow.vue'),
        beforeEnter: [fetchTrack]
    },
    {
        path: '/samples',
        name: 'samples.index',
        component: () => import('@/pages/tracks/TrackIndex.vue'),
    },
    {
        path: '/samples/:id',
        name: 'samples.show',
        component: () => import('@/pages/tracks/TrackShow.vue'),
        beforeEnter: [fetchTrack]
    },
    {
        path: '/designs',
        name: 'designs.index',
        component: () => import('@/pages/designs/DesignIndex.vue'),
    },
    {
        path: '/designs/:id',
        name: 'designs.show',
        component: () => import('@/pages/designs/DesignShow.vue'),
        beforeEnter: [fetchDesign]
    },
    {
        path: '/battles',
        name: 'battles.index',
        component: () => import('@/pages/battles/BattleIndex.vue'),
    },
    {
        path: '/battles/:id',
        name: 'battles.show',
        component: () => import('@/pages/battles/BattleShow.vue'),
        beforeEnter: [fetchBattle]
    },
    {
        path: '/albums/:id',
        name: 'albums.show',
        component: () => import('@/pages/albums/AlbumShow.vue'),
        beforeEnter: [fetchAlbum]
    },
    {
        path: '/contributors',
        name: 'contributors.index',
        component: () => import('@/pages/contributors/ContributorsIndex.vue'),
    },
    {
        path: '/carts',
        name: 'carts.show',
        component: () => import('@/pages/orders/CartShow.vue'),
    },
    {
        path: '/orders/:id/success',
        name: 'orders.success.show',
        component: () => import('@/pages/orders/OrderSuccessShow.vue'),
    },
    {
        path: '/stats',
        component: () => import('@/pages/stats/StatsIndex.vue'),
        beforeEnter: [auth],
        children: [
            {
                path: '',
                name: 'stats.summary',
                component: () => import('@/pages/stats/StatsSummary.vue'),
            },
            {
                path: 'balance',
                name: 'stats.balance',
                component: () => import('@/pages/stats/StatsBalance.vue'),
            },
        ]
    },
    {
        path: '/commerce',
        component: () => import('@/pages/admin/AdminCommerceIndex.vue'),
        beforeEnter: [auth, (to, from, next) => to.name === 'commerce.index' ? next({name: 'admin.products.index'}) : next()],
        children: [
            {
                path: '',
                name: 'admin.products.index',
                component: () => import('@/pages/admin/AdminProductIndex.vue'),
            },
            {
                path: 'sales',
                name: 'admin.sales.index',
                component: () => import('@/pages/admin/AdminSaleIndex.vue'),
            },
            {
                path: 'orders',
                name: 'admin.orders.index',
                component: () => import('@/pages/admin/AdminOrderIndex.vue'),
            },
        ]
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('@/pages/users/ProfileWrapper.vue'),
        beforeEnter: [onlyMe],
        children: [
            {
                path: '',
                name: 'profile.show',
                component: () => import('@/pages/users/ProfileShow.vue'),
            },
            {
                path: 'notification',
                name: 'profile.notification',
                component: () => import('@/pages/users/ProfileNotification.vue'),
            },
            {
                path: 'payment_methods',
                name: 'profile.payment_methods',
                component: () => import('@/pages/users/ProfilePaymentMethod.vue'),
            },
            {
                path: 'account',
                name: 'profile.account',
                component: () => import('@/pages/users/ProfileAccount.vue'),
            },
            {
                path: 'invitations',
                name: 'profile.invitations.index',
                component: () => import('@/pages/admin/AdminInvitationsIndex.vue'),
            },
        ]
    },
    {
        path: '/admin',
        name: 'admin.index',
        component: () => import('@/pages/admin/AdminIndex.vue'),
        beforeEnter: [auth],
        children: [
            {
                path: 'users',
                name: 'admin.users.index',
                component: () => import('@/pages/admin/AdminUserIndex.vue'),
                beforeEnter: [internal]
            },
            {
                path: 'tracks',
                name: 'admin.tracks.index',
                component: () => import('@/pages/admin/AdminTrackIndex.vue'),
            },
            {
                path: 'designs',
                name: 'admin.designs.index',
                component: () => import('@/pages/admin/AdminDesignIndex.vue'),
            },
            {
                path: 'albums',
                name: 'admin.albums.index',
                component: () => import('@/pages/admin/AdminAlbumIndex.vue'),
            },
            {
                path: 'battles',
                name: 'admin.battles.index',
                component: () => import('@/pages/admin/AdminBattleIndex.vue'),
            },
            // {
            //     path: 'orders',
            //     name: 'admin.orders.index',
            //     component: () => import('@/pages/admin/AdminOrderIndex.vue'),
            // },
            // {
            //     path: 'orders/:id/success',
            //     redirect: { name: 'admin.orders.index' },
            // },
            // {
            //     path: 'sales',
            //     name: 'admin.sales.index',
            //     component: () => import('@/pages/admin/AdminSaleIndex.vue'),
            // },
            {
                path: 'reports',
                name: 'admin.reports.index',
                component: () => import('@/pages/admin/AdminReportIndex.vue'),
            },
            {
                path: 'favorites',
                name: 'admin.favorites.index',
                component: () => import('@/pages/admin/AdminFavoriteIndex.vue'),
            },
            {
                path: 'emails',
                name: 'admin.emails.index',
                component: () => import('@/pages/admin/AdminEmailIndex.vue'),
            },
        ]
    },
];
