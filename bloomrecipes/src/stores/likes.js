import { defineStore } from 'pinia'
import api from '../services/api'
import { useAuthStore } from './auth'

export const useLikesStore = defineStore('likes', {
  state: () => ({
    likedIds: [],
    counts: {},
    loading: false,
    loaded: false,
    busyId: null,
    error: '',
  }),

  getters: {
    isLiked: (state) => (id) =>
      state.likedIds.includes(String(id)),

    countFor: (state) => (id, fallback = 0) =>
      state.counts[String(id)] ?? Number(fallback ?? 0),
  },

  actions: {
    resetSession() {
      this.likedIds = []
      this.loading = false
      this.loaded = false
      this.busyId = null
      this.error = ''
    },

    async load() {
      const auth = useAuthStore()

      if (!auth.isAuthenticated || this.loading) return

      const token = auth.token
      this.loading = true
      this.loaded = false
      this.error = ''

      try {
        const { data } = await api.get('users/likedrecipes')

        if (auth.token !== token || !auth.isAuthenticated) return

        if (!Array.isArray(data.recipe_ids)) {
          throw new Error('Unexpected liked recipes response.')
        }

        this.likedIds = data.recipe_ids.map(String)
        this.loaded = true
      } catch (error) {
        if (auth.token !== token || !auth.isAuthenticated) return

        if (error.response?.status === 401) {
          auth.clearSession()
          auth.sessionError = 'Your session has expired. Please log in again.'
        } else {
          this.error = 'We could not load your likes. Please try again.'
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
      const key = String(id)
      const wasLiked = this.isLiked(id)

      this.busyId = key
      this.error = ''

      try {
        const { data } = wasLiked
          ? await api.delete(`users/dislikes/${id}`)
          : await api.post(`users/likes/${id}`)

        if (auth.token !== token || !auth.isAuthenticated) return

        if (
          typeof data.liked !== 'boolean' ||
          typeof data.likes !== 'number' ||
          !Number.isFinite(data.likes)
        ) {
          throw new Error('Unexpected like response.')
        }

        this.counts[key] = data.likes
        this.likedIds = this.likedIds.filter((value) => value !== key)

        if (data.liked) {
          this.likedIds.push(key)
        }
      } catch (error) {
        if (auth.token !== token || !auth.isAuthenticated) return

        if (error.response?.status === 401) {
          auth.clearSession()
          auth.sessionError = 'Your session has expired. Please log in again.'
        } else {
          this.loaded = false
          this.error =
            'We could not confirm the change. Reload your likes before trying again.'
        }
      } finally {
        if (auth.token === token) {
          this.busyId = null
        }
      }
    },
  },
})