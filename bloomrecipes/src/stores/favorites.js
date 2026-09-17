import { defineStore } from 'pinia'
import api from '../services/api'
import { useAuthStore } from './auth'
import { formatRecipe } from './recipes'

export const useFavoritesStore = defineStore('favorites', {
  state: () => ({
    recipes: [],
    loading: false,
    loaded: false,
    busyId: null,
    error: '',
  }),

  getters: {
    isSaved: (state) => (id) =>
      state.recipes.some((recipe) => String(recipe.id) === String(id)),
  },

  actions: {
    async load() {
      const auth = useAuthStore()

      if (!auth.isAuthenticated || this.loading) return

      const token = auth.token
      this.loading = true
      this.loaded = false
      this.error = ''

      try {
        const { data } = await api.get('users/savedrecipes')

        if (auth.token !== token || !auth.isAuthenticated) return

        if (!Array.isArray(data)) {
          throw new Error('Unexpected saved recipes response.')
        }

        this.recipes = data.map(formatRecipe)
        this.loaded = true
      } catch (error) {
        if (auth.token !== token || !auth.isAuthenticated) return

        if (error.response?.status === 401) {
          auth.clearSession()
          auth.sessionError = 'Your session has expired. Please log in again.'
        } else {
          this.error = 'We could not load your favorites. Please try again.'
        }
      } finally {
        if (auth.token === token) {
          this.loading = false
        }
      }
    },

    async toggle(id) {
      const auth = useAuthStore()

      if (
        !auth.isAuthenticated ||
        !this.loaded ||
        this.loading ||
        this.busyId !== null
      ) {
        return
      }

      const token = auth.token
      const wasSaved = this.isSaved(id)

      this.busyId = String(id)
      this.error = ''

      try {
        if (wasSaved) {
          await api.delete(`users/removesavedrecipe/${id}`)
        } else {
          await api.post(`users/saverecipe/${id}`)
        }

        if (auth.token !== token || !auth.isAuthenticated) return

        await this.load()
      } catch (error) {
        if (auth.token !== token || !auth.isAuthenticated) return

        if (error.response?.status === 401) {
          auth.clearSession()
          auth.sessionError = 'Your session has expired. Please log in again.'
        } else {
          this.loaded = false
          this.error =
            'We could not confirm the change. Reload your favorites before trying again.'
        }
      } finally {
        if (auth.token === token) {
          this.busyId = null
        }
      }
    },
  },
})