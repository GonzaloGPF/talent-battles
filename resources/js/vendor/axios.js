import axios from 'axios';
import global from '@/config/global';
import AxiosInterceptor from "@/objects/AxiosInterceptor";

const Axios = axios.create({
    baseURL: global.getAppURL() + '/api', // Every front's end request will suffix an 'api'
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
});

const axiosInterceptor = new AxiosInterceptor(Axios);

Axios.interceptors.request.use(axiosInterceptor.getRequestInterceptor());

Axios.interceptors.response.use(
    axiosInterceptor.getResponseInterceptor(),
    axiosInterceptor.getErrorInterceptor()
);

export default Axios;
