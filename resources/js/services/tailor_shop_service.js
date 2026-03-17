import axios from 'axios';

const API_URL = '/api/admin';

export const getTailorShops = () => {
  return axios.get(`${API_URL}/tailor-shops`);
};

export const getTailorShop = (id) => {
  return axios.get(`${API_URL}/tailor-shop/${id}`);
};

export const createTailorShop = (data) => {
  return axios.post(`${API_URL}/tailor-shop`, data);
};

export const updateTailorShop = (id, data) => {
  return axios.put(`${API_URL}/tailor-shop/${id}`, data);
};

export const deleteTailorShop = (id) => {
  return axios.delete(`${API_URL}/tailor-shop/${id}`);
}; 