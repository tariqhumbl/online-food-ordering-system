import { http, httpFile } from './http_service';

export const getUserList = () => {
  return http().get(`/api/admin/customer-information/`);
};

export function loadMoreUsers(page) {
    return http().get(`/api/admin/customer-information?page=${page}`);
}


