import store from "../store";
import axios from "axios";
import * as auth from "./auth_service";

export function http() {
    return axios.create({
        baseURL: store.state.apiURL,
        withCredentials: true, // ✅ Ensures cookies are sent
        headers: {
            Authorization: `Bearer ${auth.getAccessToken()}`,
            Accept: "application/json",
        },
    });
}

export function httpFile() {
    return axios.create({
        baseURL: store.state.apiURL,
        withCredentials: true, // ✅ Ensures cookies are sent
        headers: {
            Authorization: `Bearer ${auth.getAccessToken()}`,
            "Content-Type": "multipart/form-data",
            Accept: "application/json",
        },
    });
}

