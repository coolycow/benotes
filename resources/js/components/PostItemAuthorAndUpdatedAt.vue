<template>
    <div class="text-gray-600 text-sm mb-1">
        @{{ truncateName }} · {{ humanUpdatedAt }}
    </div>
</template>

<style scoped lang="scss"></style>

<script>
import moment from 'moment';

export default {
    props: ['name', 'email', 'created_at', 'updated_at'],
    computed: {
        truncateName() {
            if (this.name.length > 30) {
                return this.name.substring(0, 30 - 3) + '...';
            }
            return this.name;
        },
        formattedCreatedAt() {
            return moment(String(this.created_at)).format('DD-MM-YYYY HH:mm:ss');
        },
        humanUpdatedAt() {
            return moment(String(this.updated_at)).fromNow();
        }
    }
}
</script>
