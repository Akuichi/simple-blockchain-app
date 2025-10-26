import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api/v1';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Transaction endpoints
export const createTransaction = (data) => api.post('/transaction', data);
export const getPendingTransactions = () => api.get('/transactions/pending');
export const getAllTransactions = () => api.get('/transactions');

// Block endpoints
export const mineBlock = () => api.post('/block/mine');
export const getAllBlocks = () => api.get('/blocks');
export const getBlockById = (id) => api.get(`/blocks/${id}`);

// Blockchain endpoints
export const validateBlockchain = () => api.get('/blockchain/validate');
export const getBlockchainStats = () => api.get('/blockchain/stats');

export default api;
