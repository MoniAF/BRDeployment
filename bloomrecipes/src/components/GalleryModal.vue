<script setup>
defineProps({
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
  },
})

const emit = defineEmits(['retry'])
</script>

<template>
  <div id="imagesModal" class="modal fade" tabindex="-1" aria-labelledby="imagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered custom-modal modal-dialog-scrollable">
      <div class="modal-content modal-design">
        
        <div class="modal-header header-mc hd-gallery">

          <h5 id="imagesModalLabel" class="modal-title ms-3 txt-wb">Gallery</h5>

          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

        </div>

        <div class="modal-body">
          
          <p v-if="loading" class="text-center py-4" role="status">Loading gallery...</p>

          <div v-else-if="error" class="text-center py-4" role="alert">
            <p>{{ error }}</p>
            <button type="button" class="btn btn-outline-dark" @click="emit('retry')">Try again</button>
          </div>

          <p v-else-if="!recipes.length" class="text-center py-4">No recipes available for the gallery.</p>

          <div v-else class="d-flex justify-content-center flex-wrap align-items-center mt-4 mb-4 pd-gallery">
            <section v-for="(element, index) in recipes" :key="element.id">
             
              <section v-if="index % 7 < 3">
                <div class="one-box">
                  <img :src="element.image" :alt="element.name" class="image-one">
                </div>
              </section>

              <section v-else>
                <div class="two-box">
                  <img :src="element.image" :alt="element.name" class="image-two">
                </div>
              </section>

            </section>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cmodal btnc-gallery" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>
</template>