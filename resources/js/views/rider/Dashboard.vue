<template>
  <div class="pc-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-12">
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><router-link to="/rider/dashboard">Home</router-link></li>
              <li class="breadcrumb-item" aria-current="page">Dashboard</li>
            </ul>
          </div>
          <div class="col-12">
            <div class="d-flex align-items-center gap-2">
              <i class="ph-duotone ph-package text-primary" style="font-size: 1.75rem;"></i>
              <div>
                <h2 class="mb-0">Delivery Rider Dashboard</h2>
                <p class="text-muted mb-0 small">Pick up orders from restaurants and deliver to customers.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title text-muted mb-2 d-flex align-items-center gap-2">
              <i class="ph-duotone ph-info"></i> Your role
            </h5>
            <p class="card-text mb-0 small">
              You deliver orders from <strong>restaurants (vendors)</strong> to <strong>customers</strong>. When a restaurant marks an order as <strong>Ready</strong>, it appears in Deliveries. Accept it, then mark <strong>Delivered</strong> when complete.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body d-flex flex-column justify-content-center">
            <router-link to="/rider/deliveries" class="btn btn-primary d-inline-flex align-items-center">
              <i class="ph-duotone ph-package me-2" style="font-size: 1.25rem;"></i> Go to Deliveries
            </router-link>
            <p class="text-muted small mt-2 mb-0">View available orders and manage your assignments.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default { name: 'RiderDashboard' };
</script>
