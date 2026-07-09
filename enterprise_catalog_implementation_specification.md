# Enterprise Catalog Implementation Specification (ECIS) — fstio

This document defines the final implementation specification and architectural blueprint for the **fstio Enterprise Catalog Module** integrated with Bagisto v2.4.8. All design elements are fully mapped to clean Domain-Driven Design (DDD) aggregates, CQRS patterns, and upgrade-safe extension hooks.

---

## Part 1: Bounded Context Catalog Architectural Specification

### 1. Business Capabilities
- **PC Builder & Compatibility**: Allows customers to build custom desktops checking motherboard-processor compatibility, RAM layout limits, power headroom, and physical case dimensions.
- **Warranty & Document Management**: Tracks serial-based warranty metadata, system drivers, and product spec sheets.
- **Brand & Series Taxonomy**: Groups products under brands and dynamic series mappings for navigation and filtering.

### 2. Architectural Design Specifications

#### Aggregate Root: Product Extension Aggregate
- **Entity Lifecycle**: Subclassed Product Model acts as the aggregate root, managing transactional boundaries for attributes, inventory serials, brand relationships, and hardware metrics.
- **State Machine**: Catalog objects track lifecycle states: `Draft` -> `Active` -> `Archived` (managed via Laravel database status fields).
- **Concurrency Strategy**: Optimistic concurrency control via database transaction-level locks (`DB::transaction` with `lockForUpdate`).
- **Idempotency Strategy**: Command validation enforces unique slug and serial constraints at the database index layer.

```
+------------------------------------------------------------+
|                Product Extension Aggregate                 |
+------------------------------------------------------------+
| - CustomProduct (Aggregate Root)                           |
|   ├── brand_id (Foreign Key)                               |
|   ├── brand (BelongsTo Relationship)                       |
|   ├── product_serials (HasMany Serials Entity)             |
|   └── compatibility_rules (HasMany Compatibility Entity)   |
+------------------------------------------------------------+
```

---

## Part 2: Implementation Catalogs

### 3. Aggregate & Entity Catalog
- **CustomProduct**  
  - *Location*: `app/Domain/Models/Product.php`
  - *Attributes*: `id`, `type`, `sku`, `brand_id`.
  - *Relationships*: `brand()` (BelongsTo), `productSerials()` (HasMany), `compatibilityRules()` (HasMany).
- **Brand**  
  - *Location*: `app/Domain/Models/Brand.php`
  - *Attributes*: `id`, `slug`, `logo_light`, `logo_dark`, `website_url`.
- **ProductSerial**  
  - *Location*: `app/Domain/Models/ProductSerial.php`
  - *Attributes*: `id`, `product_id`, `serial_number`, `status` (SerialStatus Enum).

### 4. Command & Query Catalog
- **Commands**:
  - `AssignBrandToProductDto` -> Maps product to brand.
  - `CreateProductSerialDto` -> Adds stock serial number.
  - `RegisterCompatibilityRuleDto` -> Saves compatibility rule.
- **Queries**:
  - `GetProductWithBrandQuery` -> Resolves a product and eager loads its brand relation.
  - `GetBrandBySlugQuery` -> Resolves a brand from its slug.

### 5. Repository Catalog
- **BrandRepositoryInterface**  
  - *Methods*: `find($id)`, `findBySlug($slug)`, `create(array $data)`, `update(array $data, $id)`, `delete($id)`.
  - *Concrete*: `App\Domain\Repositories\Eloquent\BrandRepository`.
- **ProductSerialRepositoryInterface**  
  - *Methods*: `findBySerial($serialNumber)`, `create(array $data)`.
  - *Concrete*: `App\Domain\Repositories\Eloquent\ProductSerialRepository`.

### 6. Domain & Application Service Catalog
- **CompatibilitySpecificationService**  
  - *Method*: `isCompatible(Product $p1, Product $p2): bool`
  - *Logic*: Asserts socket compatibility, memory channel allocation limits, and PCIe card dimension specifications.
- **SimpleCommandBus** & **SimpleQueryBus**  
  - *Logic*: Simple, container-based dispatcher mapping commands/queries to their respective handlers.

---

## Part 3: System Interactions & Infrastructure

### 7. Event & Queue Catalog
- **Event Publishing**: Handlers dispatch standard domain events (e.g. `BrandAssignedToProduct`) to notify indexers.
- **Queue Interaction**: Background tasks process ElasticSearch document indexing via queued jobs.

### 8. Cache & Indexing Catalog
- **Cache Strategy**: Eager loading relationships (`ProductProxy::with('brand')`) bypasses EAV cache database lookups.
- **Cache Invalidation**: Hooked into native events (`catalog.product.update.after`) to trigger index invalidation.

### 9. Media & Image Processing Catalog
- **Media Service**: Custom helper resolves image variants and dark/light logo assets using standard storage directories.

### 10. Performance & Security Catalog
- **Performance Budget**: Query executions must resolve within 50ms using optimized database indexes on foreign key columns.
- **Authorization**: SimpleAuthorizer checks command abilities (e.g. `assign_brand`) before executing handler logic.

---

## Part 4: Testing & Verification Blueprint

### 11. Testing Catalog
- **Unit Tests**: Asserts Value Object invariants (e.g., `SerialNumber` patterns) and command bus dispatch mapping.
- **Integration Tests**: Executes transactional commands against test SQLite/MySQL database.
- **Feature Tests**: Verifies complete use case execution under different mock authorization scenarios.

---

## Part 5: Red Team Architecture Review

### 1. Abstract Checking
- *Abstract Check*: Verified that the Application layer does not depend on concrete repository implementations; all repositories utilize interfaces.
- *Solid Compliance*: All handlers satisfy the Single Responsibility Principle (SRP) and Open-Closed Principle (OCP).

### 2. Concord Conflicts & Upgrade Safety
- *Bagisto Core Integrity*: No native codebase files are modified. All customizations reside in `app/` and `tests/` directories.
- *EAV Interception Conflict*: Bypassed EAV getter by subclassing the model class and registering it in Concord.

### Status: GO
*Zero critical architectural violations identified. Ready for next integration phases.*
