# Использование иконок в Treeselect

В этом документе описано, как добавить иконки в компонент Treeselect для отображения коллекций с их визуальными представлениями.

## Обзор

В приложении уже существует система иконок, которая использует два подхода:
- Встроенные SVG иконки через компонент `svg-vue`
- Внешние SVG иконки через файл `/glyphs.svg`

## Реализация

### 1. Импорт необходимых функций

```javascript
import { collectionIconIsInline } from '../api/collection'
```

### 2. Добавление шаблонов для отображения иконок

```vue
<Treeselect
    v-model="selectedCollectionId"
    :options="optionsCollections"
    :normalizer="normalizeNode"
    placeholder="Выберите коллекцию...">
    
    <!-- Шаблон для выбранного значения -->
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
    
    <!-- Шаблон для опций в списке -->
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
```

### 3. Добавление методов

```javascript
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
    // ... другие методы
}
```

## Обновленные компоненты

Следующие компоненты были обновлены для поддержки иконок в Treeselect:

1. **CollectionMenu.vue** - компонент для перемещения поста в другую коллекцию
2. **Post.vue** - компонент для создания/редактирования поста
3. **EditCollection.vue** - компонент для создания/редактирования коллекции

## Как это работает

1. **Функция нормализации** (`normalizeNode`) преобразует данные коллекций в формат, понятный Treeselect
2. **Проверка на встроенные иконки** (`isInline`) определяет, нужно ли использовать `svg-vue` или внешний SVG
3. **Доступ к данным** - через `node.raw.icon_id` получаем доступ к ID иконки коллекции
4. **Условное отображение** - иконка отображается только если у коллекции есть `icon_id`

## Примечания

- Иконки отображаются как в выбранном значении, так и в выпадающем списке
- Размер иконок можно настроить, изменив классы `w-4 h-4`
- Для опций в списке можно использовать немного большие иконки (`w-6 h-6`)
- Функция `collectionIconIsInline` определяет, является ли иконка встроенной или внешней

## Пример использования

```vue
<template>
    <div>
        <label class="label">Выберите коллекцию</label>
        <Treeselect
            v-model="selectedCollectionId"
            :options="optionsCollections"
            :normalizer="normalizeNode"
            placeholder="Выберите коллекцию...">
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
</template>

<script>
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { collectionIconIsInline } from '../api/collection'

export default {
    components: {
        Treeselect,
    },
    data() {
        return {
            selectedCollectionId: null,
            optionsCollections: [],
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
    },
    created() {
        // Загрузка коллекций
        this.$store.dispatch('collection/fetchCollections', { nested: true }).then(() => {
            this.optionsCollections = this.collections
        })
    },
}
</script>