<script setup>
import { reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  last_name: '',
  country: 'Costa Rica',
  email: '',
  password: '',
})

const errorMessage = ref('')

watch(
  () => authStore.isAuthenticated,
  (authenticated) => {
    if (authenticated) {
      router.replace({ name: 'home' })
    }
  },
  { immediate: true },
)

async function submitRegister() {
  if (authStore.busy || authStore.restoring) return

  errorMessage.value = ''

  try {
    await authStore.register(form)
  } catch (error) {
    const status = error.response?.status

    if (status === 422) {
      const errors = error.response?.data?.errors || {}

      errorMessage.value =
        Object.values(errors).flat()[0] ||
        'Please check the information entered.'
    } else if (status === 429) {
      errorMessage.value = 'Too many attempts. Please try again later.'
    } else {
      errorMessage.value =
        'We could not create your account. Please try again.'
    }
  } finally {
    form.password = ''
  }
}
</script>

<template>
  <main class="container-register m-auto">

    <section class="text-center">
      <p class="bu-icon"> BLOOM<br><span class="ru-icon">RECIPES</span></p>
    </section>

    <form class="mt-5" @submit.prevent="submitRegister">

      <p class="title-user justify-content-center">Sign Up</p>

      <section class="d-flex justify-content-center m-input">
        <div class="icon-log d-flex justify-content-center align-items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-person-fill size-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
        </div>
        <input v-model="form.name" class="form-control input-log txt-input" type="text" name="name" placeholder="First name" aria-label="First name" autocomplete="given-name" maxlength="255" required>
      </section>

      <section class="d-flex justify-content-center m-input">
        <div class="icon-log d-flex justify-content-center align-items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-person-fill size-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
        </div>
        <input v-model="form.last_name" class="form-control input-log txt-input" type="text" name="last_name" placeholder="Last name" aria-label="Last name" autocomplete="family-name" maxlength="255" required>
      </section>

      <section class="d-flex justify-content-center m-input">
        <div class="icon-log d-flex justify-content-center align-items-center">
          <span class="material-symbols-outlined size-icon" aria-hidden="true"> public </span>
        </div>
        <input v-model="form.country" class="form-control input-log txt-input" type="text" name="country" placeholder="Country" aria-label="Country" autocomplete="country-name" maxlength="255" required>
      </section>

      <section class="d-flex justify-content-center m-input">
        <div class="icon-log d-flex justify-content-center align-items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-envelope-at size-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2H2Zm3.708 6.208L1 11.105V5.383l4.708 2.825ZM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2-7-4.2Z"/><path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648Zm-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/></svg>
        </div>
        <input v-model="form.email" class="form-control input-log txt-input" type="email" name="email" placeholder="E-mail" aria-label="E-mail" autocomplete="email" maxlength="255" required>
      </section>

      <section class="d-flex justify-content-center m-input">
        <div class="icon-log d-flex justify-content-center align-items-center">
          <span class="material-symbols-outlined size-icon" aria-hidden="true">
            lock
          </span>
        </div>
        <input v-model="form.password" class="form-control input-log txt-input" type="password" name="password" placeholder="Password" aria-label="Password" aria-describedby="register-password-help" autocomplete="new-password" minlength="8" required>
      </section>

      <p id="register-password-help" class="text-center"> Use at least 8 characters. </p>

      <p v-if="errorMessage" class="text-danger text-center" role="alert"> {{ errorMessage }} </p>
      <p v-if="authStore.restoring" class="text-center" role="status"> Checking your session... </p>

      <section class="d-flex justify-content-center m-input">
        <button type="submit" class="btn-next" :disabled="authStore.busy || authStore.restoring"> {{ authStore.busy ? 'Creating...' : 'Create' }} </button>
      </section>

      <div class="d-flex justify-content-center mb-2">
        <p class="txt-question">Already have an account?</p>
        <RouterLink :to="{ name: 'login' }" class="btn-sign text-center text-decoration-none"> Log In </RouterLink>
      </div>

    </form>

  </main>
</template>