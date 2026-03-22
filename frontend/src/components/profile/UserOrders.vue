<template>
    <div class="row my-5">
        <ProfileSidebar />
        <div class="col-md-8">
            <div class="card-body" v-if="orders.length">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Delivered at</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(order,index) in orders.slice(0,data.ordersToShow)"
                            :key="order.id"
                        >
                            <td>{{ index += 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-success my-1 rounded-0"
                                        v-for="product in order.products"
                                        :key="product.id"
                                    >
                                        {{ product.name }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-danger my-1 rounded-0"
                                        v-for="product in order.products"
                                        :key="product.id"
                                    >
                                        ${{ product.price }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ order.qty }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div 
                                        v-for="product in order.products"
                                        :key="product.id"
                                        class="my-1"
                                    >
                                        <!-- ✅ ADDED: Display color from pivot data -->
                                        <div 
                                            v-if="product.pivot?.color_id"
                                            class="border border-light-subtle border-1 rounded d-inline-block"
                                            :style="{
                                                backgroundColor: getColorName(product.pivot.color_id, product.colors),
                                                width: '25px',
                                                height: '25px'
                                            }"
                                            :title="getColorName(product.pivot.color_id, product.colors)"
                                        ></div>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span 
                                        v-for="product in order.products"
                                        :key="product.id"
                                        class="bg-light text-dark me-2 p-1 fw-bold my-1 d-inline-block"
                                    >
                                        <!-- ✅ ADDED: Display size from pivot data -->
                                        {{ getSizeName(product.pivot?.size_id, product.sizes) || '-' }}
                                    </span>
                                </div>
                            </td>
                            <td>${{ order.total }}</td>
                            <td>{{ order.created_at }}</td>
                            <td>
                                <span class="badge bg-success my-1 rounded-0"
                                    v-if="order.deliverd_at"
                                >
                                    {{ order.deliverd_at }}
                                </span>
                                <i v-else class="text-muted">Pending...</i>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    @click="viewOrder(order.id)"
                                    :disabled="data.isLoadingOrder"
                                >
                                    View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Alert v-else-if="!data.isLoading" content="No orders yet!" bgColor="primary" />
            <div class="d-flex justify-content-center" v-if="data.isLoading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button type="button" class="btn btn-sm btn-dark mt-3"
                    v-if="data.ordersToShow < orders.length"
                    @click="loadMoreOrders"
                >
                    <i class="bi bi-arrow-clockwise"></i> Load more
                </button>
            </div>
        </div>
        <OrderDetailModal
            :show="showOrderModal"
            :order="selectedOrder"
            @close="closeOrderModal"
        />
    </div>
</template>

<script setup>
    import { useAuthStore } from "../../stores/useAuthStore"
    import ProfileSidebar from "./ProfileSidebar.vue"
    import OrderDetailModal from "./OrderDetailModal.vue"
    import Alert from "../layouts/Alert.vue"
    import { ref, reactive, onMounted } from "vue"
    import axios from "axios"
    import { useToast } from "vue-toastification"

    const authStore = useAuthStore()
    const toast = useToast()

    const orders = ref([])
    const showOrderModal = ref(false)
    const selectedOrder = ref(null)

    const data = reactive({
        ordersToShow: 4,
        isLoading: false,
        isLoadingOrder: false
    })

    const getColorName = (colorId, colors) => {
        if (!colorId || !colors || !Array.isArray(colors)) return '#ccc'
        const color = colors.find(c => c.id === colorId)
        return color ? color.name : '#ccc'
    }

    const getSizeName = (sizeId, sizes) => {
        if (!sizeId || !sizes || !Array.isArray(sizes)) return null
        const size = sizes.find(s => s.id === sizeId)
        return size ? size.name : null
    }

    const loadMoreOrders = () => {
        if (data.ordersToShow < orders.value.length) {
            data.ordersToShow = data.ordersToShow + 4
        }
    }

    const viewOrder = async (orderId) => {
        data.isLoadingOrder = true
        try {
            const response = await axios.get(`/orders/${orderId}`)
            selectedOrder.value = response.data.data?.order ?? null
            showOrderModal.value = true
        } catch (error) {
            console.error('Error fetching order:', error)
            toast.error('Failed to load order details')
        } finally {
            data.isLoadingOrder = false
        }
    }

    const closeOrderModal = () => {
        showOrderModal.value = false
        selectedOrder.value = null
    }

    onMounted(async () => {
        data.isLoading = true
        try {
            const response = await axios.get('/orders')
            orders.value = response.data.data?.orders ?? []
        } catch (error) {
            console.error('Error fetching orders:', error)
            orders.value = []
        } finally {
            data.isLoading = false
        }
    })
</script>

<style scoped>
</style>