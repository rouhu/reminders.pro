<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { supabase } from '../lib/supabase'
import { useRouter, useRoute } from 'vue-router'
import type { ReminderType } from '../types/reminder'

const router = useRouter()
const route = useRoute()
const isEdit = ref(false)

const title = ref('')
const type = ref<ReminderType>('appointment')
const date = ref('')
const time = ref('')
const timezone = ref(Intl.DateTimeFormat().resolvedOptions().timeZone)
const recipients = ref('')
const loading = ref(false)
const errorMsg = ref('')

const reminderTypes: ReminderType[] = ['appointment', 'task', 'event', 'meeting', 'call']

// Common timezone list
const timezones = [
  'UTC',
  'America/New_York',
  'America/Chicago',
  'America/Denver',
  'America/Los_Angeles',
  'America/Phoenix',
  'America/Anchorage',
  'America/Honolulu',
  'America/Toronto',
  'America/Vancouver',
  'Europe/London',
  'Europe/Paris',
  'Europe/Berlin',
  'Europe/Rome',
  'Europe/Madrid',
  'Asia/Tokyo',
  'Asia/Shanghai',
  'Asia/Singapore',
  'Asia/Dubai',
  'Australia/Sydney',
  'Australia/Melbourne',
  'Pacific/Auckland'
]

onMounted(async () => {
  const id = route.params.id
  if (id) {
    isEdit.value = true
    await loadReminder(id as string)
  }
})

async function loadReminder(id: string) {
  try {
    loading.value = true
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) return

    const { data, error } = await supabase
      .from('reminders')
      .select('*')
      .eq('id', id)
      .eq('user_id', user.id)
      .single()

    if (error) throw error
    if (data) {
      title.value = data.title
      type.value = data.type
      date.value = data.date
      time.value = data.time
      timezone.value = data.timezone
      recipients.value = data.recipients.join('\n')
    }
  } catch (error) {
    console.error('Error loading reminder:', error)
    router.push('/reminders')
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  try {
    loading.value = true
    errorMsg.value = ''
    
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) throw new Error('Not authenticated')

    const reminderData = {
      user_id: user.id,
      title: title.value,
      type: type.value,
      date: date.value,
      time: time.value,
      timezone: timezone.value,
      recipients: recipients.value.split('\n').filter(email => email.trim()),
      status: 'scheduled' // New reminders are always scheduled
    }

    if (isEdit.value) {
      const { error } = await supabase
        .from('reminders')
        .update(reminderData)
        .eq('id', route.params.id)
        .eq('user_id', user.id)
      if (error) throw error
    } else {
      const { error } = await supabase
        .from('reminders')
        .insert([reminderData])
      if (error) throw error
    }

    router.push('/reminders')
  } catch (error: any) {
    errorMsg.value = error.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="reminder-form">
    <h1>{{ isEdit ? 'Edit Reminder' : 'New Reminder' }}</h1>
    
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label>Title</label>
        <input 
          v-model="title" 
          type="text" 
          required
        >
      </div>

      <div class="form-group">
        <label>Type</label>
        <select v-model="type" required>
          <option v-for="t in reminderTypes" :key="t" :value="t">
            {{ t.charAt(0).toUpperCase() + t.slice(1) }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label>Date</label>
        <input 
          v-model="date" 
          type="date" 
          required
        >
      </div>

      <div class="form-group">
        <label>Time</label>
        <input 
          v-model="time" 
          type="time" 
          required
        >
      </div>

      <div class="form-group">
        <label>Timezone</label>
        <select v-model="timezone" required>
          <option v-for="tz in timezones" :key="tz" :value="tz">
            {{ tz }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label>Recipients (one email per line)</label>
        <textarea 
          v-model="recipients" 
          rows="5"
          required
        ></textarea>
      </div>

      <p v-if="errorMsg" class="error">{{ errorMsg }}</p>

      <div class="buttons">
        <button type="submit" :disabled="loading">
          {{ loading ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
        </button>
        <button type="button" @click="router.push('/reminders')" class="cancel">
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.reminder-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
}

input, select, textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

button {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

button[type="submit"] {
  background-color: #646cff;
  color: white;
  border: none;
}

.cancel {
  background-color: #f5f5f5;
  border: 1px solid #ccc;
}

.error {
  color: red;
  margin: 1rem 0;
}
</style>
