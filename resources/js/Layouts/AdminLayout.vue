<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <Link :href="route('admin.dashboard')" class="text-xl font-bold text-gray-800">
                School Management System
              </Link>
            </div>
            
            <!-- Navigation Links -->
            <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
              <Link
                :href="route('admin.dashboard')"
                :class="[
                  $page.url === '/admin/dashboard'
                    ? 'border-blue-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
                ]"
              >
                Dashboard
              </Link>
              
              <!-- Employees Link - Only show if user has permission -->
              <Link
                v-if="$page.props.auth.user?.permissions?.includes('access-employee-workspace')"
                :href="route('admin.employees.index')"
                :class="[
                  $page.url.startsWith('/admin/employees')
                    ? 'border-blue-500 text-gray-900'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
                ]"
              >
                Employees
              </Link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="hidden sm:ml-6 sm:flex sm:items-center">
            <div class="ml-3 relative">
              <!-- User dropdown menu -->
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">{{ $page.props.auth.user?.name }}</span>
                <Link
                  :href="route('admin.profile.edit')"
                  class="text-sm text-gray-500 hover:text-gray-700"
                >
                  Profile
                </Link>
                <Link
                  :href="route('logout')"
                  method="post"
                  as="button"
                  class="text-sm text-gray-500 hover:text-gray-700"
                >
                  Log Out
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Heading -->
    <header class="bg-white shadow" v-if="$slots.header">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Page Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>