<script>
import GuestVue from "../../layouts/Guest.vue"
import { useForm } from "@inertiajs/vue3";

export default {
    layout: GuestVue,
    data(){
        return{
            form : useForm({
                loginAs: '',
                email: '',
                password: ''
            })
        }
    },
    methods:{
        login(){
            this.form.post('login', {
            onFinish: () => this.form.reset('password'),
            });
        }
    }
}
</script>

<template>
    <div class="card px-sm-6 px-0">
        <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
                <a href="#" class="app-brand-link gap-2">
                      <span class="app-brand-logo demo">
                          <img src="/public/logo.png" alt="logo" class="app-brand-logo demo" style="width: 260px; height: auto;"/>
                      </span>
                </a>
            </div>
            <!-- /Logo -->

            <form id="formAuthentication" class="mb-6">
                <div class="mb-6">
                    <label for="email" class="form-label">Login as</label>
                    <select v-model="form.loginAs" class="form-select" name="" id="">
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="parent">Parent</option>
                    </select>
                    <div v-if="form.errors.loginAs" class="text-danger">{{ form.errors.loginAs }}</div>
                </div>
                <div class="mb-6">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        required
                        v-model="form.email"
                        name="email"
                        placeholder="Enter your email"
                        autofocus />
                    <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                </div>
                <div class="mb-6 form-password-toggle">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group input-group-merge">
                        <input
                          type="password"
                          id="password"
                          v-model="form.password"
                          class="form-control"
                          name="password"
                          aria-describedby="password"
                        />
                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                    </div>
                    <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                </div>
                <div class="mb-8">
                    <div class="d-flex justify-content-between mt-8">
                        <div class="form-check mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="remember-me" />
                            <label class="form-check-label" for="remember-me"> Remember Me </label>
                        </div>
                        <a href="#">
                            <span>Forgot Password?</span>
                        </a>
                    </div>
                </div>
                <div class="mb-6">
                      <button @click.prevent="login" class="btn btn-primary d-grid w-100" type="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
</style>
