<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import { formatRecipe } from '../stores/recipes'

const emit = defineEmits(['showdetails'])

const route = useRoute()

const results = ref([])
const loading = ref(false)
const errorMessage = ref('')
const retryCount = ref(0)

const term = computed(() =>
  typeof route.query.q === 'string'
    ? route.query.q.trim()
    : ''
)

const message = computed(() =>
  results.value.length
    ? 'Search results for'
    : "Sorry, we couldn't find any recipes that match"
)

watch(
  [term, retryCount],
  async ([searchTerm], previousValue, onCleanup) => {
    results.value = []
    errorMessage.value = ''
    loading.value = false

    if (!searchTerm) return

    const controller = new AbortController()

    onCleanup(() => controller.abort())

    loading.value = true

    try {
      const { data } = await api.get(
        `recipes/searchbyname/${encodeURIComponent(searchTerm)}`,
        {
          signal: controller.signal,
        }
      )

      if (controller.signal.aborted) return

      if (!Array.isArray(data)) {
        throw new Error(
          'Unexpected search response: expected a recipe array.'
        )
      }

      results.value = data.map(formatRecipe)
    } catch (error) {
      if (controller.signal.aborted) return

      errorMessage.value =
        'We could not complete your search. Please try again.'

      console.error('Error searching recipes:', error.message)
    } finally {
      if (!controller.signal.aborted) {
        loading.value = false
      }
    }
  },
  { immediate: true }
)
</script>

<template>
  <main class="container-principal">
    
    <p v-if="!term" class="text-center py-4"> Enter a recipe name in the search bar. </p>
    <p v-else-if="loading" class="text-center py-4" role="status"> Searching recipes... </p>

    <div v-else-if="errorMessage" class="py-4" role="alert">
      <p>{{ errorMessage }}</p>
      <button type="button" class="btn btn-outline-dark" @click="retryCount++"> Try again </button>
    </div>

    <template v-else>
      <div v-if="!results.length" class="d-flex justify-content-center align-items-center mt-5">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-exclamation-circle icon-msg text-primary" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
      </div>

      <section class="d-flex justify-content-center" aria-live="polite">
        <p class="txt-option"> {{ message }} '<span class="txt-value">{{ term }}</span>'.</p>
      </section>

      <div class="d-flex cards-recipes">
        <div v-for="element in results" :key="element.id" class="size-card">
          <div class="conf-cards mb-4">
            <div class="card-pp">

              <SaveRecipeButton :recipe-id="element.id" class="card-save-button"/>

              <LikeRecipeButton :recipe-id="element.id" :likes="element.likes" :show-count="false" class="card-like-button"/>
              
              <section class="img-csz">
                <img v-if="element.image" :src="element.image" class="img-card" :alt="element.name">
              </section>

              <div class="degraded"></div>

              <section class="d-flex justify-content-center">
                <div class="info-top">
                  <p class="title-card text-center">
                    <button type="button" class="recipe-title-button" @click="emit('showdetails', element.id)"> {{ element.name }} </button>
                  </p>
                  <p class="description-card txt-description"> {{ element.description }} </p>
                  <p class="category-card text-center categories-txt"> {{ element.category }} </p>
                  <p class="category-card text-center categories-txt"> {{ element.difficulty?.replace(/\s+recipes$/i, '') }} </p>
                </div>
              </section>

            </div>
          </div>
        </div>
      </div>

    </template>
    
  </main>
</template>