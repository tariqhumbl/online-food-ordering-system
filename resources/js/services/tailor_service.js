import { http, httpFile } from './http_service';

export const getTailorShop = () => {
  return http().get(`/api/admin/get-tailor-shop/`);
};

export const CreateTailor = (data) => {
  return http().post(`/api/admin/tailors`, data);
};

export const getTailorList = () => {
  return http().get(`/api/admin/tailors/`);
};
export function loadMoreTailors(page) {
    return http().get(`/api/admin/tailors?page=${page}`);
}
export const updateTailorInformation = (id, data) => {
  return http().put(`/api/admin/edit-request-tailor-change/${id}`, data);
};

export function deleteTailor(id) {
    return http().delete(`/api/admin/delete-request-tailor-change/${id}`);
}

