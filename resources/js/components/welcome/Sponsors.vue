<script setup lang="ts">
import { ExternalLink, Handshake } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface Sponsor {
    id: number;
    url_pagina?: string | null;
    url_imagen?: string | null;
    nombre: string;
    active: boolean;
}

const props = withDefaults(
    defineProps<{
        sponsors?: Sponsor[];
    }>(),
    {
        sponsors: () => [],
    },
);

const carousel = ref<HTMLElement | null>(null);
const track = ref<HTMLElement | null>(null);
const activeSponsors = computed(() => props.sponsors.filter((sponsor) => sponsor.active));
const placeholderSlots = [1, 2, 3];
const items = computed(() =>
    activeSponsors.value.length
        ? activeSponsors.value
        : placeholderSlots.map((slot) => ({
              id: -slot,
              nombre: 'Espacio para patrocinador',
              active: true,
              url_imagen: null,
              url_pagina: null,
          })),
);
const copies = [0, 1, 2, 3];
let animationFrame: number | undefined;
let resizeObserver: ResizeObserver | undefined;
let cycleWidth = 0;
let offset = 0;
let previousFrameTime = 0;
let dragStartX: number | null = null;
let dragStartOffset = 0;
let dragged = false;
const hovered = ref(false);
const focused = ref(false);
const pointerDown = ref(false);

const isPaused = computed(() => hovered.value || focused.value || pointerDown.value);

function initials(name: string) {
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase();
}

function applyOffset() {
    if (track.value) track.value.style.transform = `translate3d(${-offset}px, 0, 0)`;
}

function updateCycleWidth() {
    const first = track.value?.children.item(0);
    const second = track.value?.children.item(1);
    if (!first || !second) return;

    cycleWidth = second.getBoundingClientRect().left - first.getBoundingClientRect().left;
    if (cycleWidth > 0) {
        offset %= cycleWidth;
        applyOffset();
    }
}

function animate(timestamp: number) {
    if (!isPaused.value && cycleWidth > 0) {
        if (previousFrameTime > 0) {
            offset = (offset + ((timestamp - previousFrameTime) * 28) / 1000) % cycleWidth;
            applyOffset();
        }

        previousFrameTime = timestamp;
    } else {
        previousFrameTime = 0;
    }

    animationFrame = window.requestAnimationFrame(animate);
}

function startDrag(event: PointerEvent) {
    if (event.pointerType === 'mouse' && event.button !== 0) return;

    pointerDown.value = true;
    dragStartX = event.clientX;
    dragStartOffset = offset;
    dragged = false;
}

function moveDrag(event: PointerEvent) {
    if (dragStartX === null || cycleWidth <= 0) return;

    const delta = event.clientX - dragStartX;
    if (!dragged && Math.abs(delta) > 4) {
        dragged = true;
        carousel.value?.setPointerCapture(event.pointerId);
    }
    if (!dragged) return;

    event.preventDefault();
    offset = (((dragStartOffset - delta) % cycleWidth) + cycleWidth) % cycleWidth;
    applyOffset();
}

function stopDrag() {
    if (!pointerDown.value) return;
    pointerDown.value = false;
    dragStartX = null;
    window.setTimeout(() => {
        if (!pointerDown.value) dragged = false;
    }, 0);
}

function preventClickAfterDrag(event: MouseEvent) {
    if (!dragged) return;

    event.preventDefault();
    event.stopPropagation();
    dragged = false;
}

function handleFocusOut(event: FocusEvent) {
    if (!(event.currentTarget instanceof HTMLElement) || event.currentTarget.contains(event.relatedTarget as Node | null)) return;
    focused.value = false;
}

onMounted(() => {
    updateCycleWidth();
    resizeObserver = new ResizeObserver(updateCycleWidth);
    if (carousel.value) resizeObserver.observe(carousel.value);
    if (track.value) resizeObserver.observe(track.value);
    animationFrame = window.requestAnimationFrame(animate);
});

onBeforeUnmount(() => {
    if (animationFrame !== undefined) window.cancelAnimationFrame(animationFrame);
    resizeObserver?.disconnect();
});
</script>

<template>
    <section id="sponsors" aria-labelledby="sponsors-heading" class="w-full scroll-mt-6 py-16">
        <div class="mx-auto max-w-7xl px-6">
            <h2 id="sponsors-heading" class="mb-4 text-center font-cinzel text-2xl font-bold tracking-wider text-wine md:text-3xl dark:text-beige">
                NUESTROS PATROCINADORES
            </h2>
            <p class="mb-8 text-center text-sm text-black/70 dark:text-beige/70">Marcas que hacen posible GamerFest.</p>

            <div
                ref="carousel"
                aria-label="Carrusel infinito de patrocinadores"
                class="cursor-grab overflow-hidden touch-pan-y active:cursor-grabbing"
                @mouseenter="hovered = true"
                @mouseleave="hovered = false"
                @focusin="focused = true"
                @focusout="handleFocusOut"
                @pointerdown="startDrag"
                @pointermove="moveDrag"
                @pointerup="stopDrag"
                @pointercancel="stopDrag"
                @lostpointercapture="stopDrag"
                @click.capture="preventClickAfterDrag"
            >
                <div ref="track" class="flex w-max select-none gap-4 py-3 will-change-transform">
                    <div v-for="copy in copies" :key="copy" class="flex shrink-0 gap-4" :aria-hidden="copy > 0 ? 'true' : undefined">
                        <article
                            v-for="sponsor in items"
                            :key="`${copy}-${sponsor.id}`"
                            class="flex min-h-56 w-64 shrink-0 flex-col items-center justify-center rounded-xl border border-wine/20 bg-white/70 p-6 text-center shadow-sm dark:border-beige/20 dark:bg-white/5"
                        >
                            <div class="mb-4 flex h-28 w-full items-center justify-center overflow-hidden rounded-lg bg-black/5 p-3 dark:bg-black/30">
                                <img
                                    v-if="sponsor.url_imagen"
                                    :src="sponsor.url_imagen"
                                    :alt="`Logo de ${sponsor.nombre}`"
                                    :draggable="false"
                                    loading="lazy"
                                    class="max-h-full max-w-full object-contain"
                                />
                                <span
                                    v-else-if="activeSponsors.length"
                                    aria-hidden="true"
                                    class="flex h-16 w-16 items-center justify-center rounded-full bg-wine/10 font-cinzel text-xl font-bold text-wine dark:bg-beige/10 dark:text-beige"
                                >
                                    {{ initials(sponsor.nombre) || 'GF' }}
                                </span>
                                <Handshake v-else class="h-10 w-10 text-wine/70 dark:text-beige/70" />
                            </div>
                            <h3 class="font-cinzel font-semibold text-black dark:text-beige">
                                {{ activeSponsors.length ? sponsor.nombre : 'Espacio para patrocinador' }}
                            </h3>
                            <p v-if="!activeSponsors.length" class="mt-2 text-xs text-black/50 dark:text-beige/50">Próximamente</p>
                            <a
                                v-else-if="sponsor.url_pagina"
                                :href="sponsor.url_pagina"
                                target="_blank"
                                rel="noopener noreferrer"
                                :draggable="false"
                                class="mt-3 inline-flex items-center gap-1 text-sm text-wine underline-offset-4 hover:underline dark:text-beige"
                            >
                                Visitar sitio
                                <ExternalLink class="h-3.5 w-3.5" />
                            </a>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
