export default {
    NEW_NOTIFICATION: 'new_notification',

    backend: {
        BattleFinished: 'battle_finished',
        BattleInProgress: 'battle_in_progress',
        CommentCreated: 'comment_created',
        DownloadCreated: 'download_success',
        FavoriteCreated: 'favorite_created',
        LikeCreated: 'like_created',
        OrderSuccess: 'order_success',
        TrackSold: 'track_sold',
        DesignSold: 'design_sold',
        ReportCreated: 'report_created',
        ReportSolved: 'report_solved',
        ReportClosed: 'report_closed',
        SalesCreated: 'sales_created',
        NewDeploy: 'new_deploy',

        /* Model Events broadcast */
        '.UserUpdated': 'user_updated',
        '.TrackUpdated': 'track_updated',
        '.DesignUpdated': 'design_updated',
    },

    /* Frontend Event */
    confirmed: 'confirmed',
    errors: 'errors',
    flash_message: 'flash_message',
    entered: 'entered',
    new_design: 'new_design',
    new_track: 'new_track',
    new_user: 'new_user',
}
