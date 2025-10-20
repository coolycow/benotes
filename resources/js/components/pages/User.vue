<template>
    <form v-if="isNew" class="mt-16 lg:mx-20 mx-10 pb-8" @submit.prevent="create()">
        <div class="max-w-lg">
            <div class="mb-8">
                <h1 class="text-3xl font-medium text-gray-800">Create new User</h1>
            </div>

            <div class="mb-8">
                <label class="label">Name</label>
                <input
                    v-model="name"
                    placeholder="Name"
                    type="text"
                    class="input"
                    required />
            </div>
            <div class="mb-8">
                <label class="label">Email</label>
                <input
                    v-model="email"
                    placeholder="Email"
                    type="email"
                    class="input"
                    required />
            </div>

            <p v-if="error" class="text-red-500 mt-4">
                {{ error }}
            </p>
        </div>
    </form>

    <div v-else class="mt-20 lg:mx-20 mx-10 pb-8">
        <div class="max-w-lg">
            <div class="mb-8">
                <h1 class="text-3xl font-medium text-gray-800">
                    {{ name }}
                </h1>
            </div>

            <div class="mb-8">
                <label class="label">Name</label>
                <input
                    v-model="name"
                    placeholder="Name"
                    type="text"
                    class="input"
                    required />
            </div>
            <div class="mb-8">
                <label class="label">Email</label>
                <input
                    v-model="email"
                    placeholder="Email"
                    type="email"
                    class="input"
                    required />
            </div>

            <p v-if="error" class="text-red-500 mt-4">
                {{ error }}
            </p>

            <h2 v-if="isOwner" class="text-xl my-2 text-gray-800">Preferences</h2>

            <div v-if="isOwner" class="mb-8">
                <label class="label">Theme</label>
                <Treeselect
                    v-model="selectedTheme"
                    :options="themes"
                    :clearable="false"
                    :close-on-select="true"
                    :clear-on-select="true"
                    placeholder=""
                    class="w-80" />
            </div>

            <div v-if="isOwner"  class="mb-14 py-6 px-6 bg-red-400 rounded">
                <h3 class="text-xl font-semibold mb-1">Logout</h3>
                <p class="mb-4">This will log you out of the application.</p>
                <button
                    title="Delete Collection"
                    class="button red mb-2 bg-white"
                    @click="logout()">
                    Logout
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
export default {
    name: 'User',
    components: {
        Treeselect,
    },
    props: ['id', 'isNew'],
    data() {
        return {
            name: null,
            email: null,
            theme: null,
            error: null,
            selectedTheme: null,
            themes: [
                { id: 'default', label: 'Default 🌕' },
                { id: 'dark', label: 'Dark 🌑' },
            ],
        }
    },
    watch: {
        selectedTheme(newTheme) {
            this.setTheme()
        },
    },
    methods: {
        update() {
            this.setTheme()

            if (
                this.name === this.authUser.name &&
                this.email === this.authUser.email &&
                this.theme === this.authUser.theme
            ) {
                return
            }

            this.error = ''
            const params = {}
            if (this.name !== this.authUser.name) params.name = this.name
            if (this.email !== this.authUser.email) params.email = this.email
            if (this.theme !== this.authUser.theme) params.theme = this.selectedTheme

            axios
                .patch('/api/users/' + this.id, params)
                .then((response) => {
                    const user = response.data.data
                    this.$store.dispatch('auth/setAuthUser', user)
                })
                .catch((error) => {
                    if (error.response && error.response.data) {
                        const data = error.response.data;

                        if (data.errors && typeof data.errors === 'object') {
                            // Если есть вложенные ошибки валидации
                            this.error = Object.values(data.errors).flat().join('\n');
                        } else if (typeof data === 'object') {
                            // Если ошибка не связана с валидацией, но пришла в виде объекта
                            const firstErrorKey = Object.keys(data)[0];
                            this.error = data[firstErrorKey].toString();
                        } else {
                            // Если пришла просто строка
                            this.error = data;
                        }
                    } else {
                        // Обработка других типов ошибок
                        this.error = 'An unknown error occurred.';
                    }
                })
        },
        create() {
            this.setTheme()
            axios
                .post('/api/users', {
                    name: this.name,
                    email: this.email,
                    theme: this.theme
                })
                .then((response) => {
                    this.$router.push({ path: '/users' })
                })
                .catch((error) => {
                    if (typeof error.response.data === 'object') {
                        const firstError =
                            error.response.data[Object.keys(error.response.data)[0]]
                        this.error = firstError.toString()
                    } else {
                        this.error = error.response.data
                    }
                })
        },
        del() {
            axios
                .delete('/api/users/' + this.id)
                .then(() => {
                    this.$router.push({ path: '/users' })
                })
                .catch((error) => {
                    if (error.response.headers['content-type'].includes('json')) {
                        this.error = error.response.data
                    } else {
                        this.error = 'Failed. Error ' + error.response.status
                    }
                })
        },
        setTheme() {
            if (!this.selectedTheme || this.selectedTheme == null) {
                return
            }

            this.theme = this.selectedTheme
            document.documentElement.classList.remove('default', 'dark')
            document.documentElement.classList.add(this.selectedTheme)
        },
        logout() {
            axios.post('/api/auth/logout').catch((error) => {
                console.log(error.response)
            })
            this.$cookie.delete('token')
            this.$router.push({ path: '/login' })
        },
    },
    computed: {
        ...mapState('auth', ['authUser']),
        isOwner() {
            return this.authUser.id == this.id
        },
    },
    created() {
        if (this.isNew) {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create User',
                button: {
                    label: 'Save',
                    callback: this.create,
                    icon: 'checkmark',
                },
            })
        } else {
            axios
                .get('/api/users/' + this.id)
                .then((response) => {
                    const user = response.data.data
                    this.name = user.name
                    this.email = user.email
                    this.theme = user.theme
                    this.selectedTheme = user.theme
                })
                .catch((error) => {
                    this.error = error
                })
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit User',
                button: {
                    label: 'Save',
                    callback: this.update,
                    icon: 'checkmark',
                },
                options: [
                    {
                        label: 'Delete',
                        longLabel: 'Delete User',
                        color: 'red',
                        icon: 'delete',
                        callback: this.del,
                        condition: true,
                    },
                ],
            })
        }
    },
}
</script>
