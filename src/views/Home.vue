<script setup lang="ts">
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
</script>

<template>
  <div class="home">
    <h1>Welcome to Home</h1>
    <p v-if="auth.user">Logged in as: {{ auth.user.email }}</p>
    <button @click="auth.logout">Logout</button>
  </div>
</template>

<style scoped>
.home {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  text-align: center;
}

button {
  margin-top: 1rem;
}
</style>
