<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const items = ref([])
const errors = ref({})
const editingId = ref(null)
const conditions = ['baik', 'rusak ringan', 'rusak berat']
const emptyForm = () => ({ name: '', code: '', category: '', condition: 'baik', quantity: 1 })
const form = reactive(emptyForm())

async function load() {
  const { data } = await api.get('/equipment')
  items.value = data.data
}

function reset() {
  Object.assign(form, emptyForm())
  editingId.value = null
  errors.value = {}
}

async function save() {
  errors.value = {}
  try {
    if (editingId.value) await api.put(`/equipment/${editingId.value}`, form)
    else await api.post('/equipment', form)
    reset()
    await load()
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
    else throw e
  }
}

function edit(item) {
  editingId.value = item.id
  Object.assign(form, {
    name: item.name,
    code: item.code,
    category: item.category,
    condition: item.condition,
    quantity: item.quantity,
  })
}

async function remove(item) {
  if (!confirm(`Hapus "${item.name}"?`)) return
  await api.delete(`/equipment/${item.id}`)
  await load()
}

async function logout() {
  try {
    await api.post('/logout')
  } finally {
    localStorage.removeItem('token')
    router.push({ name: 'login' })
  }
}

onMounted(load)
</script>

<template>
  <main class="wrap">
    <header class="bar">
      <h1>Inventaris Alat Lab</h1>
      <button class="ghost" @click="logout">Keluar</button>
    </header>

    <section class="card">
      <h2>{{ editingId ? 'Ubah alat' : 'Tambah alat' }}</h2>
      <form class="grid" @submit.prevent="save">
        <label>Nama
          <input v-model="form.name" required />
          <small v-if="errors.name" class="error">{{ errors.name[0] }}</small>
        </label>
        <label>Kode
          <input v-model="form.code" required />
          <small v-if="errors.code" class="error">{{ errors.code[0] }}</small>
        </label>
        <label>Kategori
          <input v-model="form.category" required />
          <small v-if="errors.category" class="error">{{ errors.category[0] }}</small>
        </label>
        <label>Kondisi
          <select v-model="form.condition">
            <option v-for="c in conditions" :key="c">{{ c }}</option>
          </select>
          <small v-if="errors.condition" class="error">{{ errors.condition[0] }}</small>
        </label>
        <label>Jumlah
          <input v-model.number="form.quantity" type="number" min="0" required />
          <small v-if="errors.quantity" class="error">{{ errors.quantity[0] }}</small>
        </label>
        <div class="actions">
          <button>{{ editingId ? 'Simpan perubahan' : 'Tambah' }}</button>
          <button v-if="editingId" type="button" class="ghost" @click="reset">Batal</button>
        </div>
      </form>
    </section>

    <section class="card">
      <h2>Daftar alat</h2>
      <table>
        <thead>
          <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Kondisi</th><th>Jumlah</th><th></th></tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.code }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.category }}</td>
            <td>{{ item.condition }}</td>
            <td>{{ item.quantity }}</td>
            <td class="actions">
              <button class="ghost" @click="edit(item)">Ubah</button>
              <button class="danger" @click="remove(item)">Hapus</button>
            </td>
          </tr>
          <tr v-if="!items.length"><td colspan="6" class="muted">Belum ada data.</td></tr>
        </tbody>
      </table>
    </section>
  </main>
</template>
