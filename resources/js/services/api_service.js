import { http } from './http_service';

const API = {
  restaurants: {
    list: (params) => http().get('/api/restaurants', { params }),
    get: (id) => http().get(`/api/restaurants/${id}`),
    getMenu: (id) => http().get(`/api/restaurants/${id}/menu`),
    create: (data) => http().post('/api/restaurants', data),
    update: (id, data) => http().put(`/api/restaurants/${id}`, data),
    delete: (id) => http().delete(`/api/restaurants/${id}`),
  },
  admin: {
    restaurants: {
      list: (params) => http().get('/api/admin/restaurants', { params }),
      create: (data) => http().post('/api/admin/restaurants', data),
    },
    riders: {
      list: () => http().get('/api/admin/riders'),
      create: (data) => http().post('/api/admin/riders', data),
    },
  },
  orders: {
    list: (params) => http().get('/api/orders', { params }),
    get: (id) => http().get(`/api/orders/${id}`),
    create: (data) => http().post('/api/orders', data),
    updateStatus: (id, status) => http().patch(`/api/orders/${id}/status`, { status }),
  },
  payments: {
    create: (data) => http().post('/api/payments', data),
  },
  notifications: {
    list: (params) => http().get('/api/notifications', { params }),
    markAsRead: (id = null) => http().post('/api/notifications/read', id ? { id } : {}),
  },
  menu: {
    categories: (restaurantId) =>
      http().get(`/api/restaurants/${restaurantId}/menu`),
    createCategory: (restaurantId, data) =>
      http().post(`/api/restaurants/${restaurantId}/menu-categories`, data),
    updateCategory: (restaurantId, categoryId, data) =>
      http().put(`/api/restaurants/${restaurantId}/menu-categories/${categoryId}`, data),
    deleteCategory: (restaurantId, categoryId) =>
      http().delete(`/api/restaurants/${restaurantId}/menu-categories/${categoryId}`),
    createItem: (restaurantId, categoryId, data, imageFile = null) => {
      const url = `/api/restaurants/${restaurantId}/menu-categories/${categoryId}/items`;
      if (imageFile) {
        const formData = new FormData();
        formData.append('name', data.name);
        formData.append('description', data.description ?? '');
        formData.append('price', String(data.price));
        formData.append('is_available', data.is_available ? '1' : '0');
        formData.append('image', imageFile);
        return http().post(url, formData);
      }
      return http().post(url, data);
    },
    updateItem: (restaurantId, itemId, data, imageFile = null) => {
      const url = `/api/restaurants/${restaurantId}/menu-items/${itemId}`;
      if (imageFile) {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', data.name ?? '');
        formData.append('description', data.description ?? '');
        formData.append('price', String(data.price ?? ''));
        formData.append('is_available', data.is_available ? '1' : '0');
        formData.append('sort_order', String(data.sort_order ?? '0'));
        formData.append('image', imageFile);
        return http().post(url, formData);
      }
      return http().put(url, data);
    },
    deleteItem: (restaurantId, itemId) =>
      http().delete(`/api/restaurants/${restaurantId}/menu-items/${itemId}`),
  },
};

export default API;
