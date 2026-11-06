import Cookies from 'js-cookie';

export default {
    getCookie: (name) => Cookies.get(name),
}
