<script setup lang="ts">
// import { Link } from '@inertiajs/vue3';
import { ChevronDown, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';
// import { route } from 'ziggy-js';
import iconLight from '@/../../public/images/Icon2025black.webp';
import iconDark from '@/../../public/images/Icon2025white.webp';
import AdsContent from '../AdsContent.vue';
import type { Ads } from '@/types/api/Ad.type';

const isMenuOpen = ref(false);
const openMobileDropdown = ref<string | null>(null);

withDefaults(
    defineProps<{
        numJuegosSeleccionados: number;
        ads: Ads[];
        adsDrawerOpen?: boolean;
    }>(),
    {
        adsDrawerOpen: false,
    },
);

const emit = defineEmits(['update:adsDrawerOpen']);

function setAdsDrawerOpen(val: boolean) {
    emit('update:adsDrawerOpen', val);
}

const toggleMobileDropdown = (section: string) => {
    if (openMobileDropdown.value === section) {
        openMobileDropdown.value = null;
    } else {
        openMobileDropdown.value = section;
    }
};

type NavigationItems = {
    name: string;
    href: string;
    subItems?: { name: string; href: string }[];
};

const navigationItems: NavigationItems[] = [
    {
        name: 'INICIO',
        href: '/',
    },
    // {
    //     name: 'EVENTOS',
    //     href: '#',
    //     subItems: [
    //         { name: 'Conferencias', href: '#' },
    //         { name: 'Exhibiciones', href: '#' },
    //         { name: 'Talleres', href: '#' },
    //         { name: 'Calendario', href: '#' },
    //     ],
    // },
    // {
    //     name: 'TORNEOS',
    //     href: '#',
    //     subItems: [
    //         { name: 'League of Legends', href: '#' },
    //         { name: 'Valorant', href: '#' },
    //         { name: 'CS:GO', href: '#' },
    //         { name: 'Fortnite', href: '#' },
    //     ],
    // },
    { name: 'SPONSORS', href: '/#sponsors' },
    // { name: 'GALERÍA', href: '#' },
    { name: 'CONTACTO', href: '/#contact' },
];
</script>

<template>
    <div class="dark:bg-black w-full">
        <!-- Barra superior con información del evento -->
        <div class="dark:text-beige text-black dark:bg-black border-b border-gray/10 dark:border-beige/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-10 text-xs">
                    <div class="flex items-center space-x-4">
                        <span class="font-cinzel">DICIEMBRE 2, 3 y 4</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a
                            href="https://www.facebook.com/gamerfest.ec"
                            target="_blank"
                            class="hover:text-wine transition-colors duration-200 flex items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"
                                />
                            </svg>
                            Facebook
                        </a>
                        <a
                            href="https://www.instagram.com/gamerfest.ec"
                            target="_blank"
                            class="hover:text-wine transition-colors duration-200 flex items-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                />
                            </svg>
                            Instagram
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navbar principal -->
        <header class="dark:bg-black border-b border-gray/10 dark:border-beige/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="flex items-center group">
                            <picture class="select-none pointer-events-none mr-2">
                                <source :srcset="iconLight" media="(prefers-color-scheme: dark)" />
                                <img class="w-8 mx-auto" :src="iconDark" alt="Logo" />
                            </picture>
                            <div class="flex flex-col">
                                <span class="text-wine text-2xl font-bold font-cinzel tracking-wider">GAMERFEST</span>
                                <span class="dark:text-beige text-black text-sm font-cinzel tracking-wider">2026</span>
                            </div>
                        </a>
                    </div>

                    <!-- Navegación para pantallas medianas y grandes -->
                    <nav class="hidden lg:flex space-x-1">
                        <template v-for="item in navigationItems" :key="item.name">
                            <a
                                v-if="!item.subItems"
                                :href="item.href"
                                class="dark:text-beige hover:text-wine px-4 py-2 text-sm font-cinzel font-medium transition-colors duration-200 border-b-2 border-transparent hover:border-wine"
                            >
                                {{ item.name }}
                            </a>
                            <div v-else class="relative group">
                                <button
                                    class="dark:text-beige hover:text-wine px-4 py-2 text-sm font-cinzel font-medium transition-colors duration-200 border-b-2 border-transparent hover:border-wine flex items-center"
                                >
                                    {{ item.name }}
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 ml-1"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    class="absolute left-0 mt-2 w-48 bg-[#0a0a0a] border border-beige/10 rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50"
                                >
                                    <template v-for="subItem in item.subItems" :key="subItem.name">
                                        <a
                                            :href="subItem.href"
                                            class="block px-4 py-2 text-sm font-cinzel text-beige hover:bg-wine/10 hover:text-wine"
                                        >
                                            {{ subItem.name }}
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <AdsContent v-if="ads.length > 0" :ads="ads" :open="adsDrawerOpen" @update:open="setAdsDrawerOpen" />
                    </nav>

                    <!-- Botones de autenticación -->
                    <div class="flex items-center space-x-4">
                        <div class="hidden items-center space-x-4 lg:flex">
                            <template v-if="$page.props.auth.user">
                                <a
                                    href="/mis_inscripciones"
                                    class="dark:text-beige hover:text-wine text-sm font-cinzel transition-colors duration-200"
                                >
                                    Mis inscripciones
                                </a>
                            </template>
                            <template v-else>
                                <a href="/login" class="dark:text-beige hover:text-wine text-sm font-cinzel transition-colors duration-200">
                                    Iniciar sesión
                                </a>
                                <a
                                    href="/register"
                                    class="dark:text-beige border border-beige hover:bg-wine hover:border-wine hover:text-beige px-4 py-2 text-sm font-cinzel rounded transition-colors duration-200"
                                >
                                    Registrarse
                                </a>
                            </template>
                            <a
                                href="/carrito"
                                aria-label="Abrir carrito de inscripciones"
                                class="relative inline-flex items-center gap-2 dark:text-beige hover:text-wine px-3 py-2 text-sm font-cinzel transition-colors duration-200"
                            >
                                <ShoppingCart class="h-5 w-5" />
                                <span>Carrito</span>
                                <span v-if="numJuegosSeleccionados > 0" class="cart-item-number" aria-live="polite">
                                    {{ numJuegosSeleccionados }}
                                </span>
                            </a>
                        </div>
                        <!-- Botón de menú móvil -->
                        <button @click="isMenuOpen = !isMenuOpen" class="lg:hidden text-beige hover:text-wine focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    v-if="!isMenuOpen"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menú móvil -->
            <div v-if="isMenuOpen" class="w-full absolute bg-black/80 lg:hidden py-4 border-t border-beige/10 transition-all duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col space-y-2">
                        <template v-for="item in navigationItems" :key="item.name">
                            <a
                                v-if="!item.subItems"
                                :href="item.href"
                                class="dark:text-beige hover:text-wine px-4 py-2 text-sm font-cinzel font-medium transition-colors duration-200 border-b-2 border-transparent hover:border-wine"
                            >
                                {{ item.name }}
                            </a>
                            <!-- Dropdown móvil -->
                            <div v-else>
                                <button
                                    @click="toggleMobileDropdown(item.name)"
                                    class="flex justify-between items-center w-full text-beige hover:text-wine px-3 py-2 text-sm font-cinzel font-medium transition-colors duration-200"
                                >
                                    {{ item.name }}
                                    <ChevronDown
                                        class="h-4 w-4 duration-200"
                                        :class="{ 'transform rotate-180': openMobileDropdown === item.name }"
                                    ></ChevronDown>
                                </button>
                                <div v-if="openMobileDropdown === item.name" class="pl-4 py-2 space-y-2 bg-[#0a0a0a]/50">
                                    <template v-for="subItem in item.subItems" :key="subItem.name">
                                        <a
                                            :href="subItem.href"
                                            @click="isMenuOpen = false"
                                            class="block px-3 py-1 text-sm font-cinzel text-beige hover:text-wine"
                                        >
                                            {{ subItem.name }}
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <AdsContent v-if="ads.length > 0" :ads="ads" :open="adsDrawerOpen" @update:open="setAdsDrawerOpen" />

                        <!-- Accesos de cuenta y carrito móvil -->
                        <div class="mt-2 flex flex-col gap-2 border-t border-beige/10 pt-4">
                            <template v-if="$page.props.auth.user">
                                <a
                                    href="/mis_inscripciones"
                                    @click="isMenuOpen = false"
                                    class="text-beige hover:text-wine px-3 py-2 text-sm font-cinzel transition-colors duration-200"
                                >
                                    Mis inscripciones
                                </a>
                            </template>
                            <template v-else>
                                <a
                                    href="/login"
                                    @click="isMenuOpen = false"
                                    class="text-beige hover:text-wine px-3 py-2 text-sm font-cinzel transition-colors duration-200"
                                >
                                    Iniciar sesión
                                </a>
                                <a
                                    href="/register"
                                    @click="isMenuOpen = false"
                                    class="text-beige border border-beige hover:bg-wine hover:border-wine hover:text-beige px-4 py-2 text-sm font-cinzel rounded transition-colors duration-200"
                                >
                                    Registrarse
                                </a>
                            </template>
                            <a
                                href="/carrito"
                                @click="isMenuOpen = false"
                                class="inline-flex items-center gap-2 text-beige hover:text-wine px-3 py-2 text-sm font-cinzel transition-colors duration-200"
                            >
                                <ShoppingCart class="h-5 w-5" />
                                Carrito ({{ numJuegosSeleccionados }})
                            </a>
                        </div>

                        <!-- Redes sociales móvil -->
                        <div class="pt-4 mt-2 border-t border-beige/10">
                            <p class="text-beige/70 text-xs mb-2 font-cinzel">SÍGUENOS:</p>
                            <div class="flex space-x-4">
                                <a
                                    href="https://www.facebook.com/gamerfest.ec"
                                    target="_blank"
                                    @click="isMenuOpen = false"
                                    class="text-beige hover:text-wine transition-colors duration-200"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"
                                        />
                                    </svg>
                                </a>
                                <a
                                    href="https://www.instagram.com/gamerfest.ec"
                                    target="_blank"
                                    @click="isMenuOpen = false"
                                    class="text-beige hover:text-wine transition-colors duration-200"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>
</template>
<style>
.cart-item-number {
    position: absolute;
    font-size: 10px;
    font-weight: bold;
    font-family: Arial, Helvetica, sans-serif;
    padding: 0.25em 0.35em;
    text-align: center;
    border-radius: 5px;
    background-color: #db005b;
    transform: translate(18px, -35px);
    min-width: 10px;
}
</style>
