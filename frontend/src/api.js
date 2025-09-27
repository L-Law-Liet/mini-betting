import axios from "axios";
import CryptoJS from "crypto-js";

const API_URL = process.env.VUE_APP_API_URL;
const SECRET = process.env.VUE_APP_HMAC_SECRET;

const api = axios.create({
    baseURL: API_URL,
});

api.interceptors.request.use((config) => {
    const body = config.data ? JSON.stringify(config.data) : "";

    const hash = CryptoJS.HmacSHA256(body, SECRET);
    const signature = CryptoJS.enc.Base64.stringify(hash);

    config.headers["X-Signature"] = signature;

    const token = localStorage.getItem("auth_token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

export default api;
