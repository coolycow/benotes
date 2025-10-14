<template>
    <div class="flex justify-center mt-32 h-full">
        <div class="w-full max-w-2xl">
            <form class="shadow-md px-12 pt-12 pb-16 mb-4" @submit.prevent="authenticate">
                <div class="mb-4">
                    <svg-vue class="w-16 block m-auto" icon="logo_64x64" />
                    <span
                        class="block my-2 text-2xl text-orange-600 font-semibold text-center align-middle">
                        Benotes
                    </span>
                </div>

                <div class="mb-8">
                    <label class="label" for="email">Email</label>
                    <input
                        v-model="email"
                        class="input"
                        type="email"
                        name="email"
                        placeholder="Email Address"
                        autofocus
                        required />
                </div>

                <!-- Button to send code -->
                <div class="mb-4" v-if="!isCodeSent">
                    <button
                        @click.prevent="sendCode"
                        class="button w-full">
                        Send Code
                    </button>
                </div>

                <!-- Message after code is sent -->
                <div class="mb-4 text-sm text-gray-600" v-if="isCodeSent">
                    A confirmation code has been sent to your email. Please check your inbox.
                </div>

                <!--<div class="mb-8">
                    <label class="label" for="password">Password</label>
                    <input
                        v-model="password"
                        class="input tracking-tighter"
                        type="password"
                        name="password"
                        placeholder="Password"
                        required />
                </div>-->

                <div class="mb-8" v-if="isCodeSent">
                    <label class="label" for="code">Confirmation Code</label>
                    <input
                        v-model="code"
                        class="input"
                        type="text"
                        name="code"
                        placeholder="Enter code from email"
                        required />
                </div>

                <div class="mb-12">
                    <p v-if="error" class="text-red-500 text-sm italic">
                        {{ error }}
                    </p>
                </div>

                <div class="flex items-center justify-between" v-if="isCodeSent">
                    <button class="button w-full" type="submit">Login</button>
                    <!--<router-link
                        to="/forgot"
                        class="inline-block align-baseline font-semibold text-sm text-orange-600 hover:text-orange-700">
                        Forgot Password?
                    </router-link>-->
                </div>
            </form>
        </div>
    </div>
</template>
<script>
import axios from 'axios'
export default {
    data() {
        return {
            email: '',
            // password: '',
            code: '',
            error: '',
            isCodeSent: false,
        }
    },
    methods: {
        sendCode() {
            if (!this.email) {
                this.error = 'Please enter your email.'
                return
            }

            axios
                .post('/api/auth/send-code', { email: this.email })
                .then(() => {
                    this.isCodeSent = true
                    this.error = ''
                })
                .catch((error) => {
                    this.error = error.response?.data?.message || 'Failed to send code'
                })
        },

        authenticate() {
            axios
                .post('/api/auth/login-code', {
                    email: this.email,
                    // password: this.password,
                    code: this.code,
                })
                .then((response) => {
                    const token = response.data.data.token.access_token
                    this.$cookie.set('token', token, { expires: 14, samesite: 'Strict' })
                    axios.defaults.headers.common = { Authorization: `Bearer ${token}` }
                    this.$store.dispatch('auth/fetchAuthUser')
                    this.$router.push({ path: '/' })
                })
                .catch((error) => {
                    if (error.response.data.length < 200) {
                        this.error = error.response.data
                    }
                })
        },
    },
}
</script>
<style>
.bg-gray-input {
    background-color: #ececec;
}
</style>
