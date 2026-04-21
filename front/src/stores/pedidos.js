import { defineStore } from 'pinia'
import axios from 'axios'

export const usePedidosStore = defineStore('pedidos', {
  state: () => ({
    pedidos: [],
    selectedPedido: null,
    loading: false,
    pagination: {
      sortBy: 'id',
      descending: true,
      page: 1,
      rowsPerPage: 15,
      rowsNumber: 0,
    },
    filters: {
      date_from: '',
      date_to: '',
      estado: '',
      q: '',
    },
    summary: {
      total_pendientes: 0,
      total_aceptados: 0,
      total_rechazados: 0,
      cantidad: 0,
    },
  }),

  getters: {
    pedidosFiltered: (state) => {
      return state.pedidos
    },
    hasPendientes: (state) => state.summary.total_pendientes > 0,
  },

  actions: {
    async fetchPedidos(params = {}) {
      this.loading = true
      try {
        const response = await axios.get('/api/pedidos', {
          params: {
            page: this.pagination.page,
            rowsPerPage: this.pagination.rowsPerPage,
            ...this.filters,
            ...params,
          },
        })

        this.pedidos = response.data.data || []
        this.pagination.rowsNumber = response.data.total || 0
        this.summary = response.data.summary || {}

        return response.data
      } catch (error) {
        console.error('Error fetching pedidos:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchPedido(id) {
      this.loading = true
      try {
        const response = await axios.get(`/api/pedidos/${id}`)
        this.selectedPedido = response.data
        return response.data
      } catch (error) {
        console.error('Error fetching pedido:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async createPedido(data) {
      this.loading = true
      try {
        const response = await axios.post('/api/pedidos', data)
        this.pedidos.unshift(response.data)
        return response.data
      } catch (error) {
        console.error('Error creating pedido:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePedido(id, data) {
      this.loading = true
      try {
        const response = await axios.put(`/api/pedidos/${id}`, data)
        const index = this.pedidos.findIndex((p) => p.id === id)
        if (index !== -1) {
          this.pedidos[index] = response.data
        }
        this.selectedPedido = response.data
        return response.data
      } catch (error) {
        console.error('Error updating pedido:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async deletePedido(id) {
      this.loading = true
      try {
        await axios.delete(`/api/pedidos/${id}`)
        this.pedidos = this.pedidos.filter((p) => p.id !== id)
      } catch (error) {
        console.error('Error deleting pedido:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
      this.pagination.page = 1
    },

    resetFilters() {
      this.filters = {
        date_from: '',
        date_to: '',
        estado: '',
        q: '',
      }
      this.pagination.page = 1
    },

    updatePagination(pagination) {
      this.pagination = pagination
    },
  },
})
