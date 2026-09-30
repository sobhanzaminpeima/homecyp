import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import tr from './locales/tr.json'
import ru from './locales/ru.json'
import fa from './locales/fa.json'
import he from './locales/he.json'

export const SUPPORTED_LOCALES = ['en', 'tr', 'ru', 'fa', 'he'] as const
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number]
export const RTL_LOCALES: SupportedLocale[] = ['fa', 'he']

const STORAGE_KEY = 'ec_locale'

export function detectLocale(): SupportedLocale {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored && (SUPPORTED_LOCALES as readonly string[]).includes(stored)) {
    return stored as SupportedLocale
  }

  const browserLang = navigator.language?.slice(0, 2)
  if (browserLang && (SUPPORTED_LOCALES as readonly string[]).includes(browserLang)) {
    return browserLang as SupportedLocale
  }

  return 'en'
}

export function persistLocale(locale: SupportedLocale) {
  localStorage.setItem(STORAGE_KEY, locale)
}

export function applyDirection(locale: SupportedLocale) {
  document.documentElement.dir = RTL_LOCALES.includes(locale) ? 'rtl' : 'ltr'
  document.documentElement.lang = locale
}

/**
 * Cities/categories only carry translated names for `name_tr` and `name_fa`
 * (populated from the source directory data) — RU/HE fall back to the
 * English `name` since no translation exists for those yet.
 */
export function localizedName(
  entity: { name: string; name_tr?: string | null; name_fa?: string | null },
  locale: string,
): string {
  if (locale === 'fa' && entity.name_fa) return entity.name_fa
  if (locale === 'tr' && entity.name_tr) return entity.name_tr
  return entity.name
}

const i18n = createI18n({
  legacy: false,
  locale: detectLocale(),
  fallbackLocale: 'en',
  messages: { en, tr, ru, fa, he },
})

applyDirection(detectLocale())

export default i18n
