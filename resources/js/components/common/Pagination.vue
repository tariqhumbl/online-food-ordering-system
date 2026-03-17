<template>
<div class="row" v-if="pages.total && pages.total > pages.per_page">
    <div class="col-md-12">
        <div class="card-footer">
            <div class="card-body">
                <div class="col-md-12 d-flex justify-content-center">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li class="page-item" :class="{ 'disabled': pages.prev_page_url === null }">
                                <a class="page-link" href="#" aria-label="Previous" @click.prevent="$emit('loadMore', pages.current_page - 1)">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <template v-if="pages.total > pages.per_page && pages.last_page > 13">
                                <li class="page-item" v-for="index in 10" :key="index" :class="{ 'active': index == pages.current_page }">
                                    <a class="page-link" href="#" :class="{ 'disabled': index == pages.current_page }" @click.prevent="$emit('loadMore', index)">
                                        {{ index }}
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link disabled" href="javascript:void(0);">...</a>
                                </li>
                                <li class="page-item" :class="{ 'active': pages.last_page - 1 == pages.current_page }">
                                    <a class="page-link" href="#" :class="{ 'disabled': pages.last_page - 1 == pages.current_page }" @click.prevent="$emit('loadMore', pages.last_page - 1)">
                                        {{ pages.last_page - 1 }}
                                    </a>
                                </li>
                                <li class="page-item" :class="{ 'active': pages.last_page == pages.current_page }">
                                    <a class="page-link" href="#" :class="{ 'disabled': pages.last_page == pages.current_page }" @click.prevent="$emit('loadMore', pages.last_page)">
                                        {{ pages.last_page }}
                                    </a>
                                </li>
                            </template>
                            <template v-else>
                                <li class="page-item" v-for="index in pages.last_page" :key="index" :class="{ 'active': index == pages.current_page }">
                                    <a class="page-link" href="#" :class="{ 'disabled': index == pages.current_page }" @click.prevent="$emit('loadMore', index)">
                                        {{ index }}
                                    </a>
                                </li>
                            </template>
                            <li class="page-item" :class="{ 'disabled': pages.next_page_url === null }">
                                <a class="page-link" href="#" aria-label="Next" @click.prevent="$emit('loadMore', pages.current_page + 1)">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import {
    ref
} from 'vue';
import {
    Modal
} from 'bootstrap';
export default {
    name: "Pagination",
    props: {
        pages: {
            type: Object,
            default: {}
        }
    }
};
</script>



<style scoped>
.custom-css-page-link {
    color: #007bff;
}
</style>
