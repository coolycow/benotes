<template>
    <div class="text-gray-600 text-sm"
         :title="'Created at: ' + formattedCreatedAt"
    >
        {{ humanUpdatedAt }}
    </div>
</template>

<style scoped lang="scss"></style>

<script>
import moment from 'moment';

export default {
    props: ['created_at', 'updated_at'],
    computed: {
        formattedCreatedAt() {
            return moment(String(this.created_at)).format('DD-MM-YYYY HH:mm:ss');
        },
        humanUpdatedAt() {
            return moment(String(this.updated_at)).fromNow();
        }
    }
}
</script>
