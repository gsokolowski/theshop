<template>
    <div class="row my-5">
        <ProfileSidebar />
        <div class="col-md-8">
            <div class="card-body" v-if="authStore.user?.orders.length">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Product Price</th>
                            <th>Qty</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Delivered at</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(order,index) in authStore.user.orders.slice(0,data.ordersToShow)"
                            :key="index"
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
                        </tr>
                    </tbody>
                </table>
            </div>
            <Alert v-else content="No orders yet!" bgColor="primary" />
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-sm btn-dark mt-3"
                    v-if="data.ordersToShow < authStore.user?.orders?.length"
                    @click="loadMoreOrders"
                >
                    <i class="bi bi-arrow-clockwise"></i> Load more
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useAuthStore } from "../../stores/useAuthStore"
    import ProfileSidebar from "./ProfileSidebar.vue"
    import Alert from "../layouts/Alert.vue"
    import { reactive, onMounted } from "vue"

    //define how many orders to show on the user orders page
    const data = reactive({
        ordersToShow: 4
    })
    
    //define the store
    const authStore = useAuthStore()

    // ✅ ADDED: Helper function to get color name from color_id
    const getColorName = (colorId, colors) => {
        if (!colorId || !colors || !Array.isArray(colors)) return '#ccc'
        const color = colors.find(c => c.id === colorId)
        return color ? color.name : '#ccc'
    }

    // ✅ ADDED: Helper function to get size name from size_id
    const getSizeName = (sizeId, sizes) => {
        if (!sizeId || !sizes || !Array.isArray(sizes)) return null
        const size = sizes.find(s => s.id === sizeId)
        return size ? size.name : null
    }

    //define the function to load more orders
    const loadMoreOrders = () => {
        if(data.ordersToShow < authStore.user.orders.length) {
            data.ordersToShow = data.ordersToShow + 4
        }else {
            return 
        }
    }
    
    // Refresh user data (including orders) when component mounts to sync with backend changes
    onMounted(async () => {
        try {
            await authStore.getLoggedInUser()
        } catch (error) {
            console.error('Error refreshing user orders:', error)
        }
    })

</script>

<style scoped>
</style>