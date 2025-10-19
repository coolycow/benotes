<template>
    <div class="text-gray-600 text-sm"
         :title="'Created by ' + email"
    >
        @{{ truncateName }}
    </div>
</template>

<style scoped lang="scss"></style>

<script>
import moment from 'moment/moment'

export default {
    props: ['name', 'email'],
    computed: {
        truncateName() {
            if (this.name.length > 30) {
                return this.name.substring(0, 30 - 3) + '...';
            }
            return this.name;
        },
    }
}
</script>
