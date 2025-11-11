<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-8">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full">
      <h1 class="text-3xl font-bold mb-6">OAuth Debug Test</h1>
      
      <div class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded p-4">
          <h2 class="font-semibold mb-2">Configuration:</h2>
          <div class="text-sm space-y-1 font-mono">
            <div><strong>API Base URL:</strong> {{ apiBaseUrl }}</div>
            <div><strong>Google Redirect:</strong> {{ googleRedirectUrl }}</div>
          </div>
        </div>

        <div class="space-y-2">
          <button
            @click="testDirect"
            class="w-full bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700"
          >
            Test Direct Navigation
          </button>

          <button
            @click="testFetch"
            class="w-full bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700"
          >
            Test Fetch API
          </button>

          <button
            @click="testBackendHealth"
            class="w-full bg-purple-600 text-white px-6 py-3 rounded hover:bg-purple-700"
          >
            Test Backend Health
          </button>
        </div>

        <div v-if="testResult" class="bg-gray-50 border rounded p-4">
          <h3 class="font-semibold mb-2">Test Result:</h3>
          <pre class="text-xs overflow-auto">{{ testResult }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  title: 'OAuth Debug Test',
});

const config = useRuntimeConfig();
const apiBaseUrl = config.public.apiBaseUrl;
const googleRedirectUrl = `${apiBaseUrl}/auth/google/redirect`;
const testResult = ref('');

const testDirect = () => {
  testResult.value = `Navigating to: ${googleRedirectUrl}\n\nYou should be redirected to Google...`;
  console.log('🚀 Direct navigation test:', googleRedirectUrl);
  setTimeout(() => {
    window.location.href = googleRedirectUrl;
  }, 1000);
};

const testFetch = async () => {
  testResult.value = 'Testing fetch API...';
  try {
    console.log('🧪 Fetch test to:', googleRedirectUrl);
    const response = await fetch(googleRedirectUrl, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });
    
    testResult.value = `Status: ${response.status}\n`;
    testResult.value += `OK: ${response.ok}\n`;
    testResult.value += `Headers: ${JSON.stringify(Object.fromEntries(response.headers.entries()), null, 2)}\n\n`;
    
    const text = await response.text();
    testResult.value += `Body: ${text}`;
    
    console.log('✅ Fetch successful:', testResult.value);
  } catch (error) {
    testResult.value = `❌ Fetch failed:\n${error.message}\n\nStack:\n${error.stack}`;
    console.error('❌ Fetch error:', error);
  }
};

const testBackendHealth = async () => {
  testResult.value = 'Testing backend health...';
  try {
    const healthUrl = `${apiBaseUrl}/test`;
    console.log('🏥 Health check to:', healthUrl);
    
    const response = await fetch(healthUrl);
    const data = await response.json();
    
    testResult.value = `✅ Backend is healthy!\n\n${JSON.stringify(data, null, 2)}`;
    console.log('✅ Backend health:', data);
  } catch (error) {
    testResult.value = `❌ Backend health check failed:\n${error.message}`;
    console.error('❌ Health check error:', error);
  }
};
</script>
