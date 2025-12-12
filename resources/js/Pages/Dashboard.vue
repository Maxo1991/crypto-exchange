<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Dashboard</h1>
      <p class="text-sm text-gray-500">Logged in as:  {{ user.email }}</p>
    </div>
    <div class="flex gap-2 justify-end mb-4">
      <a 
        href="/orders" 
        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
      >
        Go to Orders
      </a>

      <button 
        @click="logout"
        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
      >
        Logout
      </button>
    </div>

    <!-- Form to place order and price list -->
    <div class="flex flex-col md:flex-row gap-6">
      <div class="w-full md:w-1/2 border p-4 rounded shadow bg-white">
        <h2 class="text-xl font-semibold mb-2">Place Limit Order</h2>
        <form @submit.prevent="placeOrder" class="space-y-3">
          <label class="block">
            Cryptocurrency:
            <select v-model="newOrder.symbol" required class="border p-1 rounded w-full">
              <option value="" disabled>Select one</option>
              <option v-for="crypto in cryptoOptions" :key="crypto.symbol" :value="crypto.symbol">
                {{ crypto.name }} ({{ crypto.symbol }})
              </option>
            </select>
          </label>
          <label class="block">
            Side:
            <select v-model="newOrder.side" required class="border p-1 rounded w-full">
              <option value="buy">Buy</option>
              <option value="sell">Sell</option>
            </select>
          </label>
          <label class="block">
            Price (USD):
            <input type="number" v-model.number="newOrder.price" min="0" step="0.01" required class="border p-1 rounded w-full" />
          </label>
          <label class="block">
            Amount:
            <input type="number" v-model.number="newOrder.amount" min="0" step="0.0001" required class="border p-1 rounded w-full" />
          </label>
          <div class="block">
            <span class="font-medium">Value (USD): </span>
            <strong>{{ (newOrder.price * newOrder.amount).toFixed(2) }}</strong>
          </div>
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">
            Place Order
          </button>
        </form>
      </div>

      <!-- Current Prices -->
      <div class="w-full md:w-1/2 border p-4 rounded shadow bg-white">
        <h3 class="text-lg font-semibold mb-2">Current Prices</h3>
        <ul class="space-y-1 text-sm">
          <li 
            v-for="crypto in cryptoOptions" 
            :key="crypto.symbol" 
            class="flex justify-between py-1 border-b last:border-b-0"
          >
            <span>{{ crypto.name }} ({{ crypto.symbol }})</span>
            <strong>{{ cryptoPrices[crypto.symbol] }} USD</strong>
          </li>
        </ul>
      </div>
    </div>

    <!-- Assets Table -->
    <table class="table-auto w-full border border-gray-300 mt-6">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-4 py-2">Symbol</th>
          <th class="px-4 py-2">Available</th>
          <th class="px-4 py-2">Locked</th>
          <th class="px-4 py-2">Value (USD)</th>
        </tr>
      </thead>
      <tbody>
        <!-- USD Row -->
        <tr class="text-center border-t border-gray-300 bg-gray-100">
          <td class="px-4 py-2">USD</td>
          <td class="px-4 py-2">{{ usdBalance }}</td>
          <td class="px-4 py-2">{{ lockedUSD }}</td>
          <td class="px-4 py-2">{{ (Number(usdBalance) + Number(lockedUSD)).toFixed(2) }} USD</td>
        </tr>

        <!-- Crypto Assets -->
        <tr v-for="asset in assetsList" :key="asset.id" class="text-center border-t border-gray-300">
          <td class="px-4 py-2">{{ asset.symbol }}</td>
          <td class="px-4 py-2">{{ asset.amount }}</td>
          <td class="px-4 py-2">{{ asset.locked_amount }}</td>
          <td class="px-4 py-2">{{ (cryptoPrices[asset.symbol] * (Number(asset.amount) + Number(asset.locked_amount))).toFixed(2) }} USD</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { defineProps } from 'vue'
import { logout } from '../app.js';
import { usePage } from '@inertiajs/vue3';
import { reactive } from 'vue'

const page = usePage();
const userId = page.props.user.id; 

const props = defineProps({
  user: Object,        
  usd_balance: [Number, String],
  assets: Array,
  locked_usd: { type: Number, default: 0 },
})

const cryptoOptions = [
  { symbol: 'BTC', name: 'Bitcoin' },
  { symbol: 'ETH', name: 'Ethereum' },
  { symbol: 'USDT', name: 'Tether' },
  { symbol: 'BNB', name: 'Binance Coin' },
  { symbol: 'ADA', name: 'Cardano' },
]

const cryptoPrices = ref({
  BTC: 45000,
  ETH: 2300,
  USDT: 1,
  BNB: 310,
  ADA: 0.62,
})

const newOrder = reactive({
  symbol: '',
  side: 'buy',
  price: 0,
  amount: 0
})

const usdBalance = ref(0)
const lockedUSD = ref(0)
const assetsList = ref([])

const fetchAssets = async () => {
  try {
    const res = await axios.get('/api/profile')
    console.log(res.data)
    assetsList.value = res.data.assets.map(asset => ({
      id: asset.id,
      symbol: asset.symbol,
      amount: asset.available,       
      locked_amount: asset.locked    
    }))
    usdBalance.value = Number(res.data.usd_balance) || 0 
    lockedUSD.value = Number(res.data.locked_usd) || 0
  } catch (error) {
    console.error('Error fetching assets:', error)
  }
}

const placeOrder = async () => {
  try {
    await axios.post('/api/orders', {
      symbol: newOrder.symbol,
      side: newOrder.side,
      price: newOrder.price,
      amount: newOrder.amount
    })
    alert('Order placed successfully!')
    newOrder.symbol = ''
    newOrder.side = 'buy'
    newOrder.price = 0
    newOrder.amount = 0
    await fetchAssets()
  } catch (err) {
    const errorMessage = err.response?.data?.error || 'Failed to place order.'
    alert(errorMessage)
  }
}

onMounted(() => {
    fetchAssets();

    console.log("Connecting to private channel: user." + userId);
    window.Echo.private(`user.${userId}`)
        .listen('OrderMatched', (event) => {
            console.log("🔥 EVENT PRIMLJEN:", event);
            fetchAssets();
            alert("Order matched for user " + userId);
        });
});
</script>
