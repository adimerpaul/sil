import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_BACK,
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('tokenSil')
  if (token) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default http

