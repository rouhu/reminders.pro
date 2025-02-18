<script setup lang="ts">
import { ref } from 'vue'
import { supabase } from '../lib/supabase'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const name = ref('')
const email = ref('')
const password = ref('')
const errorMsg = ref('')

async function handleRegister() {
  try {
    loading.value = true
    errorMsg.value = ''
    
    const { data, error } = await supabase.auth.signUp({
      email: email.value,
      password: password.value,
      options: {
        data: {
          name: name.value
        }
      }
    })

    if (error) throw error

    if (data.user) {
      router.push('/login')
    }
  } catch (error: any) {
    errorMsg.value = error.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="register">
    <h1>Register</h1>
    <form @submit.prevent="handleRegister">
      <div class="form-group">
        <input 
          v-model="name" 
          type="text" 
          placeholder="Full Name" 
          required
        >
      </div>
      
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
          minlength="6"
        >
      </div>

      <p v-if="errorMsg" class="error">{{ errorMsg }}</p>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Registering...' : 'Register' }}
      </button>

      <p class="login-link">
        Already have an account? 
        <router-link to="/">Login</router-link>
      </p>
    </form>
  </div>
</template>

<style scoped>
.register {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
}

.form-group {
  margin-bottom: 1rem;
}

input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  width: 100%;
  padding: 0.5rem;
  margin-top: 1rem;
}

.error {
  color: red;
  margin: 0.5rem 0;
}

.login-link {
  margin-top: 1rem;
  text-align: center;
}
</style>
