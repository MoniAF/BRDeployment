<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useFavoritesStore } from '../stores/favorites'

const props = defineProps({
  recipeId: {
    type: [Number, String],
    required: true,
  }
})

const authStore = useAuthStore()
const favoritesStore = useFavoritesStore()

const saved = computed(() => favoritesStore.isSaved(props.recipeId))

const label = computed(() =>
  saved.value ? 'Remove from favorites' : 'Save to favorites',
)
</script>

<template>

  <button v-if="authStore.isAuthenticated" type="button" class="save-recipe-button" :class="{ 'is-saved': saved }" :aria-label="label" :title="label" :aria-pressed="saved" :disabled=" favoritesStore.loading || !favoritesStore.loaded || favoritesStore.busyId !== null" @click.stop="favoritesStore.toggle(recipeId)"><svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" :fill="saved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4V3Z" /></svg></button>

</template>

<style scoped>
  .save-recipe-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem;
    margin: 0.4rem;
    border: 0;
    border-radius: 50%;
    background: #eff9f9;
    color: #18999f;
    cursor: pointer;
  }

  .save-recipe-button.is-saved {
    color: #b84308;
  }

  .save-recipe-button:disabled {
    opacity: 0.55;
    cursor: default;
  }

  .save-recipe-button:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 3px;
  }

  .save-recipe-button.save-recipe-large {
    color: #000000;
    border: 3px solid #1f8e8e;
    width: 60px;
    height: 60px;
  }

  .save-recipe-button.save-recipe-large svg {
    width: 34px;
    height: 34px;
  }

  .save-recipe-button.save-recipe-large.is-saved {
    background-color: #b84308;
    color: #fff;
    border: none;
  }
</style>