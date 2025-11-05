<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">API Test</h2>
                <p class="mt-2 text-sm text-gray-600">Testing API connection</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <button @click="testApi" :disabled="loading" class="w-full btn btn-primary">
                    {{ loading ? 'Testing...' : 'Test API Connection' }}
                </button>

                <button @click="testLogin" :disabled="loading" class="w-full btn btn-secondary">
                    {{ loading ? 'Testing...' : 'Test Login' }}
                </button>

                <div v-if="result" class="mt-4 p-4 bg-green-100 rounded">
                    <h3 class="font-bold text-green-800">Success:</h3>
                    <pre class="text-sm mt-2 text-green-700">{{ JSON.stringify(result, null, 2) }}</pre>
                </div>

                <div v-if="error" class="mt-4 p-4 bg-red-100 rounded">
                    <h3 class="font-bold text-red-800">Error:</h3>
                    <pre class="text-sm mt-2 text-red-700">{{ error }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const loading = ref(false)
const result = ref(null)
const error = ref(null)

const testApi = async () => {
    loading.value = true
    result.value = null
    error.value = null

    try {
        const { $api } = useNuxtApp()

        console.log('Testing API connection...')
        const response = await $api.get('/test')

        console.log('API Response:', response)
        result.value = response
    } catch (err) {
        console.error('API Error:', err)
        error.value = {
            message: err.message,
            status: err.status,
            data: err.data,
            response: err.response
        }
    } finally {
        loading.value = false
    }
}

const testLogin = async () => {
    loading.value = true
    result.value = null
    error.value = null

    try {
        const { $api } = useNuxtApp()

        console.log('Testing login...')
        const response = await $api.post('/login', {
            email: 'john@example.com',
            password: 'password123'
        })

        console.log('Login Response:', response)
        result.value = response
    } catch (err) {
        console.error('Login Error:', err)
        error.value = {
            message: err.message,
            status: err.status,
            data: err.data,
            response: err.response
        }
    } finally {
        loading.value = false
    }
}
</script>