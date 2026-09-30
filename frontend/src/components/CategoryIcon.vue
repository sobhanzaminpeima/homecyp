<script setup lang="ts">
import { computed } from 'vue'
import {
  Utensils,
  UtensilsCrossed,
  Coffee,
  Building2,
  Home,
  Car,
  Scissors,
  ShoppingCart,
  Store,
  Hammer,
  HardHat,
  Dice5,
  ShoppingBag,
  MoreHorizontal,
  Tag,
  type LucideProps,
} from 'lucide-vue-next'
import type { FunctionalComponent } from 'vue'

const props = withDefaults(defineProps<{ icon?: string | null; size?: number }>(), {
  size: 22,
})

const ICONS: Record<string, FunctionalComponent<LucideProps>> = {
  utensils: Utensils,
  'utensils-crossed': UtensilsCrossed,
  coffee: Coffee,
  'building-2': Building2,
  home: Home,
  car: Car,
  scissors: Scissors,
  'shopping-cart': ShoppingCart,
  store: Store,
  hammer: Hammer,
  'hard-hat': HardHat,
  'dice-5': Dice5,
  'shopping-bag': ShoppingBag,
  'more-horizontal': MoreHorizontal,
}

const resolved = computed(() => (props.icon && ICONS[props.icon]) || Tag)
</script>

<template>
  <component :is="resolved" :size="size" />
</template>
