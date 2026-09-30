export interface City {
  id: number
  name: string
  name_tr: string | null
  name_fa: string | null
  slug: string
  image: string | null
  is_active: boolean
}

export type CategoryContentType = 'none' | 'projects' | 'products' | 'services'

export interface Category {
  id: number
  parent_id: number | null
  name: string
  name_tr: string | null
  name_fa: string | null
  slug: string
  icon: string | null
  content_type: CategoryContentType
  is_active: boolean
  children: Category[]
}

export interface MenuItem {
  id: number
  name: string
  description: string | null
  price: number
  image: string | null
  is_available: boolean
}

export interface Menu {
  id: number
  name: string
  items: MenuItem[]
}

export interface Review {
  id: number
  rating: number
  body: string | null
  user_name: string | null
  is_own?: boolean
  created_at: string
}

export type PropertyType = 'apartment' | 'villa' | 'land' | 'commercial' | 'other'
export type ListingType = 'sale' | 'rent' | 'presale'
export type RentalPeriod = 'daily' | 'monthly' | 'long_term' | null
export type ListingStatus = 'pending' | 'approved' | 'rejected'

export interface BusinessProject {
  id: number
  business_id: number
  business?: Business
  title: string
  description: string | null
  images: string[]
  property_type: PropertyType
  listing_type: ListingType
  rental_period: RentalPeriod
  available_from: string | null
  price: number | null
  currency: string
  area_m2: number | null
  bedrooms: number | null
  bathrooms: number | null
  max_guests: number | null
  minimum_stay: number | null
  floor: string | null
  amenities: string[]
  address: string | null
  booking_url: string | null
  lat: number | null
  lng: number | null
  status: ListingStatus
  is_featured: boolean
  view_count: number
  created_at: string
}

export interface BusinessProduct {
  id: number
  name: string
  description: string | null
  price: number | null
  image: string | null
  is_available: boolean
}

export interface BusinessServiceItem {
  id: number
  name: string
  description: string | null
  price: number | null
  duration_minutes: number | null
  image: string | null
  is_available: boolean
}

export interface Business {
  id: number
  name: string
  slug: string
  description: string | null
  address: string | null
  lat: number | null
  lng: number | null
  phone: string | null
  whatsapp: string | null
  email: string | null
  website: string | null
  website_secondary: string | null
  notes: string | null
  logo: string | null
  cover_image: string | null
  gallery: string[]
  social: { instagram?: string; facebook?: string; twitter?: string }
  hours: Record<string, string>
  amenities: string[]
  languages: string[]
  payment_methods: string[]
  is_open_now: boolean | null
  last_verified_at: string | null
  rating_avg: number
  rating_count: number
  external_rating_avg: number
  external_rating_count: number
  view_count: number
  status: 'pending' | 'approved' | 'rejected' | 'expired'
  is_verified: boolean
  is_featured: boolean
  city?: City
  category?: Category
  package?: Package | null
  menus?: Menu[]
  reviews?: Review[]
  projects?: BusinessProject[]
  products?: BusinessProduct[]
  services?: BusinessServiceItem[]
  is_favorited?: boolean
  distance_km?: number
  created_at: string
}

export interface Deal { id:number; title:string; description:string|null; code:string|null; discount_label:string|null; image:string|null; starts_at:string|null; ends_at:string|null; business:Business }
export interface LocalEvent { id:number; title:string; description:string|null; venue:string|null; image:string|null; starts_at:string; ends_at:string|null; price:number|null; currency:string; booking_url:string|null; city?:City; business?:Business }

export interface Advertisement {
  id: number
  title: string
  image: string
  target_url: string | null
  placement: string
  city_id: number | null
  city?: City
  starts_at: string | null
  ends_at: string | null
  is_active: boolean
  sort_order: number
}

export interface Package {
  id: number
  name: string
  description: string | null
  price: number
  duration_days: number
  is_featured: boolean
  is_premium: boolean
  listing_limit: number | null
  features: string[]
}

export interface SubscriptionRequest {
  id: number
  status: 'pending' | 'approved' | 'rejected'
  note: string | null
  package: Package
  business_name?: string
  created_at: string
}

export type AccountType = 'user' | 'business' | 'admin'

export interface User {
  id: number
  name: string
  email: string
  phone: string | null
  account_type: AccountType
  created_at: string
}

export interface Paginated<T> {
  data: T[]
  meta?: {
    current_page: number
    last_page: number
    total: number
  }
  links?: unknown
}

export interface HomeData {
  featured: Business[]
  popular: Business[]
  ads: Advertisement[]
  view_counter: number
}
