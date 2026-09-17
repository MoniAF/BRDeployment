<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '../services/api'
import { formatRecipe } from '../stores/recipes'
import { useFiltersStore } from '../stores/filters'

const props = defineProps({
  recipes: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['showdetails'])

const route = useRoute()
const filtersStore = useFiltersStore()

const filteredRecipes = ref([])
const loading = ref(false)
const errorMessage = ref('')
const retryCount = ref(0)

const hasFilter = computed(() =>
  route.query.filter !== undefined ||
  route.query.value !== undefined
)

const displayedRecipes = computed(() =>
  hasFilter.value ? filteredRecipes.value : props.recipes
)

const heading = computed(() => {
  if (!hasFilter.value) return 'All Recipes'

  const group = filtersStore.groups.find(
    (item) => item.filter === route.query.filter
  )

  const option = group?.options.find(
    (item) => String(item.id) === route.query.value
  )

  return option
    ? `${group.title}: ${option.label}`
    : 'Filtered Recipes'
})

watch(
  [
    () => route.query.filter,
    () => route.query.value,
    retryCount,
  ],
  async ([filter, value], previousValue, onCleanup) => {
    filteredRecipes.value = []
    errorMessage.value = ''
    loading.value = false

    if (!hasFilter.value) return

    const allowedFilters = ['level', 'category', 'occasion']

    if (
      typeof filter !== 'string' ||
      !allowedFilters.includes(filter) ||
      typeof value !== 'string' ||
      !/^[1-9]\d*$/.test(value)
    ) {
      errorMessage.value = 'Invalid recipe filter.'
      return
    }

    const controller = new AbortController()

    onCleanup(() => controller.abort())

    loading.value = true

    try {
      const { data } = await api.get(
        `recipes/filterby/${filter}/${value}`,
        {
          signal: controller.signal,
        }
      )

      if (controller.signal.aborted) return

      if (!Array.isArray(data)) {
        throw new Error(
          'Unexpected filter response: expected a recipe array.'
        )
      }

      filteredRecipes.value = data.map(formatRecipe)
    } catch (error) {
      if (controller.signal.aborted) return

      errorMessage.value =
        'We could not load these recipes. Please try again.'

      console.error('Error filtering recipes:', error.message)
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

    <section class="d-flex justify-content-center">
      <p class="txt-value"> {{ heading }} </p>
    </section>

    <div v-if="hasFilter" class="mb-3">
      <RouterLink :to="{ name: 'recipes' }" class="link-more"> View all recipes </RouterLink>
    </div>

    <p v-if="loading" class="text-center py-4" role="status"> Loading recipes... </p>

    <div v-else-if="errorMessage" class="py-4" role="alert">
      <p> {{ errorMessage }} </p>
      <button type="button" class="btn btn-outline-dark" @click="retryCount++"> Try again </button>
    </div>

    <p v-else-if="!displayedRecipes.length" class="text-center py-4"> No recipes found. </p>

    <div v-else class="d-flex cards-recipes">
      <div v-for="element in displayedRecipes" :key="element.id" class="size-card">
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
    
  </main>
</template>