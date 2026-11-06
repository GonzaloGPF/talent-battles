import vuetifyColors from 'vuetify/lib/util/colors';

const brand = vuetifyColors.blueGrey.base;
const info = vuetifyColors.blue.base;
const success = vuetifyColors.green.base;
const warning = vuetifyColors.yellow.base;
const danger = vuetifyColors.red.base;

const user = vuetifyColors.cyan.base;
const beat = vuetifyColors.deepPurple.base;
const design = vuetifyColors.indigo.base;
const song = vuetifyColors.pink.base;
const sample = vuetifyColors.purple.base;

const Colors = {
    // primary: vuetifyColors.red.darken1, // #E53935
    // secondary: vuetifyColors.red.lighten4, // #FFCDD2
    success: success,
    warning: warning,
    info: info,
    danger: danger,
    brand: brand,
    battle: brand,
    user: user,
    track: beat,
    beat: beat,
    sample: sample,
    song: song,
    design: design,
    album: vuetifyColors.blue.base,
    order: vuetifyColors.lightGreen.base,
    sale: vuetifyColors.green.base,
    activity: vuetifyColors.cyan.base,
    donation: vuetifyColors.pink.lighten4,
    report: vuetifyColors.red.lighten4,
    favorite: vuetifyColors.pink.darken4,
    email: vuetifyColors.blue.lighten4,

    role_admin: brand,
    role_manager: brand,
    role_user: user,

    email_type_verify_email: brand,
    email_type_reset_password: info,
    email_type_order_success: success,

    payment_state_pending: warning,
    payment_state_success: success,
    payment_state_error: danger,

    report_state_open: danger,
    report_state_working: warning,
    report_state_solved: success,
    report_state_closed: info,

    category_producers: beat,
    category_designers: design,
    category_singers: song,
    category_musicians: sample,

    thematic_free: vuetifyColors.shades.white,
    thematic_nature: vuetifyColors.green.base,
    thematic_future: vuetifyColors.purple.base,
    thematic_experimental: vuetifyColors.brown.base,
    thematic_claim: vuetifyColors.red.base,
    thematic_comedy: vuetifyColors.lime.base,

    battle_state_open: info,
    battle_state_in_progress: warning,
    battle_state_finished: success,
}

export default Colors;
