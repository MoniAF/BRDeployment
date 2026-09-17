import { defineStore } from 'pinia'
import api, { AUTH_TOKEN_KEY } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(AUTH_TOKEN_KEY) || '',
    user: null,
    busy: false,
    restoring: false,
    sessionError: '',
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),

    fullName: (state) =>
      [state.user?.name, state.user?.last_name]
        .filter(Boolean)
        .join(' '),
  },

  actions: {
    clearSession() {
      localStorage.removeItem(AUTH_TOKEN_KEY)
      this.token = ''
      this.user = null
      this.sessionError = ''
    },

    async register(form) {
        if (this.busy || this.restoring) return false

        this.busy = true
        this.sessionError = ''

        try {
            const { data } = await api.post('users/register', {
            name: form.name.trim(),
            last_name: form.last_name.trim(),
            country: form.country.trim(),
            email: form.email.trim(),
            password: form.password,
            })

            if (
            typeof data.access_token !== 'string' ||
            !data.access_token ||
            !data.user?.id
            ) {
            throw new Error('Unexpected registration response.')
            }

            localStorage.setItem(AUTH_TOKEN_KEY, data.access_token)
            this.token = data.access_token
            this.user = data.user

            return true
        } finally {
            this.busy = false
        }
    },

    async login(email, password) {
      if (this.busy || this.restoring) return false

      this.busy = true
      this.sessionError = ''

      try {
        const { data } = await api.post('users/login', {
          email,
          password,
        })

        if (
          typeof data.access_token !== 'string' ||
          !data.access_token ||
          !data.user?.id
        ) {
          throw new Error('Unexpected login response.')
        }

        localStorage.setItem(AUTH_TOKEN_KEY, data.access_token)

        this.token = data.access_token
        this.user = data.user

        return true
      } finally {
        this.busy = false
      }
    },

    async restoreSession() {
      if (!this.token || this.restoring) return

      const previousToken = this.token
      this.restoring = true
      this.sessionError = ''

      try {
        const { data } = await api.get('user')

        if (this.token !== previousToken) return

        if (!data?.id) {
          throw new Error('Unexpected user response.')
        }

        this.user = data
      } catch (error) {
        if (this.token !== previousToken) return

        if (error.response?.status === 401) {
          this.clearSession()
          this.sessionError = 'Your session has expired. Please log in again.'
        } else {
          this.sessionError =
            'We could not restore your session. Please try logging in again.'
        }
      } finally {
        this.restoring = false
      }
    },

    async logout() {
      if (this.busy) return false

      this.busy = true
      this.sessionError = ''

      try {
        await api.post('users/logout')

        this.clearSession()
        return true
      } catch (error) {
        if (error.response?.status === 401) {
          this.clearSession()
          return true
        }

        this.sessionError =
          'We could not log you out. Please try again.'

        return false
      } finally {
        this.busy = false
      }
    },
  },
})