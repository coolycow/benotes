<template>
    <div>
        <div class="flex justify-center mt-10 h-full">
            <div class="pt-2 text-center">
                <h1 class="text-4xl md:text-5xl font-bold my-2 text-gradient">Benotes NEXT: Заметки и закладки</h1>
                <h2 class="text-3xl md:text-4xl font-medium my-4 text-white">Сохраняйте всю свою информацию</h2>
                <h2 class="text-3xl md:text-4xl font-bold my-2 text-gradient">в одном месте.</h2>
            </div>
        </div>
        <div class="flex justify-center mt-10 h-full">
            <div class="w-full max-w-2xl">
                <form class="shadow-md px-12 pt-12 pb-16 mb-4 login" @submit.prevent="authenticate">
                    <div class="mb-4">
                        <svg-vue class="w-16 block m-auto" icon="logo_64x64" />
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
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios'
export default {
    data() {
        return {
            email: '',
            code: '',
            error: '',
            isCodeSent: false,
        }
    },
    mounted() {
        document.body.classList.add('login-page')
    },
    beforeDestroy() {
        document.body.classList.remove('login-page')
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
.login {
    box-shadow:
        0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);

    border-radius: 10px;
    background-color: #fff;
}
.login-page {
    background-image: linear-gradient(
        rgb(31, 41, 55),
        rgb(17, 24, 39)
    );
}
.text-gradient {
    color: transparent;
    background-clip: text;
    background-image: linear-gradient(
        to right,
        rgb(234, 88, 12),
        rgb(126, 34, 206)
    );
}
</style>
