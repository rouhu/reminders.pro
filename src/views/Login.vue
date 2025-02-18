<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { supabase } from '../lib/supabase'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const successMsg = ref('')
const isResetMode = ref(false)
const loading = ref(false)

async function handleLogin() {
  try {
    errorMsg.value = ''
    successMsg.value = ''
    await auth.login(email.value, password.value)
    router.push('/reminders')
  } catch (error: any) {
    errorMsg.value = error.message
  }
}

async function handleResetPassword() {
  try {
    loading.value = true
    errorMsg.value = ''
    successMsg.value = ''

    const { error } = await supabase.auth.resetPasswordForEmail(email.value, {
      redirectTo: `${window.location.origin}/reset-password`,
    })

    if (error) throw error

    successMsg.value = 'Password reset instructions have been sent to your email'
    isResetMode.value = false
  } catch (error: any) {
    errorMsg.value = error.message
  } finally {
    loading.value = false
  }
}

function toggleResetMode() {
  isResetMode.value = !isResetMode.value
  errorMsg.value = ''
  successMsg.value = ''
}
</script>

<template>
  <div class="login-page">
    <div class="login-container">
      <h1>Reminder App</h1>
      
      <form v-if="!isResetMode" @submit.prevent="handleLogin">
        <div class="form-group">
          <input 
            v-model="email" 
            type="email" 
            placeholder="Email" 
            required
          >
        </div>

        <div class="form-group">
          <input 
            v-model="password" 
            type="password" 
            placeholder="Password" 
            required
          >
        </div>

        <p v-if="errorMsg" class="error">{{ errorMsg }}</p>
        <p v-if="successMsg" class="success">{{ successMsg }}</p>

        <button type="submit" :disabled="auth.loading">
          {{ auth.loading ? 'Loading...' : 'Login' }}
        </button>

        <div class="links">
          <button 
            type="button" 
            class="text-button" 
            @click="toggleResetMode"
          >
            Forgot Password?
          </button>
          <router-link to="/register">Register</router-link>
        </div>
      </form>

      <form v-else @submit.prevent="handleResetPassword">
        <h2>Reset Password</h2>
        <p class="instructions">
          Enter your email address and we'll send you instructions to reset your password.
        </p>

        <div class="form-group">
          <input 
            v-model="email" 
            type="email" 
            placeholder="Email" 
            required
          >
        </div>

        <p v-if="errorMsg" class="error">{{ errorMsg }}</p>
        <p v-if="successMsg" class="success">{{ successMsg }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Sending...' : 'Send Reset Instructions' }}
        </button>

        <div class="links">
          <button 
            type="button" 
            class="text-button" 
            @click="toggleResetMode"
          >
            Back to Login
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.login-container {
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

h2 {
  text-align: center;
  margin-bottom: 1rem;
  color: #333;
}

.instructions {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #666;
  font-size: 0.9rem;
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

.links {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
}

.text-button {
  background: none;
  border: none;
  color: #646cff;
  padding: 0;
  margin: 0;
  font-size: 0.9rem;
  width: auto;
  text-decoration: underline;
  cursor: pointer;
}

.text-button:hover {
  color: #535bf2;
}

.error {
  color: #ff4444;
  margin: 0.5rem 0;
  text-align: center;
}

.success {
  color: #4caf50;
  margin: 0.5rem 0;
  text-align: center;
}

a {
  color: #646cff;
  text-decoration: none;
  font-size: 0.9rem;
}

a:hover {
  text-decoration: underline;
}
</style>
