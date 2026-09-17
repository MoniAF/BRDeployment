<script setup>
import { computed } from 'vue'

const props = defineProps({
  recipes: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  }
})

const emit = defineEmits(['showdetails', 'retry'])

const newestRecipes = computed(() => props.recipes.slice(-5))
</script>

<template>
  <div id="newestModal" class="modal fade" tabindex="-1" aria-labelledby="newestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal modal-dialog-scrollable">
      <div class="modal-content modal-design">

        <div class="modal-header header-mc">
          <h5 id="newestModalLabel" class="modal-title"> Our newest recipes </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          
          <p v-if="loading" class="text-center py-4" role="status"> Loading recipes... </p>

          <div v-else-if="error" class="text-center py-4" role="alert">
            <p>{{ error }}</p>
            <button type="button" class="btn btn-outline-dark" @click="emit('retry')"> Try again </button>
          </div>

          <p v-else-if="!newestRecipes.length" class="text-center py-4"> No recipes available. </p>

          <div v-else class="d-fb cards-modal justify-content-between">
            <div v-for="element in newestRecipes" :key="element.id" class="size-card">
              <div class="conf-cards">
                <div class="card-pp">

                  <SaveRecipeButton :recipe-id="element.id" class="card-save-button"/>

                  <LikeRecipeButton :recipe-id="element.id" :likes="element.likes" :show-count="false" class="card-like-button"/>
                  
                  <section class="img-csz">
                    <img v-if="element.image" :src="element.image" :alt="element.name" class="img-card"/>
                  </section>

                  <div class="degraded"></div>

                  <section class="d-flex justify-content-center">
                    <div class="info-top">
                      <p class="title-card text-center">
                        <button type="button" class="recipe-title-button" data-bs-dismiss="modal" @click="emit('showdetails', element.id)"> {{ element.name }} </button>
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

        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cmodal" data-bs-dismiss="modal"> Close </button>
        </div>
        
      </div>
    </div>
  </div>
</template>