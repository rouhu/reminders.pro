<script setup lang="ts">
import { ref } from 'vue'
import { supabase } from '../lib/supabase'
import { useRouter } from 'vue-router'

const router = useRouter()
const password = ref('')
const confirmPassword = ref('')
const errorMsg = ref('')
const loading = ref(false)

async function handlePasswordReset() {
  try {
    if (password.value !== confirmPassword.value) {
      errorMsg.value = 'Passwords do not match'
      return
    }

    loading.value = true
    errorMsg.value = ''

    const { error } = await supabase.auth.updateUser({
      password: password.value
    })

    if (error) throw error

    // Redirect to login page after successful password reset
    router.push('/')
  } catch (error: any) {
    errorMsg.value = error.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="reset-page">
    <div class="reset-container">
      <h1>Reset Password</h1>
      
      <form @submit.prevent="handlePasswordReset">
        <div class="form-group">
          <input 
            v-model="password" 
            type="password" 
            placeholder="New Password" 
            required
            minlength="6"
          >
        </div>

        <div class="form-group">
          <input 
            v-model="confirmPassword" 
            type="password" 
            placeholder="Confirm New Password" 
            required
            minlength="6"
          >
        </div>

        <p v-if="errorMsg" class="error">{{ errorMsg }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Updating...' : 'Update Password' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.reset-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.reset-container {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  color: #646cff;
}

.form-group {
  margin-bottom: 1rem;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

button {
  width: 100%;
  padding: 0.75rem;
  background-color: #646cff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  margin-top: 1rem;
}

button:disabled {
  background-color: #a5a5a5;
  cursor: not-allowed;
}

.error {
  color: #ff4444;
  margin: 0.5rem 0;
  text-align: center;
}
</style>
