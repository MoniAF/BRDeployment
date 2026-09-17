import { defineStore } from 'pinia'
import api from '../services/api'

const definitions = [
  {
    filter: 'level',
    title: 'Difficulty',
    endpoint: 'levels',
    field: 'level',
    event: 'showlevels',
  },
  {
    filter: 'category',
    title: 'Category',
    endpoint: 'categories',
    field: 'category',
    event: 'showcategories',
  },
  {
    filter: 'occasion',
    title: 'Occasion',
    endpoint: 'occasions',
    field: 'occasion',
    event: 'showoccasions',
  },
]

export const useFiltersStore = defineStore('filters', {
  state: () => ({
    groups: [],
    loading: false,
    loaded: false,
    error: '',
  }),

  actions: {
    async loadFilters() {
      if (this.loading || this.loaded) return

      this.loading = true
      this.error = ''

      try {
        const responses = await Promise.all(
          definitions.map((group) =>
            api.get(`recipes/${group.endpoint}`)
          )
        )

        this.groups = definitions.map((group, index) => {
          const items = responses[index].data

          if (!Array.isArray(items)) {
            throw new Error(
              `Unexpected response for ${group.endpoint}: expected an array.`
            )
          }

          return {
            ...group,
            options: items.map((item) => ({
              id: item.id,
              label: item[group.field],
            })),
          }
        })

        this.loaded = true
      } catch (error) {
        this.error = 'We could not load the filters.'
        console.error('Error loading filters:', error.message)
      } finally {
        this.loading = false
      }
    },
  },
})