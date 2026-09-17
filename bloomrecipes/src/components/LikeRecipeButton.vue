<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useLikesStore } from '../stores/likes'

const props = defineProps({
  recipeId: {
    type: [Number, String],
    required: true,
  },
  likes: {
    type: [Number, String],
    default: 0,
  },
  showCount: {
    type: Boolean,
    default: true,
  },
})

const authStore = useAuthStore()
const likesStore = useLikesStore()

const liked = computed(() => likesStore.isLiked(props.recipeId))

const count = computed(() =>
  likesStore.countFor(props.recipeId, props.likes),
)

const label = computed(() => {
  if (!authStore.isAuthenticated) {
    return `${count.value} likes. Log in to like this recipe.`
  }

  return liked.value
    ? `Remove like. ${count.value} likes.`
    : `Like this recipe. ${count.value} likes.`
})
</script>

<template>
  <button v-if="authStore.isAuthenticated || props.showCount" type="button" class="recipe-like-button" :class="{ 'is-liked': liked }" :aria-label="label" :aria-pressed="authStore.isAuthenticated ? liked : undefined" :title="label" :disabled=" !authStore.isAuthenticated || likesStore.loading || !likesStore.loaded || likesStore.busyId !== null " @click.stop="likesStore.toggle(recipeId)">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="liked || !authStore.isAuthenticated ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg><span v-if="showCount">{{ count }}</span>
  </button>
</template>

<style scoped>
  .recipe-like-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    padding: 0;
    border: 0;
    background: transparent;
    color: inherit;
    font: inherit;
    cursor: pointer;
  }

  .recipe-like-button svg {
    width: 23px;
    height: 23px;
    color: #18999f;
  }

  .recipe-like-button.is-liked svg {
    color: #b84308;
  }

  .recipe-like-button:disabled {
    cursor: default;
  }

  .recipe-like-button:focus-visible {
    outline: 2px solid #18999f;
    outline-offset: 4px;
    border-radius: 4px;
  }

  .recipe-like-button.recipe-like-large {
    min-height: 60px;
    padding: 0.75rem;
    gap: 0.5rem;
  }

  .recipe-like-button.recipe-like-large svg {
    width: 34px;
    height: 34px;
  }

  .recipe-like-button.card-like-button {
    width: auto;
    height: auto;
    padding: 0.4rem;
    margin: 0.4rem;
    border: 0;
    border-radius: 50%;
    background: #eff9f9;
  }
</style>