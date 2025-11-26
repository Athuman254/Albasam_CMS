<template>
   <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
         <a href="#" class="app-brand-link">
            <span v-if="logo" class="app-brand-logo demo">
               <img :src="logo" alt="logo" >
            </span>
         </a>
      </div>
      
      <div class="menu-inner-shadow" ref="menuInnerShadow"></div>
      
      <div class="menu-inner" ref="menuInner">
         <ul v-if="user" class="menu-inner py-1 ps">
            <AdminMenu />
         </ul>
         <ul v-if="staff" class="menu-inner py-1 ps">
            <EmployeeMenu />
         </ul>
      </div>
   </aside>
</template>

<script>
import PerfectScrollbar from 'perfect-scrollbar'
import EmployeeMenu from "@layouts/shared/EmployeeMenu.vue";
import AdminMenu from "@layouts/shared/AdminMenu.vue";

export default {
   name: 'MenuWrapper',
   components: {AdminMenu, EmployeeMenu},
   computed: {
      logo() {
         return this.$page.props.logoUrl;
      },
      user() {
         return this.$page.props.auth.user;
      },
      staff() {
         return this.$page.props.auth.employee;
      },
   },
   mounted() {
      this.initMenu()
   },
   methods: {
      initMenu() {
         const el = this.$el
         const menuInner = this.$refs.menuInner
         
         el.classList.add('menu')
         el.classList.remove('menu-no-animation')
         el.classList.add('menu-vertical')
         
         // Attempt PerfectScrollbar or fallback
         try {
            this.ps = new PerfectScrollbar(menuInner, {
               suppressScrollX: true,
               wheelPropagation: false
            })
            
            // Sneat shadow visibility based on scroll
            menuInner.addEventListener('ps-scroll-y', () => {
               const shadow = this.$refs.menuInnerShadow
               const thumb = menuInner.querySelector('.ps__thumb-y')
               shadow.style.display = thumb?.offsetTop ? 'block' : 'none'
            })
         } catch (e) {
            console.warn('PerfectScrollbar failed, falling back to native scroll', e)
            menuInner.classList.add('overflow-auto')
         }
         
         // Watch screen resize
         window.addEventListener('resize', () => {
            this.ps?.update()
         })
      }
   },
   unmounted() {
      this.ps?.destroy()
   }
}
</script>

<style scoped>
.layout-menu {
   display: flex;
   flex-direction: column;
   height: 100vh;
   overflow: hidden;
   flex-shrink: 0;
}
.menu-inner {
   height: calc(100vh - 60px);
   position: relative;
   overflow: hidden;
}
</style>
