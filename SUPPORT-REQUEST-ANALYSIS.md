# Support Request: ECM/ablocks feature requests (A-level teacher / affiliate site use case)

Source: support message, 2026-09-29. User runs an affiliate/recommendation site (recommended texts for A-level students) and separately runs Academy LMS + School Management for a prospective online academy.

## What they actually want (ECM-relevant)

**Note on "maps to Track X" below:** these tracks are NOT in this repo's code — they exist only in a prior planning artifact, **ECM-PLAN-001** (https://claude.ai/code/artifact/37990868-a909-41e5-900b-e1eb86c00fb6), dated 2026-09-07, explicitly marked *"not yet implemented — the plan for review."* Nothing has been built against it. Treat these rows as "this ask already has a design sketch somewhere" not "this is in progress."

| # | Ask | Maps to |
|---|-----|---------|
| 1 | No-code front-end template builder for a CPT (layout, buttons, styled fields) | ECM-PLAN-001 Track A — ablocks integration (planned on paper, not built) |
| 2 | Bidirectional relationship fields (Author ↔ Texts auto-links both ways) | ECM-PLAN-001 Track C — "reverse/computed relations" row (planned on paper, not built; bidirectionality not explicitly spelled out — needs confirming) |
| 3 | Create a draft related post inline from a relationship field (e.g. add "Author" without leaving the Text editor) | **New** — not in ECM-PLAN-001 at all |
| 4 | Custom filter/facet builder on archive pages (filter by exam board, theme, etc., no-code) | **New** — closest existing item is Track A3 (Loop Filter by ECM custom fields, taxonomy-only today per the plan's own note), but that's ablocks-side filtering, not a customer-facing archive facet UI — not explicitly scoped |
| 5 | Relationship fields that can target CPTs registered by other plugins (not just ECM's own) | **New** — ECM-PLAN-001's relation-index row doesn't mention cross-plugin targets |
| 6 | Semantic search across post types + a synonym-mapping/navigation layer | **New** — not in ECM-PLAN-001; largest, most speculative ask |

## Not out of scope, but different product: Digital Campus + Academy LMS School Management

Not this repo's codebase, but tracked here per instruction. `academy-digital-campus` plugin exists in the tree at **v0.0.1** ("Academy Digital Campus" — early-stage, barely started: "Share your knowledge by launching an online course"). Decision: approach this customer about Digital Campus as the vehicle for their online-only-academy use case, and treat their asks below as candidate requirements for Digital Campus + the Academy LMS School Management add-on.

- [ ] Online-only school support in Digital Campus (customer is negotiating with their local authority to run an online academy through it — no physical campus)
- [ ] Local-authority compliance reporting:
  - [ ] Parents can view their child's sanctions/community rule violations
  - [ ] Parents and students can export a per-term progress report (term dates come from the add-on's term settings): time spent per lesson, in-progress goals, most recent assignment grades
- [ ] KSBs/competencies model:
  - [ ] Departments/Subjects can define KSBs/competencies they assess
  - [ ] Courses map to which competencies they fulfill
  - [ ] Individual quizzes/assignments map to which specific competencies they test (finer grain than the course level)

## Out of scope entirely

- **Video call request** — sales/relationship action, not a dev task.
- **Competitive positioning vs BuddyBoss/FluentCommunity** — product strategy input, not a ticket.

## Proposed todo (ECM only)

- [ ] #2 — confirm whether ECM-PLAN-001's relation-index item covers bidirectional linking, or just reverse lookup; decide and scope
- [ ] #3 — inline draft-creation from a relationship field: new scope, not in ECM-PLAN-001
- [ ] #4 — archive-page facet/filter builder: new scope; decide if it extends ECM-PLAN-001's A3 (Loop Filter) or is a separate ECM-side feature
- [ ] #5 — cross-plugin relation targets: new scope, extend ECM-PLAN-001's relation-index item
- [ ] #6 — semantic search + synonym mapping: separate, larger R&D item, not in ECM-PLAN-001
- [ ] Internal triage only — no customer reply drafted per instruction

## Detailed implementation plan (ECM's six asks)

Grounded in the actual code, not the ECM-PLAN-001 artifact — verified directly:
- `includes/features/fields/relationship-field.php` exists (PHP field class, REST schema) but is **dead code today**: its picker entry is commented out at `dev_contentManager/utils/customField.js:134-140`, and it has no General/Validation/Render React components. Only "Post Object" (single/multi post-picker) is live.
- Post Object's post-type source (`fetchCollections` → `get_all_custom_post_types`, `includes/ajax/post-types.php:310-314`) is hard-scoped to `Helper::ECM_POST_TYPE_NAME` — ECM's own CPT-definition posts only. It cannot target another plugin's post type today.
- `ablocks/src/blocks/dynamic-text/settings.js:60-81` confirms ECM-PLAN-001's A1: field source is core fields + taxonomy names + a raw meta-key fallback, no ECM field-group awareness.
- `ablocks/src/blocks/loop-filter/settings.js` confirms A3: taxonomy-only filter UI. **Correction to ECM-PLAN-001:** it claims the underlying PHP "already supports `meta_query`" — grepped the entire `ablocks` plugin (PHP + JS), zero matches for `meta_query`. That plumbing does not exist; it needs to be built, not just exposed.
- No search/embedding/vector infrastructure exists anywhere in ECM.

**Suggested build order:** #2 → #5 → #3 → #1 (parallel-safe) → #4 → #6 (separate track, biggest unknown).

---

### #1 — No-code CPT template builder (via ablocks integration)

- **a.** Expose an ECM post type's field-group schema (label, meta_key, type) as a read-only endpoint — `includes/ajax/post-type-fields.php` already reads this data; confirm it's callable from ablocks' context or add a thin public accessor.
- **b.** In `ablocks/src/blocks/dynamic-text/settings.js`, add an "ECM Fields" options group populated from (a) when the block's post type is an ECM CPT — same mechanism as the existing taxonomy group, just a new source.
- **c.** In ablocks' Dynamic Text PHP render, resolve `ecm:<meta_key>` through ECM's field-type registry (not raw `get_post_meta`) so typed fields (Link, Image, Relationship) render correctly instead of raw serialized values.
- **d.** For the "affiliate link as a button" requirement specifically: once the Link field is exposed via (b), either style Dynamic Text's output as a button, or add a small "Dynamic Button" ablocks block variant that renders a Link-field value as `<a class="button">`.
- **e.** No new builder UI needed inside ECM — the customer builds the single-CPT layout visually in the Site Editor/ablocks canvas using these dynamic blocks plus ablocks' existing Loop Builder for the archive.
- **Files:** `ablocks/src/blocks/dynamic-text/settings.js`, `ablocks/includes/blocks/dynamic-text/*.php`, `includes/ajax/post-type-fields.php`.
- **Depends on:** nothing else here.

### #2 — Bidirectional relationships

- **a.** Uncomment the picker entry (`customField.js:134-140`) and build the missing components, mirroring `PostObjectFieldGeneral`'s shape: `dev_contentManager/components/CustomFields/General/RelationshipFieldGeneral/`, `.../Validation/RelationshipFieldValidation/`, `dev_contentManager/components/RenderField/RenderRelationshipField/`.
- **b.** Add a "Bidirectional" toggle + "Target field" picker to the General settings: when field A on post type X points at post type Y, the admin chooses which field on Y mirrors it back.
- **c.** New relation-index table, e.g. `{$prefix}ecm_relations (id, source_post_id, source_field_key, target_post_id, target_field_key)`, synced on save.
- **d.** On saving field A's value: write the index row **and** push the reciprocal post ID into field B's stored meta on the target post — a real two-way write, not a computed reverse-lookup, since the ask is "the text name appears as an option under the author's Texts field," i.e. visible and editable on both sides.
- **e.** Removing a relation on either side must delete the index row and strip the mirrored value from the other side's meta.
- **Depends on:** nothing else here, but #3 and #5 build on it.

### #3 — Inline draft-post creation from a relationship field

- **a.** In the Relationship field's render component (from #2), add a "+ Create new" option in the post-search dropdown when a search returns no match.
- **b.** Clicking it opens a lightweight modal (reuse the existing `Modal` component) with just the target post type's required fields (title, minimum), creating via the existing post-item-creation ajax path with `post_status: draft` forced.
- **c.** On success, attach the new draft's ID to the relationship field's value immediately (and sync the reverse field too, if bidirectional) — no leaving the current screen, no manual re-linking later.
- **Depends on:** #2 (needs its render component to exist first).

### #4 — No-code archive facet/filter builder

- **a. ablocks side (extends/corrects A3):** give Loop Filter a real meta-filter mode — a field-type-aware operator + input (reuses #1's field-source picker), and actually build `meta_query` construction into Loop Builder's PHP query assembly, since it does not exist today (see correction above).
- **b. Front-end UX:** the ask is students filtering a public archive page by exam board then theme — needs to be verified whether Loop Filter's current taxonomy mode already re-queries live on the front end (AJAX/REST) or is editor-config-only; if the latter, that live-refresh behavior is new work, not just adding a meta mode.
- **Depends on:** #1 (item a, the field-source picker).

### #5 — Cross-plugin relation targets

- **a.** New/extended endpoint listing all public, non-ECM post types (`get_post_types(['public' => true])`, excluding ECM's internal definition post type) — check what `get_existing_post_type` (`includes/ajax/post-types.php:74`, already takes a `built_in` flag) currently returns before assuming a wholly new endpoint is needed.
- **b.** Relationship field's post-type picker (#2) merges ECM's own CPT list with this external list.
- **c.** The relation-index table from #2 stores plain `post_id`s, agnostic of which plugin registered the post type — so once the field can target external post types, indexing and bidirectional sync work unmodified. Cheapest of the "new" items once #2 exists.
- **Depends on:** #2.

### #6 — Semantic search + synonym mapping

Full plan, per request — but this is genuinely new infrastructure top to bottom with an ongoing external-API cost, not an extension of anything found in the code.

1. **Embedding provider abstraction:** `EmbeddingProviderInterface::embed(string $text): array<float>`. First implementation calls an external embeddings API (e.g. OpenAI `text-embedding-3-small`) — self-hosting a model in PHP isn't practical. Gated behind a customer-supplied API key in Settings.
2. **Indexing:** on `save_post` for enabled post types, queue a background job (Action Scheduler — confirm whether it's already a dependency here or needs adding) that embeds title + content + selected custom fields, storing the vector in a new table `{$prefix}ecm_search_vectors (post_id, vector BLOB, updated_at)`.
3. **Query-time matching:** brute-force cosine similarity in PHP over a candidate set pre-filtered by `WP_Query` (post_type/status) to keep it small. This scales to roughly tens of thousands of rows, not unboundedly — state that ceiling explicitly. A real vector-DB backend (pgvector/Pinecone/Qdrant) can be added later as a second provider behind the same abstraction if a site outgrows it.
4. **Synonym mapping:** an admin-managed table (`term => [synonyms]`), used to (a) expand a search query before matching, and (b) power the "manage our own internal links/navigation" ask — pin a synonym group to a canonical post and auto-link recognized terms in rendered content.
5. **Gating:** naturally an SE-Premium feature given the per-embed API cost, consistent with how Settings Builder/Theme Options are already gated.
- **Open product question, not just engineering:** who pays for the embedding API calls — Kodezen's own key, or does each customer supply their own?
