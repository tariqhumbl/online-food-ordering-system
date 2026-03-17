import { http, httpFile } from './http_service';
export function createTailorShop(data) {
    return httpFile().post('/api/admin/tailor-shop', data)
}

export function loadTailorShops() {
    return http().get('/api/admin/tailor-shop/');
}
export function loadMoreTailorShops(page) {
    return http().get(`/api/admin/tailor-shop?page=${page}`);
}
export function updateTailorShop(id, data) {
    return httpFile().post(`/api/admin/tailor-shop/${id}`, data);
}

export function deleteTailorShops(id) {
    return http().delete(`/api/admin/tailor-shop/${id}`);
}
