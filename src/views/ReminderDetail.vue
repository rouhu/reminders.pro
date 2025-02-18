<script setup lang="ts">
import { ref, onMounted, defineComponent } from 'vue'
import { supabase } from '../lib/supabase'
import { useRouter, useRoute } from 'vue-router'
import type { Reminder } from '../types/reminder'

defineComponent({
  name: 'ReminderDetail'
})

const router = useRouter()
const route = useRoute()
const reminder = ref<Reminder | null>(null)
const loading = ref<boolean>(true)

onMounted(async () => {
  await loadReminder()
})

async function loadReminder() {
  try {
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) return

    const { data, error } = await supabase
      .from('reminders')
      .select('*')
      .eq('id', route.params.id)
      .eq('user_id', user.id)
      .single()

    if (error) throw error
    reminder.value = data
  } catch (error) {
    console.error('Error loading reminder:', error)
    router.push('/reminders')
  } finally {
    loading.value = false
  }
}

function getStatusColor(status: string): string {
  switch (status) {
    case 'scheduled': return '#646cff'
    case 'sent': return '#4caf50'
    case 'failed': return '#ff4444'
    default: return '#666'
  }
}
</script>

<template>
  <div class="reminder-detail">
    <div v-if="loading" class="loading">Loading...</div>
    
    <template v-else-if="reminder">
      <div class="header">
        <h2>Reminder Details</h2>
        <router-link :to="'/reminders/edit/' + reminder.id" class="edit-button">
          Edit
        </router-link>
      </div>

      <div class="details">
        <div class="detail-item">
          <div class="label">Title:</div>
          <div class="value">{{ reminder.title }}</div>
        </div>

        <div class="detail-item">
          <div class="label">Type:</div>
          <div class="value">{{ reminder.type }}</div>
        </div>

        <div class="detail-item">
          <div class="label">Status:</div>
          <div class="value">
            <span 
              class="status-badge"
              :style="{ backgroundColor: getStatusColor(reminder.status) }"
            >
              {{ reminder.status }}
            </span>
          </div>
        </div>

        <div class="detail-item">
          <div class="label">Date:</div>
          <div class="value">{{ reminder.date }}</div>
        </div>

        <div class="detail-item">
          <div class="label">Time:</div>
          <div class="value">{{ reminder.time }}</div>
        </div>

        <div class="detail-item">
          <div class="label">Timezone:</div>
          <div class="value">{{ reminder.timezone }}</div>
        </div>

        <div class="detail-item">
          <div class="label">Recipients:</div>
          <div class="value">
            <ul>
              <li v-for="email in reminder.recipients" :key="email">
                {{ email }}
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="actions">
        <button @click="router.push('/reminders')" class="back-button">
          Back to List
        </button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.reminder-detail {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header h2 {
  margin: 0;
  text-align: left;
  font-size: 1.5rem;
  font-weight: 500;
}

.edit-button {
  padding: 0.5rem 1rem;
  background-color: #646cff;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

.details {
  background-color: #f5f5f5;
  padding: 1.5rem;
  border-radius: 4px;
  text-align: left;
}

.detail-item {
  display: flex;
  margin-bottom: 1rem;
  align-items: flex-start;
}

.label {
  width: 120px;
  font-weight: bold;
  padding-top: 2px;
}

.value {
  flex: 1;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  color: white;
  font-size: 0.875rem;
  text-transform: capitalize;
}

ul {
  margin: 0;
  padding-left: 0;
  list-style-position: inside;
}

li {
  margin-bottom: 0.25rem;
}

.actions {
  margin-top: 2rem;
  text-align: center;
}

.back-button {
  padding: 0.5rem 1rem;
  background-color: #f5f5f5;
  border: 1px solid #ccc;
  border-radius: 4px;
  cursor: pointer;
}

.loading {
  text-align: center;
  padding: 2rem;
}
</style>
