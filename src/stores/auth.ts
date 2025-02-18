import { defineStore } from 'pinia'
import { supabase } from '../lib/supabase'
import { ref } from 'vue'
import type { User } from '@supabase/supabase-js'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)

  async function login(email: string, password: string) {
    try {
      loading.value = true
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password
      })
      if (error) throw error
      user.value = data.user
    } catch (error) {
      console.error('Error logging in:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      loading.value = true
      const { error } = await supabase.auth.signOut()
      if (error) throw error
      user.value = null
    } catch (error) {
      console.error('Error logging out:', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    loading,
    login,
    logout
  }
})
