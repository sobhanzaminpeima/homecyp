# HomeCyp documentation

This index links the product, engineering, security, release, and operations references that ship with HomeCyp.

| Document | Primary audience | Covers |
| --- | --- | --- |
| [Project README](../README.md) | Everyone | Product scope, stack, setup, AI configuration, commands, and production links |
| [Architecture](ARCHITECTURE.md) | Engineers | Application boundaries, request flow, search, RAG, providers, data, and integrations |
| [AI chat](AI_CHAT.md) | Product and engineering | Conversation flow, grounding, multilingual behavior, tools, attachments, and guardrails |
| [Operations](OPERATIONS.md) | Operators | Health checks, queues, imports, media recovery, caches, logs, and incident triage |
| [Deployment](../DEPLOYMENT.md) | Release operators | Build, shared-host layout, environment setup, migration, smoke checks, and rollback |
| [Security](../SECURITY.md) | Engineers and operators | Secret handling, production hardening, uploads, privacy, and vulnerability reporting |
| [Contributing](../CONTRIBUTING.md) | Contributors | Branch, code, test, documentation, and pull-request expectations |
| [Changelog](../CHANGELOG.md) | Product and release teams | Shipped capabilities and release history |

## Documentation rules

- Update the relevant document in the same commit as a behavior, route, environment, or operational change.
- Use placeholders in examples; never include live credentials, tokens, private customer data, or database exports.
- Treat the source code and migrations as authoritative when documentation and implementation disagree.
- Verify internal links and commands before release.
