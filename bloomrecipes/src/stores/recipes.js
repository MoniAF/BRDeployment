import { defineStore } from 'pinia'
import api, { BACKEND_URL } from '../services/api'

export function formatRecipe(item) {
  return {
    id: item.id,
    name: item.name,
    description: item.description ?? '',
    category: item.category ?? '',
    difficulty: item.level ?? '',
    likes: item.likes ?? 0,
    image: item.image
      ? new URL(item.image, `${BACKEND_URL}/storage/imgs/`).href
      : null,
  }
}

export const useRecipesStore = defineStore('recipes', {
  state: () => ({
    recipes: [],
    trendings: [],
    loading: false,
    error: '',
  }),

  actions: {
    async loadHome() {
      if (this.loading) return

      this.loading = true
      this.error = ''

      try {
        const [recipesResponse, trendingsResponse] = await Promise.all([
          api.get('recipes/all'),
          api.get('recipes/top10'),
        ])

        if (
          !Array.isArray(recipesResponse.data) ||
          !Array.isArray(trendingsResponse.data)
        ) {
          throw new Error('Unexpected API response: expected recipe arrays.')
        }

        const recipes = recipesResponse.data.map(formatRecipe)
        const trendings = trendingsResponse.data.map(formatRecipe)

        this.recipes = recipes
        this.trendings = trendings
      } catch (error) {
        this.error = 'We could not load the recipes. Please try again.'
        console.error('Error loading recipes:', error.message)
      } finally {
        this.loading = false
      }
    },
  },
})