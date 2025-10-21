<template>
    <div class="py-12 px-12 md:px-40 md:pt-32 max-w-5xl">
        <div class="w-full collection">
            <h1 class="text-3xl font-bold mb-4">
                {{ headline }}
            </h1>
            <p class="text-xl mb-16">
                {{ description }}
            </p>

            <div class="mb-10">
                <label class="label">Name of Collection</label>
                <input
                    v-model="name"
                    placeholder="Enter collection name (e.g., Cars, Travel, Family, Books, Projects"
                    autofocus
                    class="input" />
            </div>

            <div class="mb-10">
                <label class="label">Subcollection of</label>
                <Treeselect
                    v-model="parentCollection"
                    :options="optionsCollections"
                    :close-on-select="true"
                    :clear-on-select="true"
                    :normalizer="normalizeNode"
                    placeholder=""
                    class="inline-block w-80">
                    <template #value-label="{ node }">
                        <div class="flex items-center">
                            <div v-if="node.raw.icon_id" class="mr-2">
                                <svg-vue
                                    v-if="isInline(node.raw.icon_id)"
                                    :icon="'glyphs/' + node.raw.icon_id"
                                    class="w-4 h-4" />
                                <svg v-else class="w-4 h-4">
                                    <use
                                        :xlink:href="'/glyphs.svg#' + node.raw.icon_id" />
                                </svg>
                            </div>
                            <span>{{ node.label }}</span>
                        </div>
                    </template>
                    <template #option-label="{ node }">
                        <div class="flex items-center">
                            <div v-if="node.raw.icon_id" class="mr-2">
                                <svg-vue
                                    v-if="isInline(node.raw.icon_id)"
                                    :icon="'glyphs/' + node.raw.icon_id"
                                    class="w-4 h-4" />
                                <svg v-else class="w-6 h-6">
                                    <use
                                        :xlink:href="'/glyphs.svg#' + node.raw.icon_id" />
                                </svg>
                            </div>
                            <span>{{ node.label }}</span>
                        </div>
                    </template>
                </Treeselect>
            </div>

            <div class="mb-10 relative">
                <label class="label">Collection Icon</label>
                <p class="mt-2 mb-4">Select an optional icon for your collection.</p>
                <button
                    class="border-2 border-gray-400 rounded py-2 px-2"
                    @click="openPicker()">
                    <svg-vue
                        v-if="collectionIconIsInline(iconId)"
                        :icon="'glyphs/' + iconId"
                        class="w-6 h-6" />
                    <svg v-else-if="iconId" class="w-6 h-6">
                        <use :xlink:href="'/glyphs.svg#' + iconId" />
                    </svg>
                    <svg-vue
                        v-else
                        icon="remix/folder-fill"
                        class="w-6 text-gray-500 fill-current align-text-bottom" />
                </button>
                <transition name="fade">
                    <IconPicker
                        v-if="showPicker"
                        @iconSelected="iconSelect"
                        class="mt-2" />
                </transition>
            </div>

            <div v-if="!isNew" class="mt-8 mb-8">
                <label class="label mb-2">Shared With Users</label>

                <div v-for="(share, index) in sharedUsers" :key="index" class="mb-4 flex space-x-4 items-center">
                    <Treeselect
                        :multiple="false"
                        :async="true"
                        :load-options="loadUsers"
                        v-model="share.guest_id"
                        placeholder="Select user"
                        class="w-80"
                        :clearable="false"
                        value-consists-of="LEAF_PRIORITY"
                        :default-options="userOptions"
                    >
                    </Treeselect>

                    <Treeselect
                        v-model="share.permission"
                        :options="permissions"
                        placeholder="Select permission"
                        class="w-48"
                        :clearable="false"/>

                    <button @click="removeShare(index)" class="text-red-600 hover:text-red-800 font-bold">
                        &times;
                    </button>
                </div>

                <button @click="addShare" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add User
                </button>
            </div>

            <div v-if="!isNew" class="mb-14 py-6 px-6 bg-red-400 rounded">
                <h3 class="text-xl font-semibold mb-1">Delete collection</h3>
                <p class="mb-4">Delete this collection and all its content.</p>
                <button
                    title="Delete Collection"
                    class="button red mb-2 bg-white"
                    @click="deleteCollection">
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
import { collectionIconIsInline } from '../../api/collection'
import IconPicker from '../IconPicker.vue'
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { ASYNC_SEARCH } from '@riophae/vue-treeselect'
export default {
    components: {
        IconPicker,
        Treeselect,
    },
    props: ['id', 'isNew'],
    data() {
        return {
            // Collection
            name: '',
            headline: this.isNew ? 'Create Collection' : 'Collection Settings',
            description: this.isNew
                ? 'Specify a name for your new collection.'
                : "Update your collection's title and public available URL.",
            isSupported: null,
            iconId: null,
            showPicker: false,
            optionsCollections: [],
            parentCollection: null,

            // Shares
            sharedUsers: [],
            userOptions: [],
            permissions: [
                { id: '1', label: 'Read' },
                { id: '2', label: 'Read & Write' },
                { id: '3', label: 'Read & Write & Delete' }
            ],
        }
    },
    methods: {
        normalizeNode(node) {
            return {
                id: node.id,
                label: node.name,
                children: node.nested?.length > 0 ? node.nested : node.children,
            }
        },
        isInline(id) {
            return collectionIconIsInline(Number(id))
        },
        create() {
            if (this.name.trim() === '') {
                this.$store.dispatch('notification/setNotification', {
                    type: 'error',
                    title: 'Empty name',
                    description: 'Name can not be empty or consist of only spaces',
                })
                return
            }

            axios
                .post('/api/collections', {
                    name: this.name,
                    parent_id: this.parentCollection,
                    icon_id: this.iconId,
                })
                .then((response) => {
                    //this.$store.dispatch('collection/addCollection', response.data.data)
                    this.$store.dispatch('collection/fetchCollections', {
                        nested: true,
                        force: true,
                    })
                    this.$router.push({ path: '/c/' + response.data.data.id })
                })
                .catch((error) => {
                    console.log(error.response.data)
                    this.$store.dispatch('notification/setNotification', {
                        type: 'error',
                        title: 'Error ' + error.response.status,
                        description:
                            error.response.data.errors?.content?.[0] ??
                            'Collection could not be created.',
                    })
                })
        },
        update() {
            this.$store.dispatch('collection/updateCollection', {
                id: this.id,
                name: this.name,
                parentId: this.parentCollection,
                iconId: this.iconId,
            })
            this.saveShares()
            this.$router.push({ path: '/c/' + this.id })
        },
        deleteCollection() {
            this.$store.dispatch('collection/deleteCollection', {
                id: this.id,
                nested: true,
            })
            this.$store.dispatch('notification/setNotification', {
                type: 'deletion',
                title: 'Collection deleted',
                description: 'Collection was successfully deleted.',
            })
            this.$router.push({ path: '/' })
        },
        addShare() {
            this.sharedUsers.push({ guest_id: null, label: null, permission: '1' });
        },
        removeShare(index) {
            this.sharedUsers.splice(index, 1);
        },
        loadUsers({ action, searchQuery, callback }) {
            if (action === ASYNC_SEARCH && searchQuery.length > 2) {
                axios.get('/api/users/search', {
                    params: { email: searchQuery },
                })
                    .then(response => {
                        const options = (response.data.data || []).map(user => ({
                            id: user.id,
                            label: user.email,
                        }));
                        callback(null, options);
                    })
                    .catch(error => {
                        console.error('Error loading users for Treeselect:', error);
                        callback(error);
                    });
            }
        },
        saveShares() {
            axios.post('/api/shares', {
                collection_id: this.id,
                guests: this.sharedUsers.filter(
                    share => share.guest_id !== null
                ),
            }).catch(console.error);
        },
        loadExistingShares() {
            axios.get('/api/shares/', {
                params: { collection_id: this.id }
            })
                .then(response => {
                    if (response.data.data) {
                        this.userOptions = response.data.data.map(share => ({
                            id: share.guest_id,
                            label: share.email,
                            permission: share.permission,
                        }));
                        this.sharedUsers = response.data.data.map(share => ({
                            guest_id: share.guest_id,
                            email: share.email,
                            label: share.email,
                            permission: share.permission,
                        }));
                    }
                })
                .catch(error => {
                    console.error('Failed to load shares:', error);
                });
        },
        openPicker() {
            this.showPicker = true
            document
                .querySelector('#app')
                .addEventListener('click', this.hidePicker, true)
        },
        hidePicker() {
            if (document.querySelector('#iconPicker').contains(event.target)) {
                return
            }
            this.showPicker = false
            document
                .querySelector('#app')
                .removeEventListener('click', this.hidePicker, true)
        },
        iconSelect(event) {
            this.iconId = Number(event.id)
            this.showPicker = false
            document
                .querySelector('#app')
                .removeEventListener('click', this.hidePicker, true)
        },
        collectionIconIsInline,
    },
    computed: {
        ...mapState('collection', ['collections']),
    },
    created() {
        if (!this.isNew) {
            if (parseInt(this.id) === 0) {
                this.$router.push({ path: '/' })
                return
            }

            axios
                .get('/api/collections/' + this.id)
                .then((response) => {
                    const collection = response.data.data

                    this.name = collection.name
                    this.iconId = collection.icon_id
                    this.parentCollection = collection.parent_id

                    this.loadExistingShares();
                })
                .catch((error) => {
                    console.log(error.response.data)
                })

            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit Collection',
                button: {
                    label: 'Save',
                    callback: this.update,
                    icon: 'checkmark',
                },
            })
        } else {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Collection',
                button: {
                    label: 'Save',
                    callback: this.create,
                    icon: 'checkmark',
                },
            })
        }
        // nested: true - обязательно! Иначе вся логика ломается.
        // В исходном контролере была заведомо ошибка перевода в bool и поэтому nested всегда был true.
        // После исправления контролера и использования корректного nested происходят ошибки:
        // Ломается меню коллекций в сайдбаре и селекторах на странице создания и редактирования коллекций.
        this.$store.dispatch('collection/fetchCollections', { nested: true }).then(() => {
            this.optionsCollections = this.optionsCollections.concat(this.collections)
        })
        navigator.permissions
            .query({ name: 'clipboard-write' })
            .then((result) => {
                this.isSupported = result.state === 'granted' || result.state === 'prompt'
            })
            .catch(() => {
                this.isSupported = false
            })
    },
}
</script>
<style lang="scss">
button.switch {
    @apply float-right border-2 uppercase font-medium tracking-wide text-sm px-2 mb-1;
    padding-top: 0.125rem;
    padding-bottom: 0.125rem;
    transition: color, background-color 0.2s;
}
button.switch:hover {
    @apply bg-white text-orange-600;
}
.collection {
    input.readonly {
        @apply text-white border-gray-700 bg-gray-600 w-auto;
    }
    .label.inline-block {
        display: inline-block;
    }
}
.button.red:hover {
    border-color: #fff;
}
</style>
