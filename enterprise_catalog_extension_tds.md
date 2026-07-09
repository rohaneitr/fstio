# Enterprise Catalog Extension — Technical Design Specification (TDS)

This specification defines the extension architecture for the **fstio** Enterprise Catalog Module on Bagisto v2.4.8. It details the findings from the zero-trust forensic engineering audit of the native catalog bounded context and sets the blueprint for all future Catalog integrations (e.g. ERP, GraphQL, Elasticsearch, Multi-tenancy, and AI Search).

---

## Part 1: Native Catalog Bounded Context Forensic Audit

### 1. Executive Summary
Bagisto v2.4.8 implements a hybrid catalog storage architecture combining **EAV (Entity-Attribute-Value)** for flexible attributes, a compiled **Flat Index** (`product_flat`) table for optimized reads, and **Asynchronous Queue Jobs** for inventory, pricing, and Elasticsearch synchronization.

### 2. Component Inventory
The native catalog resides in `packages/Webkul/Product/src/` and interacts with `packages/Webkul/Attribute/src/` and `packages/Webkul/Inventory/src/`. Key components:
- **Product Model Proxy**: `Webkul\Product\Models\ProductProxy`
- **Product Repository**: `Webkul\Product\Repositories\ProductRepository`
- **Product Types**: Simple, Configurable, Virtual, Bundle, Grouped, Downloadable.
- **Indexers**: Price, Inventory, Flat, Elasticsearch.

---

## Part 2: Bounded Context Component Catalogs

### 3. Repository Catalog
- **ProductRepository**  
  - *Location*: `packages/Webkul/Product/src/Repositories/ProductRepository.php`
  - *Purpose*: CRUD database access for product models.
  - *API*: `create()`, `update()`, `checkInLoadedFamilyAttributes()`, `makeModel()`.
- **ProductAttributeValueRepository**  
  - *Location*: `packages/Webkul/Product/src/Repositories/ProductAttributeValueRepository.php`
  - *Purpose*: Persistence layer for EAV attribute values.

### 4. Model Catalog
- **Product Model**  
  - *Location*: `packages/Webkul/Product/src/Models/Product.php`
  - *Extends*: `Illuminate\Database\Eloquent\Model`
  - *Implements*: `Webkul\Product\Contracts\Product`
  - *EAV Interceptor*: Overrides `getAttribute($key)` to load dynamic attributes. Bypassed for relations using physical method declarations.

### 5. Concord Catalog
- **Product Mapping**  
  - *Contract*: `Webkul\Product\Contracts\Product`
  - *Concrete*: Registered as `Webkul\Product\Models\Product` inside `Webkul\Product\Providers\ModuleServiceProvider`. Overridden to `App\Domain\Models\Product` inside `FoundationServiceProvider`.

### 6. Event Catalog
- `catalog.product.create.after` — Fired inside `ProductRepository@create` after transactional DB insertion.
- `catalog.product.update.after` — Fired inside `ProductRepository@update` after updates are saved.
- `catalog.product.delete.before` — Fired inside `ProductRepository@delete` before DB records are deleted.

### 7. Listener Catalog
- **Webkul\Product\Listeners\Product**  
  - *Consumes*: `catalog.product.create.after`, `catalog.product.update.after`, `catalog.product.delete.before`.
  - *Action*: Triggers `$this->flatIndexer->refresh($product)` and queues indexer update jobs.

### 8. Observer Catalog
- **ProductObserver**  
  - *Location*: `packages/Webkul/Product/src/Observers/ProductObserver.php`
  - *Events*: `deleted`
  - *Action*: Deletes the product media directory (`product/{id}`) from storage.

### 9. Queue Job Catalog
- **UpdateCreateInventoryIndex** — Re-builds stock levels for related products.
- **UpdateCreatePriceIndex** — Re-builds pricing cache table.
- **ElasticSearch\UpdateCreateIndex** — Pushes updated documents to Elasticsearch.

### 10. Command Catalog
- **Indexer Command**  
  - *Signature*: `indexer:index {--type=*} {--mode=*}`
  - *Supports*: `inventory`, `price`, `flat`, `elastic` indexes.
  - *Execution*: Daily Cron scheduled in `ProductServiceProvider` for price indexes at `00:01`.

### 11. Migration Catalog
- `2018_07_27_065727_create_products_table.php` — Core product attributes.
- `2018_07_27_070011_create_product_attribute_values_table.php` — EAV attributes.
- `2018_12_06_185202_create_product_flat_table.php` — Read cache.

### 12. Config Catalog
- `product_types` (`packages/Webkul/Product/src/Config/product_types.php`) — Defines registered product types.
- `elasticsearch` (`config/elasticsearch.php`) — Connection settings.

### 13. Route Catalog
- Registered inside `packages/Webkul/Shop/src/Routes/shop-routes.php` and `packages/Webkul/Admin/src/Routes/admin-routes.php`.

### 14. Policy & Middleware Catalog
- Handled through Laravel's standard auth gate and ACL configuration in `packages/Webkul/Admin/src/Config/acl.php`.

---

## Part 3: Bounded Context Architectural Matrices

### 15. Native vs Custom Matrix
| Capability | Native Bagisto v2.4.8 | Custom fstio Extension |
|---|---|---|
| **Product Model** | `Webkul\Product\Models\Product` | `App\Domain\Models\Product` (Subclass via Concord) |
| **Brand Assignment** | EAV Attribute (Flat String) | Relational SQL Foreign Key (`brand_id` column) |
| **Buses** | Event-driven listeners only | SimpleCommandBus & SimpleQueryBus |

### 16. Reuse & Extension Matrix
| Native Component | Reuse Strategy | Extension Mechanism |
|---|---|---|
| **ProductRepository** | Direct Injection | Bypassed for custom queries; utilized for base CRUD. |
| **Flat Indexer** | Retained for product flat updates | Hooked via native events. |
| **Price Indexer** | Daily cron/queue jobs | Custom price logic wraps standard pricing indexes. |

### 17. Risk & Technical Debt Matrix
- **EAV Interception**: High risk. Overriding `getAttribute` dynamically can break relationship loads if methods do not exist physically. Resolved by subclassing.
- **Vite/Assets Build**: Admin assets are compiled to `public/themes/`. Avoid editing assets inside `packages/` directly without rebuilding.

---

## Part 4: Enterprise Catalog Extension Design

This section defines the architecture for the catalog extensions. No implementation is included.

```
├── app/
│   ├── Application/
│   │   ├── Bus/                     # Command & Query Buses
│   │   ├── DTO/                     # PC Builder, Warranty, Download Specs
│   │   ├── Handlers/                # CQRS Command Handlers
│   │   └── Queries/                 # CQRS Query Handlers
│   ├── Domain/
│   │   ├── Models/
│   │   │   ├── Product.php          # Subclass extending Webkul Product
│   │   │   ├── Brand.php            # Brand relation
│   │   │   └── CompatibilityRule.php # Compatibility model
│   │   ├── Repositories/            # Domain Repositories interfaces
│   │   └── Services/                # Compatibility Engine, ERP Sync Service
│   └── Infrastructure/
│       ├── Services/
│       │       └── ElasticsearchService.php # Custom OpenSearch/ElasticSearch
```

### 18. Application Layer
- **CommandBus** & **QueryBus**: Resolve handers dynamically using preg-replaced string templates.
- **DTOs**:
  - `UpdateCompatibilityDto`
  - `RegisterProductSerialDto`
  - `SyncErpCatalogDto`
- **Handlers**:
  - `UpdateCompatibilityHandler` — Wrapped in `DB::transaction()`.
  - `SyncErpCatalogHandler` — Handles bulk UPSERT and dispatches indexing jobs.

### 19. Domain Layer
- **Value Objects**:
  - `SerialNumber` (alphanumeric validation rules).
  - `WarrantyPeriod` (validates duration, unit, and extension terms).
- **Domain Services**:
  - `CompatibilitySpecificationService` — Inspects compatibility metadata (socket type, form factor, power limits) to return compatibility matches.
  - `PricingResolutionService` — Resolves customer-group-specific pricing rules.

### 20. Infrastructure Layer
- **Elasticsearch/OpenSearch**: Custom indexing client that handles raw document mapping updates.
- **ERP Integration**: Synchronizer job class pulling catalog updates from external systems.

### 21. Future Compatibility Specs
- **GraphQL & REST**: Mapped via custom routing controllers resolving CQRS queries.
- **Multi-Tenant**: Scope database connections per tenant code resolving through tenant-specific sub-repositories.
- **AI Search**: Expose semantic search API through vector-based index mappings in OpenSearch.
