<template>
  <div class="modal fade" id="viewTailorModal" tabindex="-1" aria-labelledby="viewTailorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewTailorLabel">Tailor Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div v-if="tailor" class="row">
            <!-- User Information -->
            <div class="col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-header">
                  <h6 class="mb-0">User Information</h6>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tbody>
                        <tr>
                        <th>#</th>
                        <td>{{ tailor.user?.id }}</td>
                      </tr>
                      <tr>
                        <th>Name:</th>
                        <td>{{ tailor.user?.name }}</td>
                      </tr>
                      <tr>
                        <th>Email:</th>
                        <td>{{ tailor.user?.email }}</td>
                      </tr>
                      <tr>
                        <th>Role:</th>
                        <td>
                          <span :class="[
                            'badge',
                            {
                              'bg-danger': tailor.user?.role_id === 1,
                              'bg-primary': tailor.user?.role_id === 2,
                              'bg-success': tailor.user?.role_id === 3
                            }
                          ]">
                            {{ tailor.user?.role_id === 1 ? 'Admin' : 
                               tailor.user?.role_id === 2 ? 'Tailor' : 
                               tailor.user?.role_id === 3 ? 'User' : 'Unknown' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Tailor Shop Information -->
            <div class="col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-header">
                  <h6 class="mb-0">Tailor Shop Information</h6>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tbody>
                      <tr>
                        <th>Shop Name:</th>
                        <td>{{ tailor.tailor_shop?.name }}</td>
                      </tr>
                      <tr>
                        <th>Email:</th>
                        <td>{{ tailor.tailor_shop?.email }}</td>
                      </tr>
                      <tr>
                        <th>Contact:</th>
                        <td>{{ tailor.tailor_shop?.contact }}</td>
                      </tr>
                      <tr>
                        <th>Address:</th>
                        <td>{{ tailor.tailor_shop?.address }}</td>
                      </tr>
                      <tr>
                        <th>Status:</th>
                        <td>
                          <span :class="[
                            'badge',
                            {
                              'bg-success': tailor.tailor_shop?.status === 'active',
                              'bg-danger': tailor.tailor_shop?.status === 'inactive'
                            }
                          ]">{{ tailor.tailor_shop?.status }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Tailor Status Information -->
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">Tailor Status Information</h6>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <tbody>
                      <tr>
                        <th>Status:</th>
                        <td>
                          <span :class="[
                            'badge',
                            {
                              'bg-primary': tailor.status === 'pending',
                              'bg-success': tailor.status === 'approved',
                              'bg-danger': tailor.status === 'rejected'
                            }
                          ]">{{ tailor.status }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { Modal } from 'bootstrap';

export default {
  name: 'ViewTailor',
  props: {
    tailor: {
      type: Object,
      required: true
    }
  },
  setup() {
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleString();
    };

    const showModal = () => {
      new Modal(document.getElementById("viewTailorModal")).show();
    };

    return {
      showModal,
      formatDate
    };
  }
};
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.table th {
  width: 40%;
  font-weight: 600;
}

.badge {
  font-size: 0.875rem;
  padding: 0.5em 0.75em;
}
</style>
