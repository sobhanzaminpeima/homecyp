# Architecture

HomeCyp is a Laravel monolith with server-rendered Blade pages, Livewire chat, and Filament administration.

```text
Browser
  ├─ Blade/Livewire chat
  ├─ Listing and editorial pages
  └─ Filament admin
        │
Laravel application
  ├─ Chat orchestration and deterministic tools
  ├─ Property/project/rental domain models
  ├─ Lead and workflow services
  ├─ Knowledge ingestion and hybrid search
  └─ Media library
        │
MySQL + public media disk + external AI/email/CRM services
```

## Main modules

| Module | Primary classes |
| --- | --- |
| Chat UI | `App\Livewire\ChatWindow` |
| AI orchestration | `App\Services\RagPipelineService` |
| LLM abstraction | `App\Services\Llm\LlmManager` and providers |
| Property search | `App\Services\PropertySearchService` |
| Retrieval | `KnowledgeIngestService`, `HybridSearchService`, `MySqlVectorSearch` |
| Deterministic tools | `Services\Tools\*` |
| Personalization | `AdaptiveRecommendationService`, conversation memory |
| Leads/workflows | `LeadCaptureService`, `WorkflowAutomationService` |
| Integrations | `Services\Mcp\*`, SMTP, CRM, Document AI |
| Inventory quality | `InventoryQualityService`, AI Analytics |
| Media | Spatie Media Library, `RecoverPropertyMedia` |

## Data model

Real-estate entities are `Property`, `Project`, `Agent`, `Lead`, and translation/media relations. AI migrations add conversations, messages, knowledge sources/chunks, recommendation rules, sponsored campaigns, analytics, viewing requests, attachments, and lead reset tokens.

Domain content uses translation tables; interface strings use JSON dictionaries. Supported locales are `en`, `tr`, `fa`, `ar`, `ru`, and `de`.

## Routes

- `/` — AI chat.
- `/listings` — listing/marketing home.
- `/properties`, `/projects`, `/resale` — sales inventory.
- `/daily-rentals`, `/long-term-rentals` — rentals.
- `/areas/{area}` — SEO area pages.
- `/blog`, `/agents`, and static pages.
- `/admin` — Filament.
- `/healthz`, `/sitemap.xml` — operations and SEO.

## Runtime

- Shared hosting uses synchronous queues and file cache/sessions.
- VPS deployments can use Redis and queue workers.
- Media conversions are synchronous and non-optimized for hosts without `proc_open`.
- LLM calls are isolated behind provider interfaces and timeouts.
- Deterministic fallbacks remain available when an LLM is unavailable.

## Extension points

- Implement `LlmProviderInterface` for another AI provider.
- Implement `VectorSearchInterface` for another vector store.
- Implement `McpConnectorInterface` for another workflow integration.
- Add tools in `App\Services\Tools` and route them from `RagPipelineService::buildWidget()`.
