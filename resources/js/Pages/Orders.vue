<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold mb-4">Orders</h1>
      <a 
        href="/dashboard" 
        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
      >
        Go to Dashboard
      </a>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex flex-wrap gap-4 items-center">
      <!-- Symbol Filter -->
      <label class="flex items-center gap-2">
        Symbol:
        <select v-model="selectedSymbol" @change="fetchOrders" class="border p-1 rounded">
          <option value="">All</option>
          <option v-for="crypto in cryptoOptions" :key="crypto.symbol" :value="crypto.symbol">
            {{ crypto.symbol }}
          </option>
        </select>
      </label>

      <!-- Side Filter -->
      <label class="flex items-center gap-2">
        Side:
        <select v-model="selectedSide" @change="fetchOrders" class="border p-1 rounded">
          <option value="">All</option>
          <option value="buy">Buy</option>
          <option value="sell">Sell</option>
        </select>
      </label>

      <!-- Status Filter -->
      <label class="flex items-center gap-2">
        Status:
        <select v-model="selectedStatus" @change="fetchOrders" class="border p-1 rounded">
          <option value="">All</option>
          <option value="1">Open</option>
          <option value="2">Filled</option>
          <option value="3">Cancelled</option>
        </select>
      </label>

      <!-- Sort Filter -->
      <label class="flex items-center gap-2">
        Sort by:
        <select v-model="sortField" @change="fetchOrders" class="border p-1 rounded">
          <option value="created_at">Date</option>
          <option value="price">Price</option>
          <option value="amount">Amount</option>
        </select>
      </label>

      <label class="flex items-center gap-2">
        Order:
        <select v-model="sortOrder" @change="fetchOrders" class="border p-1 rounded">
          <option value="desc">Desc</option>
          <option value="asc">Asc</option>
        </select>
      </label>

      <!-- Limit per page -->
      <label class="flex items-center gap-2">
        Limit:
        <select v-model="limit" @change="fetchOrders" class="border p-1 rounded">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
      </label>
    </div>

    <!-- Orders Table -->
    <table class="table-auto w-full border border-gray-300 mt-4">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-4 py-2">ID</th>
          <th class="px-4 py-2">Symbol</th>
          <th class="px-4 py-2">Side</th>
          <th class="px-4 py-2">Price (USD)</th>
          <th class="px-4 py-2">Amount</th>
          <th class="px-4 py-2">Value (USD)</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in ordersList" :key="order.id" class="text-center border-t">
          <td>{{ order.id }}</td>
          <td>{{ order.symbol }}</td>
          <td>{{ order.side }}</td>
          <td>{{ Number(order.price).toFixed(2) }}</td>
          <td>{{ Number(order.amount).toFixed(4) }}</td>
          <td>{{ (Number(order.price) * Number(order.amount)).toFixed(2) }}</td>
          <td>{{ statusMap[order.status] }}</td>
          <td>
            <button 
              v-if="order.status === 1" 
              @click="cancelOrder(order.id)" 
              class="bg-red-500 text-white px-2 py-1 rounded">
              Cancel
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center items-center gap-2">
      <button 
        :disabled="page === 1" 
        @click="page-- && fetchOrders()" 
        class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
      >
        Prev
      </button>

      <span>Page {{ page }} of {{ totalPages }}</span>

      <button 
        :disabled="page >= totalPages" 
        @click="page++ && fetchOrders()" 
        class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
      >
        Next
      </button>
    </div>

    <!-- Trades -->
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold mb-4">Trades</h1>
    </div>
    <div class="mt-8">
      <table class="table-auto w-full border border-gray-300 mt-4">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Order ID</th>
            <th class="px-4 py-2">Symbol</th>
            <th class="px-4 py-2">Side</th>
            <th class="px-4 py-2">Price (USD)</th>
            <th class="px-4 py-2">Amount</th>
            <th class="px-4 py-2">Value (USD)</th>
            <th class="px-4 py-2">Created At</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="trade in tradesList" :key="trade.id" class="text-center border-t">
            <td>{{ trade.id }}</td>
            <td>{{ trade.order_id }}</td>
            <td>{{ trade.symbol }}</td>
            <td>{{ trade.side }}</td>
            <td>{{ Number(trade.price).toFixed(2) }}</td>
            <td>{{ Number(trade.amount).toFixed(4) }}</td>
            <td>{{ (Number(trade.price) * Number(trade.amount)).toFixed(2) }}</td>
            <td>{{ new Date(trade.created_at).toLocaleDateString('en-GB') }}</td>
          </tr>

          <tr v-if="tradesList.length === 0">
            <td colspan="8" class="text-center py-4 text-gray-500">
              No trades found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { initCSRF } from '../app'

const cryptoOptions = [
  { symbol: 'BTC', name: 'Bitcoin' },
  { symbol: 'ETH', name: 'Ethereum' },
  { symbol: 'USDT', name: 'Tether' },
  { symbol: 'BNB', name: 'Binance Coin' },
  { symbol: 'ADA', name: 'Cardano' },
]

const statusMap = { 1: 'Open', 2: 'Filled', 3: 'Cancelled' }

const selectedSymbol = ref('')
const selectedSide = ref('')
const selectedStatus = ref('')
const sortField = ref('created_at')
const sortOrder = ref('desc')

const page = ref(1)
const limit = ref(5)
const total = ref(0)
const totalPages = computed(() => Math.ceil(total.value / limit.value))

const ordersList = ref([])
const tradesList = ref([])

const fetchOrders = async () => {
  try {
    let url = `/api/orders?page=${page.value}&limit=${limit.value}`
    if (selectedSymbol.value) url += `&symbol=${selectedSymbol.value}`
    if (selectedSide.value) url += `&side=${selectedSide.value}`
    if (selectedStatus.value) url += `&status=${selectedStatus.value}`
    if (sortField.value) url += `&sortField=${sortField.value}`
    if (sortOrder.value) url += `&sortOrder=${sortOrder.value}`

    const res = await axios.get(url)
    ordersList.value = res.data.orders
    total.value = res.data.total
  } catch (err) {
    console.error('Error fetching orders:', err)
    alert('Failed to fetch orders.')
  }
}

const cancelOrder = async (orderId) => {
  if (!confirm('Are you sure you want to cancel this order?')) return

  try {
    await axios.post(`/api/orders/${orderId}/cancel`)
    alert('Order cancelled successfully!')
    await fetchOrders() // Refresh table
  } catch (err) {
    console.error('Error cancelling order:', err)
    alert(err.response?.data?.error || 'Failed to cancel order.')
  }
}

const fetchTrades = async () => {
  try {
    const res = await axios.get('/api/trades')
    tradesList.value = res.data.trades
  } catch (err) {
    console.error('Error fetching trades:', err)
  }
}

onMounted(async () => {
  await initCSRF()
  await fetchOrders()
  await fetchTrades()
})
</script>
