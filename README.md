# Crypto Limit Order Exchange

This project is a **cryptocurrency trading platform** that allows users to place **buy and sell limit orders** for assets like BTC and ETH. It ensures **financial data integrity, concurrency safety, and real-time updates** for balances and orderbooks.

## Project Description

The backend is built with **Laravel**, providing a RESTful API for user management, order creation/cancellation, and automated order matching. The frontend is implemented with **Vue.js (Composition API)** and **Tailwind CSS**, featuring real-time updates through **Pusher/Laravel Broadcasting**.

**Key Features:**
- User account with USD balance and asset balances  
- Limit order creation (buy/sell) with validation and locking of funds/assets  
- Automatic order matching (full match only) with 1.5% commission  
- Real-time broadcasting of executed trades to both parties  
- Frontend displays balances, orderbook, and past orders with instant updates  



## Technologies Used

**Backend:**
- **PHP 8.2** – latest stable PHP version  
- **Laravel 12** – modern PHP framework for building APIs and web applications  
- **Laravel Sanctum** – authentication and API token management  
- **Inertia.js (Laravel adapter)** – enables building SPA with Laravel and Vue.js  
- **Pusher PHP Server** – real-time event broadcasting  
- **Laravel Tinker** – interactive REPL for testing and debugging  

**Frontend:**
- **Vue.js (via Inertia.js)** – SPA frontend framework (Composition API recommended)  
- **Tailwind CSS** – utility-first CSS framework (used via frontend setup)  
- **Ziggy** – generates Laravel routes in JavaScript for frontend usage  

**Dev Tools / Testing:**
- **Laravel Breeze** – simple authentication scaffolding  
- **Laravel Sail** – local development environment (Docker)  
- **Laravel Pint** – code formatting / linting  
- **Laravel Pail** – logs management tool  
- **PHPUnit** – testing framework for PHP  
- **FakerPHP** – fake data generation  
- **Mockery** – mocking library for testing  
- **Nunomaduro Collision** – pretty error reporting for CLI  

**Build & Scripts:**
- **NPM / Node.js** – frontend dependencies management  
- **Vite** – modern build tool for frontend assets  
- **Concurrently** – run multiple development processes simultaneously  



## Installation & Setup

### 1. Clone the repository:
```bash
git clone https://github.com/Maxo1991/crypto-exchange.git
cd crypto-exchange
```

### 2. Install PHP and NPM
```bash
composer install
npm install --legacy-peer-deps
```

### 3. Create .env file and configure environment variables:
```bash
cp .env.example .env
```

    Set up your database credentials and Pusher keys.

### 4. Run migrations and seed the database:
```bash
php artisan migrate --seed
```

### 5. Create the storage folders (if missing):
```bash
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
chmod -R 775 storage
```

### 6. Start the development servers:
```bash
php artisan serve
npm run dev
```

    - Laravel backend will run on: http://127.0.0.1:8000
    - Vite (Vue.js) dev server will handle frontend assets and hot reload.


## Frontend Screens

### 1. Dashboard (`/dashboard`)
- **Limit Order Form** – form for buying and selling cryptocurrencies (Buy/Sell)  
- **Current Prices** – displays the current prices of different cryptocurrencies  
- **Wallet Overview** – table showing:  
  - USD balance  
  - Asset balances for all cryptocurrencies (BTC, ETH, ADA, USDT, BNB)  
- Real-time updates: balances and current prices refresh immediately when a trade is executed.

### 2. Orders Page (`/orders`)
- **Orders Table** – displays all user orders (open, filled, cancelled)  
- **Trades Table** – displays all executed trades  
- Allows viewing and tracking transaction history in real-time  


## Real-Time Updates

- **Pusher events** (`OrderMatched`)  
- **Private channels** (`private-user.{id}`)  
- **Instant updates** of balances, assets, and orders

## Author

**Igor Maksimović**
