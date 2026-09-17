<script setup>
import { useAuthStore } from '../stores/auth'
import { useFavoritesStore } from '../stores/favorites'

defineEmits(['showdetails'])

const authStore = useAuthStore()
const favoritesStore = useFavoritesStore()
</script>

<template>
  <div id="favoritesModal" class="modal fade" tabindex="-1" aria-labelledby="favoritesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal modal-dialog-scrollable">
      <div class="modal-content modal-design">
        <div class="modal-header header-mc">

          <h5 id="favoritesModalLabel" class="modal-title">Favorites</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

        </div>

        <div class="modal-body">

          <p v-if="!authStore.isAuthenticated" class="text-center">Please log in to see your favorites.</p>

          <p v-else-if="favoritesStore.loading" class="text-center" role="status">Loading favorites...</p>

          <div v-else-if="favoritesStore.error" class="text-center" role="alert">
            <p>{{ favoritesStore.error }}</p>
            <button type="button" class="btn-cmodal" @click="favoritesStore.load()">Reload favorites</button>
          </div>

          <p v-else-if="favoritesStore.recipes.length === 0" class="text-center">You haven't saved any recipes yet.</p>

          <div v-else class="d-flex justify-content-center flex-wrap">
            <div v-for="element in favoritesStore.recipes" :key="element.id" class="size-card">
              <section class="d-flex cards-modal justify-content-center mm-modal">
                <div class="conf-cards">
                  <div class="card-top">

                    <SaveRecipeButton :recipe-id="element.id" class="card-save-button"/>

                    <LikeRecipeButton :recipe-id="element.id" :likes="element.likes" :show-count="false" class="card-like-button"/>

                    <section class="img-csz">
                      <img :src="element.image" :alt="element.name" class="img-card"/>
                    </section>

                    <div class="degraded"></div>

                    <section class="d-flex justify-content-center">
                      <div class="info-top">

                        <p class="title-card text-center">
                          <button type="button" class="recipe-title-button" data-bs-dismiss="modal" @click="$emit('showdetails', element.id)">{{ element.name }}</button>
                        </p>

                        <p class="category-card text-center categories-txt">{{ element.category }}</p>

                        <p class="category-card text-center categories-txt">{{ element.difficulty?.replace(/\s+recipes$/i, '') }}</p>

                      </div>
                    </section>

                  </div>
                </div>
              </section>
            </div>
          </div>
          
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cmodal" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>
</template>