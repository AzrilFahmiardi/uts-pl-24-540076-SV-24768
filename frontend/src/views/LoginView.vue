<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const form = reactive({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', form)
    localStorage.setItem('token', data.token)
    router.push({ name: 'equipment' })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Tidak dapat terhubung ke server.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="card login">
    <h1>Inventaris Alat Lab</h1>
    <p class="muted">Masuk untuk mengelola data alat.</p>
    <form @submit.prevent="submit">
      <label>Email
        <input v-model="form.email" type="email" required autocomplete="username" />
      </label>
      <label>Password
        <input v-model="form.password" type="password" required autocomplete="current-password" />
      </label>
      <p v-if="error" class="error">{{ error }}</p>
      <button :disabled="loading">{{ loading ? 'Memproses...' : 'Masuk' }}</button>
    </form>
  </main>
</template>
