<template>
    <div>
        <header>
            <nav class="flex px-8 py-6">
                <div class="flex-1">
                    <svg class="w-8 inline-block align-middle" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 32C0 14.327 14.327 0 32 0c17.673 0 32 14.327 32 32 0 17.673-14.327 32-32 32C14.327 64 0 49.673 0 32Z" fill="#F60"></path>
                        <path d="M63.6 37.8c-3 12-7.8 15-7 14.6 0 0-3 5-13 9.4L21 42.2c19.434-34.6 13.154-3.8 24-18l18.6 13.6Z" fill="#0A0A0A" fill-opacity=".24"></path>
                        <path fill="#F60" d="M22.4 24H42v15.6H22.4z"></path>
                        <path d="M19 31.45h7.75v-9.3H19v9.3Zm0 10.85h7.75V33H19v9.3Zm9.3 0h7.75V33H28.3v9.3Zm9.3 0h7.75V33H37.6v9.3Zm-9.3-10.85h7.75v-9.3H28.3v9.3Zm9.3-9.3v9.3h7.75v-9.3H37.6Z" fill="#fff"></path>
                    </svg><span class="ml-2 text-orange-500 font-medium align-middle text-lg login-header"> Benotes NEXT </span>
                </div><div class="">
                <a href="https://github.com/coolycow/benotes" target="_blank">
                    <img class="w-8 inline-block" src="/GitHub-Mark-Light-64px.png" alt="GitHub">
                </a>
            </div>
            </nav>
        </header>
        <div class="flex justify-center h-full">
            <div class="pt-2 text-center">
                <h1 class="text-4xl md:text-5xl font-bold my-2 text-gradient">Benotes NEXT: Заметки и закладки</h1>
                <h2 class="text-3xl md:text-4xl font-normal my-4 text-white">Сохраняйте всю свою информацию</h2>
                <h2 class="text-3xl md:text-4xl font-bold my-2 text-gradient">в одном месте.</h2>
            </div>
        </div>
        <div class="flex justify-center mt-10 h-full">
            <div class="w-full max-w-2xl">
                <form class="shadow-md px-12 pt-12 pb-16 mb-4 login" @submit.prevent="authenticate">
                    <div class="mb-4">
                        <svg-vue class="w-16 block m-auto" icon="logo_64x64" />
                    </div>

                    <div class="mb-2">
                        <label class="label" for="email">Email</label>
                        <input
                            v-model="email"
                            class="input"
                            type="email"
                            name="email"
                            placeholder="Email Address"
                            autofocus
                            required />
                        <!-- Message after code is sent -->
                        <div class="text-sm text-gray-600" v-if="isCodeSent">
                            A confirmation code has been sent to your email.
                        </div>
                    </div>

                    <!-- Button to send code -->
                    <div class="mb-2" v-if="!isCodeSent">
                        <button
                            @click.prevent="sendCode"
                            class="button w-full">
                            Send Code
                        </button>
                    </div>

                    <div class="mb-2" v-if="isCodeSent">
                        <label class="label" for="code">Confirmation Code</label>
                        <input
                            v-model="code"
                            @input="filterDigits"
                            class="input"
                            type="text"
                            name="code"
                            placeholder="Enter code from email, example: 123456"
                            minlength="6"
                            maxlength="6"
                            required />
                    </div>

                    <div class="mb-2 flex items-center justify-between" v-if="isCodeSent">
                        <button class="button w-full" type="submit">Login</button>
                    </div>

                    <div class="">
                        <p v-if="error" class="text-red-500 text-sm italic">
                            {{ error }}
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios'
import InputMask from 'vue-input-mask';
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
                .then(response => {
                    if (response.status === 201) {
                        this.isCodeSent = true;
                        this.error = '';
                    } else if (response.status === 200) {
                        this.isCodeSent = true;
                        this.error = response.data?.message;
                    }
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
                        this.error = error.response?.data?.message || 'Confirmation code is invalid'
                })
        },

        filterDigits(event) {
            this.code = event.target.value.replace(/[^0-9]/g, '').slice(0, 6)
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
.login-header {
    font-weight: 400;
    font-size: 1rem;
    line-height: 1.75rem;
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
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type="number"] {
    -moz-appearance: textfield;
}
</style>
