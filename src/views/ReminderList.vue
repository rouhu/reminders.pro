<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { supabase } from '../lib/supabase'
import type { Reminder } from '../types/reminder'
import { useRouter } from 'vue-router'

const reminders = ref<Reminder[]>([])
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('asc')
const loading = ref(true)
const router = useRouter()

async function loadReminders() {
  try {
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) return

    let query = supabase
      .from('reminders')
      .select('*')
      .eq('user_id', user.id)
      .order(sortField.value, { ascending: sortDirection.value === 'asc' })

    if (searchQuery.value) {
      query = query.ilike('title', `%${searchQuery.value}%`)
    }

    const { data, error } = await query
    if (error) throw error
    reminders.value = data
  } catch (error) {
    console.error('Error loading reminders:', error)
  } finally {
    loading.value = false
  }
}

async function handleLogout() {
  try {
    const { error } = await supabase.auth.signOut()
    if (error) throw error
    router.push('/')
  } catch (error) {
    console.error('Error logging out:', error)
  }
}

function toggleSort(field: string) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  loadReminders()
}

function getStatusColor(status: string): string {
  switch (status) {
    case 'scheduled': return '#646cff'
    case 'sent': return '#4caf50'
    case 'failed': return '#ff4444'
    default: return '#666'
  }
}

onMounted(loadReminders)
</script>

<template>
  <div class="reminder-list">
    <div class="title-row">
      <h1>Reminders</h1>
      <button @click="handleLogout" class="logout-button">
        Logout
      </button>
    </div>

    <div class="actions-row">
      <router-link to="/reminders/new" class="new-button">
        Create New Reminder
      </router-link>
    </div>

    <div class="search-bar">
      <input 
        v-model="searchQuery" 
        @input="loadReminders"
        type="text" 
        placeholder="Search reminders..."
      >
    </div>

    <div v-if="loading" class="loading">Loading...</div>
    
    <table v-else>
      <thead>
        <tr>
          <th @click="toggleSort('title')">Title</th>
          <th @click="toggleSort('type')">Type</th>
          <th @click="toggleSort('date')">Date</th>
          <th @click="toggleSort('time')">Time</th>
          <th @click="toggleSort('status')">Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="reminder in reminders" :key="reminder.id">
          <td>{{ reminder.title }}</td>
          <td>{{ reminder.type }}</td>
          <td>{{ reminder.date }}</td>
          <td>{{ reminder.time }}</td>
          <td>
            <span 
              class="status-badge"
              :style="{ backgroundColor: getStatusColor(reminder.status) }"
            >
              {{ reminder.status }}
            </span>
          </td>
          <td>
            <router-link :to="'/reminders/' + reminder.id">View</router-link>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-if="!loading && reminders.length === 0" class="no-results">
      No reminders found
    </p>
  </div>
</template>

<style scoped>
.reminder-list {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.title-row h1 {
  margin: 0;
}

.actions-row {
  margin-bottom: 1.5rem;
}

.new-button {
  padding: 0.5rem 1rem;
  background-color: #646cff;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  display: inline-block;
}

.logout-button {
  padding: 0.5rem 1rem;
  background-color: #ff4444;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.search-bar {
  margin-bottom: 1rem;
}

.search-bar input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 0.5rem;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  cursor: pointer;
  background-color: #f5f5f5;
}

.loading, .no-results {
  text-align: center;
  padding: 2rem;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  color: white;
  font-size: 0.875rem;
  text-transform: capitalize;
}
</style>
