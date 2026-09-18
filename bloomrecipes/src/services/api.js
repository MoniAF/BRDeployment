import axios from 'axios'

export const BACKEND_URL =
  import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

export const AUTH_TOKEN_KEY = 'bloom_token'

const api = axios.create({
  baseURL: `${BACKEND_URL}/`,
  timeout: 15000,
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(AUTH_TOKEN_KEY)

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export default api