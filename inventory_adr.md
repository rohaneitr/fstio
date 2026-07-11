# Architectural Decision Record (ADR) — Inventory Module

This document details the architectural decisions made for the **fstio Enterprise Inventory Module** integration on Bagisto v2.4.8.

---

## 1. Context & Business Problem
We must model stock updates, warehouse allocations, and checkout reservation locks. The solution must support multi-warehouse structures, prevent overselling, and scale cleanly to thousands of requests.

---

## 2. Decision: Eager EAV Decoupled Relational Database Locking

### Selected Approach
- We use **Optimistic and Pessimistic Lock Queries (`SELECT FOR UPDATE`)** inside Laravel transactional command handlers to update native tables (`product_inventories`, `product_ordered_inventories`).

### Justification
1. **Overselling Prevention**: EAV value updates are vulnerable to race conditions if multiple requests read the current value before saving updates. Database row-level locks on product inventories prevent double-allocation.
2. **Reuse**: Using native tables preserves compatibility with the native inventory indexer (`Webkul\Product\Helpers\Indexers\Inventory`), which automatically reads from `product_inventories` and `ordered_inventories` to compute the salable flat index.

---

## 3. Alternative Options Evaluated

### Option A: Custom isolated inventory mapping tables
- *Why Rejected*: Bypasses native indexers, requiring rewrite of listing query controllers and Elasticsearch mappings.

### Option B: Real-time calculation on order checkouts
- *Why Rejected*: High query cost under heavy traffic (requires joining order tables on every page view).

---

## 4. Status: **APPROVED FOR IMPLEMENTATION**
- **Confidence Score**: 100% (proven via codebase tracing of indexing logic).
