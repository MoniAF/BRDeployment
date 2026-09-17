<script setup>
import { computed, onMounted, watch } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import NavBar from './components/NavBar.vue'
import { useRecipesStore } from './stores/recipes'
import GalleryModal from './components/GalleryModal.vue'
import NewestRecipesModal from './components/NewestRecipesModal.vue'
import FooterPages from './components/FooterPages.vue'
import { useAuthStore } from './stores/auth'
import FavoritesModal from './components/FavoritesModal.vue'
import { useFavoritesStore } from './stores/favorites'
import { useLikesStore } from './stores/likes'

const recipesStore = useRecipesStore()
const authStore = useAuthStore()
const likesStore = useLikesStore()

watch(
  () => (authStore.isAuthenticated ? authStore.token : ''),
  (token) => {
    likesStore.resetSession()

    if (token) {
      likesStore.load()
    }
  },
  { immediate: true },
)

const favoritesStore = useFavoritesStore()

watch(
  () => (authStore.isAuthenticated ? authStore.token : ''),
  (token) => {
    favoritesStore.$reset()

    if (token) {
      favoritesStore.load()
    }
  },
  { immediate: true },
)

const route = useRoute()
const router = useRouter()

const usesCatalog = computed(() =>
  ['home', 'recipes'].includes(route.name)
)

const viewProps = computed(() => {
  if (route.name === 'home') {
    return {
      recipes: recipesStore.recipes,
      trendings: recipesStore.trendings,
    }
  }

  if (route.name === 'recipes') {
    return {
      recipes: recipesStore.recipes,
    }
  }

  return {}
})

function openRecipe(id) {
  router.push({
    name: 'recipe-details',
    params: { id: String(id) },
  })
}

function openHome() {
  router.push({ name: 'home' })
}

function openRecipes() {
  router.push({ name: 'recipes' })
}

function openFilteredRecipes(filter, value) {
  router.push({
    name: 'recipes',
    query: {
      filter,
      value: String(value),
    },
  })
}

function openSearchResults(value) {
  const term = value.trim()

  if (!term) return

  router.push({
    name: 'search',
    query: {
      q: term,
    },
  })
}

async function handleLogout() {
  const success = await authStore.logout()

  if (success) {
    router.push({ name: 'home' })
  }
}

onMounted(() => {
  recipesStore.loadHome()
  authStore.restoreSession()
})
</script>

<template>
  <div :class="{ 'secondary-layout': route.name !== 'home' }">

    <NavBar v-if="!['login', 'register'].includes(route.name)" :trendings="recipesStore.trendings" :online="authStore.isAuthenticated" :username="authStore.user?.name || ''" :name="authStore.fullName" :email="authStore.user?.email || ''" @showdetails="openRecipe" @openhome="openHome" @openrecipes="openRecipes" @showlevels="(id) => openFilteredRecipes('level', id)" @showcategories="(id) => openFilteredRecipes('category', id)" @showoccasions="(id) => openFilteredRecipes('occasion', id)" @searchrecipes="openSearchResults" @logout="handleLogout"/>

    <div v-if="authStore.isAuthenticated && likesStore.error" class="container-principal py-3" role="alert">
      <p class="text-danger">{{ likesStore.error }}</p>
      <button type="button" class="btn-cmodal" :disabled="likesStore.loading || likesStore.busyId !== null" @click="likesStore.load()"> Reload likes </button>
    </div>

    <p v-if="authStore.sessionError" class="container-principal text-danger py-3" role="alert"> {{ authStore.sessionError }} </p>

    <GalleryModal :recipes="recipesStore.recipes" :loading="recipesStore.loading" :error="recipesStore.error" @retry="recipesStore.loadHome()"/>

    <NewestRecipesModal :recipes="recipesStore.recipes" :loading="recipesStore.loading" :error="recipesStore.error" @showdetails="openRecipe" @retry="recipesStore.loadHome()"/>

    <FavoritesModal @showdetails="openRecipe"/>

    <p v-if="usesCatalog && recipesStore.loading" class="container-principal py-4" role="status"> Loading recipes... </p>

    <div v-else-if="usesCatalog && recipesStore.error" class="container-principal py-4" role="alert">
      <p>{{ recipesStore.error }}</p>
      <button type="button" class="btn btn-outline-dark" @click="recipesStore.loadHome()"> Try again </button>
    </div>

    <RouterView v-else v-slot="{ Component }">
      <component :is="Component" v-bind="viewProps" @showdetails="openRecipe" @openrecipes="openRecipes"/>
    </RouterView>

    <FooterPages v-if="['recipes', 'recipe-details', 'search'].includes(route.name)" class="secondary-footer"/>

  </div>
</template>

<style scoped>
  .secondary-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .secondary-footer {
    margin-top: auto;
    flex-shrink: 0;
  }
</style>