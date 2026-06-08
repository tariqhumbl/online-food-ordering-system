<template>
  <div class="landing">
    <!-- Nav -->
    <header class="landing-header">
      <div class="landing-container">
        <router-link to="/" class="landing-logo">
          <i class="ph-duotone ph-fork-knife"></i>
          <span>FoodOrder</span>
        </router-link>
        <button class="landing-nav-toggle" type="button" aria-label="Menu" @click="navOpen = !navOpen">
          <i class="ph-duotone ph-list"></i>
        </button>
        <nav class="landing-nav" :class="{ open: navOpen }">
          <a href="#how-it-works" class="landing-nav-link" @click="navOpen = false">How it works</a>
          <a href="#restaurants" class="landing-nav-link" @click="navOpen = false">Restaurants</a>
          <template v-if="!isLoggedIn()">
            <router-link to="/login" class="landing-nav-link" @click="navOpen = false">Login</router-link>
            <router-link to="/register" class="landing-btn landing-btn-primary" @click="navOpen = false">Get started</router-link>
          </template>
          <router-link v-else to="/dashboard" class="landing-btn landing-btn-outline" @click="navOpen = false">Dashboard</router-link>
        </nav>
      </div>
    </header>

    <!-- Hero -->
    <section class="landing-hero">
      <div class="landing-hero-bg"></div>
      <div class="landing-container landing-hero-inner">
        <div class="landing-hero-content">
          <p class="landing-hero-badge">Add to cart · Place order · Delivered</p>
          <h1 class="landing-hero-title">Order from the best restaurants. Delivered to you.</h1>
          <p class="landing-hero-lead">Browse menus, add your favorites to the cart, and place your order in one click. Fast, simple, and reliable.</p>
          <div class="landing-hero-actions">
            <a href="#restaurants" class="landing-btn landing-btn-primary landing-btn-lg">
              <i class="ph-duotone ph-magnifying-glass"></i>
              Browse & add to cart
            </a>
            <router-link v-if="!isLoggedIn()" to="/register" class="landing-btn landing-btn-ghost landing-btn-lg">
              Create free account
            </router-link>
          </div>
        </div>
        <div class="landing-hero-visual">
          <div class="landing-hero-card">
            <i class="ph-duotone ph-shopping-cart landing-hero-icon"></i>
            <span>Add to cart</span>
          </div>
          <div class="landing-hero-card">
            <i class="ph-duotone ph-credit-card landing-hero-icon"></i>
            <span>Place order</span>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="landing-section landing-section-alt">
      <div class="landing-container">
        <h2 class="landing-section-title">How it works</h2>
        <p class="landing-section-lead">Three steps to your meal: browse, add to cart, place order.</p>
        <div class="landing-steps">
          <div class="landing-step">
            <div class="landing-step-num">1</div>
            <div class="landing-step-icon"><i class="ph-duotone ph-storefront"></i></div>
            <h3 class="landing-step-title">Browse restaurants</h3>
            <p class="landing-step-text">Explore menus from top restaurants near you.</p>
          </div>
          <div class="landing-step">
            <div class="landing-step-num">2</div>
            <div class="landing-step-icon"><i class="ph-duotone ph-shopping-cart"></i></div>
            <h3 class="landing-step-title">Add to cart</h3>
            <p class="landing-step-text">Pick your dishes and add them to your cart in one click.</p>
          </div>
          <div class="landing-step">
            <div class="landing-step-num">3</div>
            <div class="landing-step-icon"><i class="ph-duotone ph-rocket-launch"></i></div>
            <h3 class="landing-step-title">Place order</h3>
            <p class="landing-step-text">Check out and get your food delivered to your door.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="landing-stats">
      <div class="landing-container">
        <div class="landing-stats-grid">
          <div class="landing-stat">
            <span class="landing-stat-value">500+</span>
            <span class="landing-stat-label">Restaurants</span>
          </div>
          <div class="landing-stat">
            <span class="landing-stat-value">50k+</span>
            <span class="landing-stat-label">Orders delivered</span>
          </div>
          <div class="landing-stat">
            <span class="landing-stat-value">Fast</span>
            <span class="landing-stat-label">Delivery</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured restaurants -->
    <section id="restaurants" class="landing-section">
      <div class="landing-container">
        <h2 class="landing-section-title">Featured restaurants</h2>
        <p class="landing-section-lead">View menus, add to cart, and place your order. All in one place.</p>

        <div v-if="loading" class="landing-loading">
          <div class="landing-spinner"></div>
          <p>Loading restaurants...</p>
        </div>
        <div v-else-if="error" class="landing-message landing-message-warn">{{ error }}</div>
        <div v-else-if="!restaurants.length" class="landing-empty">
          <i class="ph-duotone ph-storefront"></i>
          <p>No restaurants available yet. Check back soon.</p>
        </div>
        <div v-else class="landing-restaurants">
          <article
            v-for="r in restaurants"
            :key="r.id"
            class="landing-restaurant-card"
          >
            <div class="landing-restaurant-card-inner">
              <div class="landing-restaurant-badge">Delivery</div>
              <h3 class="landing-restaurant-name">{{ r.name }}</h3>
              <p class="landing-restaurant-desc">{{ r.description || 'Fresh food, delivered to you.' }}</p>
              <div class="landing-restaurant-meta">
                <span><i class="ph-duotone ph-map-pin"></i> {{ r.address || '—' }}</span>
                <span><i class="ph-duotone ph-truck"></i> {{ r.delivery_fee != null ? formatMoney(r.delivery_fee) : 'Free' }} · {{ r.estimated_delivery_minutes || '—' }} min</span>
              </div>
              
              <router-link :to="`/restaurant/${r.id}`" class="landing-btn landing-btn-primary landing-btn-block">
                <i class="ph-duotone ph-shopping-cart"></i>
                View menu & add to cart
              </router-link>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="landing-cta">
      <div class="landing-container">
        <h2 class="landing-cta-title">Ready to order?</h2>
        <p class="landing-cta-lead">Browse restaurants, add to cart, and place your order in seconds.</p>
        <a href="#restaurants" class="landing-btn landing-btn-primary landing-btn-lg">
          <i class="ph-duotone ph-shopping-bag"></i>
          Start ordering
        </a>
      </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
      <div class="landing-container">
        <div class="landing-footer-inner">
          <div class="landing-footer-brand">
            <i class="ph-duotone ph-fork-knife"></i>
            <span>FoodOrder</span>
          </div>
          <div class="landing-footer-links">
            <router-link to="/login">Login</router-link>
            <router-link to="/register">Register</router-link>
            <router-link to="/register?type=restaurant">List your restaurant</router-link>
            <a href="#restaurants">Restaurants</a>
          </div>
        </div>
        <p class="landing-footer-copy">© {{ new Date().getFullYear() }} FoodOrder. Order food online.</p>
      </div>
    </footer>
  </div>
</template>

<script>
import API from '@/services/api_service';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { isLoggedIn, getUser } from '@/services/auth_service';

export default {
  name: 'WebsiteHome',
  setup() {
    const router = useRouter();
    const restaurants = ref([]);
    const loading = ref(false);
    const error = ref('');
    const navOpen = ref(false);

    function formatMoney(val) {
      if (val == null) return '—';
      return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(Number(val));
    }

    onMounted(() => {
      if (isLoggedIn()) {
        const user = getUser();
        const roleId = user?.role_id;
        const pathMap = { 1: '/admin/dashboard', 2: '/vendor/dashboard', 3: '/customer/dashboard', 4: '/rider/dashboard' };
        router.replace(pathMap[roleId] || '/customer/dashboard');
        return;
      }
      loading.value = true;
      API.restaurants
        .list({})
        .then((res) => {
          restaurants.value = res.data?.data ?? res.data ?? [];
        })
        .catch(() => {
          error.value = 'Could not load restaurants.';
        })
        .finally(() => (loading.value = false));
    });

    return {
      restaurants,
      loading,
      error,
      navOpen,
      formatMoney,
      isLoggedIn,
    };
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.landing {
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
  min-height: 100vh;
  background: #fafafa;
}

:root.landing-theme {
  --lp-primary: #c2410c;
  --lp-primary-hover: #ea580c;
  --lp-dark: #0f172a;
  --lp-dark-soft: #1e293b;
  --lp-text: #334155;
  --lp-muted: #64748b;
  --lp-bg: #fafafa;
  --lp-card: #ffffff;
  --lp-radius: 16px;
  --lp-shadow: 0 4px 24px rgba(0,0,0,0.06);
  --lp-shadow-hover: 0 12px 40px rgba(0,0,0,0.12);
}

/* Header */
.landing-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: rgba(255,255,255,0.95);
  backdrop-filter: saturate(180%) blur(12px);
  border-bottom: 1px solid rgba(0,0,0,0.06);
}

.landing-header .landing-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 16px;
  padding-bottom: 16px;
}

.landing-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

.landing-logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 1.35rem;
  font-weight: 800;
  color: #0f172a;
  text-decoration: none;
}

.landing-logo i {
  font-size: 1.75rem;
  color: #c2410c;
}

.landing-nav-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 8px;
}

.landing-nav {
  display: flex;
  align-items: center;
  gap: 28px;
}

.landing-nav-link {
  color: #475569;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.95rem;
  transition: color 0.2s;
}

.landing-nav-link:hover {
  color: #0f172a;
}

.landing-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  text-decoration: none;
  transition: transform 0.2s, box-shadow 0.2s;
  border: none;
  cursor: pointer;
}

.landing-btn:active {
  transform: scale(0.98);
}

.landin-btn-primary,
.landing-btn-primary {
  background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
  color: #fff;
  box-shadow: 0 4px 14px rgba(194, 65, 12, 0.4);
}

.landing-btn-primary:hover {
  box-shadow: 0 6px 20px rgba(194, 65, 12, 0.5);
  color: #fff;
}

.landing-btn-outline {
  background: transparent;
  color: #0f172a;
  border: 2px solid #e2e8f0;
}

.landing-btn-outline:hover {
  border-color: #c2410c;
  color: #c2410c;
}

.landing-btn-ghost {
  background: rgba(255,255,255,0.15);
  color: #fff;
  border: 2px solid rgba(255,255,255,0.5);
}

.landing-btn-ghost:hover {
  background: rgba(255,255,255,0.25);
  color: #fff;
}

.landing-btn-lg {
  padding: 14px 28px;
  font-size: 1rem;
}

.landing-btn-block {
  width: 100%;
}

@media (max-width: 991px) {
  .landing-header .landing-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    padding-bottom: 14px;
  }
  .landing-nav-toggle { display: block; }
  .landing-nav {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    flex-direction: column;
    padding: 20px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    gap: 12px;
    display: none;
  }
  .landing-nav.open { display: flex; }
}

/* Hero */
.landing-hero {
  position: relative;
  padding: 140px 0 100px;
  overflow: hidden;
}

.landing-hero-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
  background-size: 200% 200%;
}

.landing-hero-inner {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
}

.landing-hero-badge {
  display: inline-block;
  padding: 8px 16px;
  background: rgba(194, 65, 12, 0.2);
  color: #fdba74;
  border-radius: 100px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 20px;
  letter-spacing: 0.02em;
}

.landing-hero-title {
  font-size: clamp(2.25rem, 5vw, 3.5rem);
  font-weight: 800;
  line-height: 1.15;
  color: #fff;
  margin-bottom: 20px;
  letter-spacing: -0.02em;
}

.landing-hero-lead {
  font-size: 1.15rem;
  line-height: 1.65;
  color: #94a3b8;
  margin-bottom: 32px;
  max-width: 480px;
}

.landing-hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.landing-hero-visual {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
}

.landing-hero-card {
  width: 160px;
  height: 160px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #e2e8f0;
  font-weight: 600;
  font-size: 0.9rem;
  transition: transform 0.3s, background 0.3s;
}

.landing-hero-card:hover {
  transform: translateY(-6px);
  background: rgba(255,255,255,0.12);
}

.landing-hero-icon {
  font-size: 3rem;
  color: #fdba74;
}

@media (max-width: 991px) {
  .landing-hero-inner { grid-template-columns: 1fr; text-align: center; }
  .landing-hero-lead { margin-left: auto; margin-right: auto; }
  .landing-hero-actions { justify-content: center; }
}

/* Sections */
.landing-section {
  padding: 80px 0;
}

.landing-section-alt {
  background: #fff;
}

.landing-section-title {
  font-size: clamp(1.75rem, 3vw, 2.25rem);
  font-weight: 800;
  color: #0f172a;
  text-align: center;
  margin-bottom: 12px;
  letter-spacing: -0.02em;
}

.landing-section-lead {
  text-align: center;
  color: #64748b;
  font-size: 1.05rem;
  margin-bottom: 48px;
  max-width: 560px;
  margin-left: auto;
  margin-right: auto;
}

/* How it works */
.landing-steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
}

.landing-step {
  position: relative;
  padding: 36px 28px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  border: 1px solid #f1f5f9;
  text-align: center;
  transition: transform 0.3s, box-shadow 0.3s;
}

.landing-step:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}

.landing-step-num {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #c2410c, #ea580c);
  color: #fff;
  border-radius: 50%;
  font-size: 0.9rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.landing-step-icon {
  font-size: 2.5rem;
  color: #c2410c;
  margin-bottom: 16px;
}

.landing-step-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 8px;
}

.landing-step-text {
  font-size: 0.95rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

@media (max-width: 767px) {
  .landing-steps { grid-template-columns: 1fr; }
}

/* Stats */
.landing-stats {
  padding: 48px 0;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.landing-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  text-align: center;
}

.landing-stat-value {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 4px;
}

.landing-stat-label {
  font-size: 0.9rem;
  color: #94a3b8;
}

@media (max-width: 767px) {
  .landing-stats-grid { grid-template-columns: 1fr; gap: 24px; }
}

/* Restaurants */
.landing-restaurants {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 28px;
}

.landing-restaurant-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  border: 1px solid #f1f5f9;
  transition: transform 0.3s, box-shadow 0.3s;
}

.landing-restaurant-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.12);
}

.landing-restaurant-card-inner {
  padding: 28px;
}

.landing-restaurant-badge {
  display: inline-block;
  padding: 6px 12px;
  background: #fef3c7;
  color: #b45309;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 700;
  margin-bottom: 14px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.landing-restaurant-name {
  font-size: 1.35rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 8px;
}

.landing-restaurant-desc {
  font-size: 0.95rem;
  color: #64748b;
  margin-bottom: 16px;
  line-height: 1.5;
}

.landing-restaurant-meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 20px;
}

.landing-restaurant-meta i {
  margin-right: 6px;
  color: #c2410c;
}

.landing-loading,
.landing-empty {
  text-align: center;
  padding: 60px 24px;
  color: #64748b;
}

.landing-loading .landing-spinner,
.landing-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f1f5f9;
  border-top-color: #c2410c;
  border-radius: 50%;
  animation: lp-spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes lp-spin {
  to { transform: rotate(360deg); }
}

.landing-empty i {
  font-size: 4rem;
  color: #cbd5e1;
  margin-bottom: 16px;
  display: block;
}

.landing-message {
  padding: 16px 24px;
  border-radius: 12px;
  text-align: center;
}

.landing-message-warn {
  background: #fef3c7;
  color: #b45309;
}

/* CTA */
.landing-cta {
  padding: 80px 0;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  text-align: center;
}

.landing-cta-title {
  font-size: clamp(1.75rem, 3vw, 2.25rem);
  font-weight: 800;
  color: #fff;
  margin-bottom: 12px;
}

.landing-cta-lead {
  color: #94a3b8;
  font-size: 1.05rem;
  margin-bottom: 28px;
}

.landing-cta .landing-btn-primary {
  box-shadow: 0 4px 20px rgba(194, 65, 12, 0.5);
}

/* Footer */
.landing-footer {
  padding: 40px 0 24px;
  background: #0f172a;
  color: #94a3b8;
}

.landing-footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.landing-footer-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  font-size: 1.1rem;
  color: #fff;
}

.landing-footer-brand i {
  color: #c2410c;
}

.landing-footer-links {
  display: flex;
  gap: 24px;
}

.landing-footer-links a {
  color: #94a3b8;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}

.landing-footer-links a:hover {
  color: #fff;
}

.landing-footer-copy {
  font-size: 0.85rem;
  margin: 0;
  color: #64748b;
}
</style>
