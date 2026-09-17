<script setup>
import { computed, ref, watch } from 'vue'
import api, { BACKEND_URL } from '../services/api'
import { useFavoritesStore } from '../stores/favorites'
import { useLikesStore } from '../stores/likes'

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['showdetails'])

const recipe = ref(null)
const ingredients = ref([])
const relatedRecipes = ref([])
const loading = ref(true)
const errorMessage = ref('')
const retryCount = ref(0)
const favoritesStore = useFavoritesStore()
const likesStore = useLikesStore()

function imageUrl(image) {
  return image
    ? new URL(image, `${BACKEND_URL}/storage/imgs/`).href
    : null
}

const instructions = computed(() => {
  const text = recipe.value?.preparation_instructions ?? ''

  return text
    .replace(/([.,]\s)(Step)/g, '$1*$2')
    .split(/\*|\r?\n/)
    .map((step) => step.trim())
    .filter(Boolean)
})

watch(
  [() => props.id, retryCount],
  async ([id], previousValue, onCleanup) => {
    const controller = new AbortController()

    onCleanup(() => controller.abort())

    loading.value = true
    errorMessage.value = ''
    recipe.value = null
    ingredients.value = []
    relatedRecipes.value = []

    try {
      if (!/^[1-9]\d*$/.test(id)) {
        errorMessage.value = 'Recipe not found.'
        return
      }

      const { data } = await api.get(`recipes/recipe/${id}`, {
        signal: controller.signal,
      })

      if (controller.signal.aborted) return

      if (
        !Array.isArray(data) ||
        data.length < 3 ||
        !data.slice(0, 3).every(Array.isArray)
      ) {
        throw new Error(
          'Unexpected detail response: expected [recipes, ingredients, related].'
        )
      }

      const [recipeRows, ingredientRows, relatedRows] = data
      const item = recipeRows[0]

      if (!item) {
        errorMessage.value = 'Recipe not found.'
        return
      }

      recipe.value = {
        ...item,
        image: imageUrl(item.image),
      }

      ingredients.value = ingredientRows.map((ingredient) =>
        [
          ingredient.amount,
          ingredient.measurement_unit,
          ingredient.description,
        ]
          .filter((value) => value !== null && value !== undefined && value !== '')
          .join(' ')
      )

      relatedRecipes.value = relatedRows.slice(0, 3).map((item) => ({
        ...item,
        image: imageUrl(item.image),
      }))
    } catch (error) {
      if (controller.signal.aborted) return

      errorMessage.value =
        error.response?.status === 404
          ? 'Recipe not found.'
          : 'We could not load this recipe. Please try again.'

      console.error('Error loading recipe details:', error.message)
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
  <main class="container-details">
    
    <p v-if="loading" class="py-4" role="status"> Loading recipe... </p>

    <div v-else-if="errorMessage" class="py-4" role="alert">
      <p>{{ errorMessage }}</p>
      <button type="button" class="btn btn-outline-dark" @click="retryCount++"> Try again </button>
    </div>

    <template v-else-if="recipe">

      <section>
        <div class="details-name d-flex align-items-center justify-content-center">
          <p class="name-dt"> {{ recipe.name }} </p>
        </div>
        <img v-if="recipe.image" :src="recipe.image" class="img-details" :alt="recipe.name">
      </section>

      <div class="m-details">
        <section class="d-fbd justify-content-center dt-gap">
          <p class="details-text"> Difficulty: <span class="txt-black"> {{ (recipe.level ?? '').replace(/\s+recipes\s*$/i, '') }} </span></p>
          <p class="details-text"> Category: <span class="txt-black"> {{ recipe.category }} </span></p>
          <p class="details-text"> Total time: <span class="txt-black"> {{ recipe.total_time }} min</span></p>
          <p class="details-text"> Servings: <span class="txt-black"> {{ recipe.portions }} </span></p>
        </section>

        <section class="d-fbd justify-content-center dt-gap">
          <p class="details-text"> Prep time: <span class="txt-black"> {{ recipe.preparation_time }} min</span></p>
          <p class="details-text"> Cook time: <span class="txt-black"> {{ recipe.cooking_time }} min</span></p>
          <p class="details-text"> Occasion: <span class="txt-black"> {{ recipe.occasion }} </span></p>
          <p class="details-text m-dtk">
            <LikeRecipeButton :recipe-id="recipe.id" :likes="recipe.likes" class="recipe-like-large txt-black"/>
          </p>
        </section>
      </div>

      <div class="d-flex flex-column align-items-center mb-5">
        <SaveRecipeButton :recipe-id="recipe.id" class="save-recipe-large"/>
        <div aria-live="polite">
          <p v-if="favoritesStore.isSaved(recipe.id)" class="details-text txt-black"> Saved </p>
        </div>
      </div>

      <section>
        <h4>Description</h4>
        <p class="txt-recipes mb-5">{{ recipe.description }}</p>

        <h4>Ingredients</h4>
        <div class="mb-ins">
          <p v-for="(ingredient, index) in ingredients" :key="index" class="txt-recipes"> • {{ ingredient }} </p>
          <p v-if="!ingredients.length" class="txt-recipes"> No ingredients available.</p>
        </div>

        <h4>Instructions</h4>
        <section class="mb-ins">
          <p v-for="(instruction, index) in instructions" :key="index" class="txt-recipes"> {{ instruction }} </p>
          <p v-if="!instructions.length" class="txt-recipes"> No instructions available. </p>
        </section>

        <template v-if="relatedRecipes.length">
          <h4>Related recipes</h4>
          <div class="d-flex cards-recipes mb-5">
            <div v-for="element in relatedRecipes" :key="element.id" class="size-card">
              <div class="conf-cards">
                <div class="card-top">

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
                      <p class="category-card text-center categories-txt"> {{ element.category }} </p>
                      <p class="category-card text-center categories-txt"> {{ (element.level ?? '').replace(/\s+recipes\s*$/i, '') }} </p>
                      <p class="txt-likes text-center"><span><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-heart-fill card-heart" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/></svg></span> {{ likesStore.countFor(element.id, element.likes) }} </p>
                    </div>
                  </section>

                </div>
              </div>
            </div>
          </div>
        </template>

      </section>

    </template>
    
  </main>
</template>