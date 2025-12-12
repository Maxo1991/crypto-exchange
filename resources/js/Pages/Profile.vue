<template>
  <div>
    <h2>Welcome, {{ user.name }}</h2>
    <p>Balance: ${{ user.usd_balance }}</p>

    <h3>Assets</h3>
    <ul>
      <li v-for="asset in user.assets" :key="asset.symbol">
        {{ asset.symbol }}: {{ asset.amount }} (locked: {{ asset.locked_amount }})
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    user: Object
  }
}
</script>
