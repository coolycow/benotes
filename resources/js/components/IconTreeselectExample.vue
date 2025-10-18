<template>
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Пример использования иконок в Treeselect</h2>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Выберите коллекцию с иконками
            </label>
            <Treeselect
                v-model="selectedCollectionId"
                :options="optionsCollections"
                :normalizer="normalizeNode"
                placeholder="Выберите коллекцию..."
                class="w-full max-w-md">
                <template #value-label="{ node }">
                    <div class="flex items-center">
                        <div v-if="node.raw.icon_id" class="mr-2">
                            <svg-vue v-if="isInline(node.raw.icon_id)" :icon="'glyphs/' + node.raw.icon_id" class="w-4 h-4" />
                            <svg v-else class="w-4 h-4">
                                <use :xlink:href="'/glyphs.svg#' + node.raw.icon_id" />
                            </svg>
                        </div>
                        <span>{{ node.label }}</span>
                    </div>
                </template>
                <template #option-label="{ node }">
                    <div class="flex items-center">
                        <div v-if="node.raw.icon_id" class="mr-2">
                            <svg-vue v-if="isInline(node.raw.icon_id)" :icon="'glyphs/' + node.raw.icon_id" class="w-4 h-4" />
                            <svg v-else class="w-4 h-4">
                                <use :xlink:href="'/glyphs.svg#' + node.raw.icon_id" />
                            </svg>
                        </div>
                        <span>{{ node.label }}</span>
                    </div>
                </template>
            </Treeselect>
        </div>
        
        <div class="mt-4 p-4 bg-gray-100 rounded">
            <p class="text-sm text-gray-600">
                Выбранная коллекция: 
                <span v-if="selectedCollection" class="font-medium">
                    {{ selectedCollection.name }}
                </span>
                <span v-else class="italic">Нет</span>
            </p>
            <p v-if="selectedCollection && selectedCollection.icon_id" class="text-sm text-gray-600 mt-1">
                ID иконки: {{ selectedCollection.icon_id }}
            </p>
        </div>
    </div>
</template>

<script>
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { collectionIconIsInline } from './../../api/collection'
import { mapState } from 'vuex'

export default {
    name: 'IconTreeselectExample',
    components: {
        Treeselect,
    },
    data() {
        return {
            selectedCollectionId: null,
            optionsCollections: [],
        }
    },
    computed: {
        ...mapState('collection', ['collections']),
        selectedCollection() {
            if (!this.selectedCollectionId || !this.collections) return null
            return this.collections.find(c => c.id === this.selectedCollectionId)
        },
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
    },
    created() {
        const uncategorized = { name: 'Без коллекции', id: 0, nested: null, icon_id: null }
        this.optionsCollections.push(uncategorized)
        
        this.$store.dispatch('collection/fetchCollections', { nested: true }).then(() => {
            this.optionsCollections = this.optionsCollections.concat(this.collections)
        })
    },
}
</script>