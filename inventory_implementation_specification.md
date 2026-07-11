# Enterprise Inventory Implementation Specification (ECIS) — fstio

This document compiles the **Architecture Freeze Review**, **Concurrency Analysis**, and the definitive **Implementation Specification (ECIS)** for the fstio Enterprise Inventory Module on top of Bagisto v2.4.8.

---

## Part 1: Bounded Context Architecture Freeze Review

### 1. Architectural Boundaries
- **Aggregate Boundaries**: The `InventorySource` aggregate is independent. The `ProductInventory` entity acts as a boundary-spanning link owned by the Product Aggregate Root.
- **Dependency Direction**: Strictly inward. The Application layer references Domain repository interfaces, which are implemented by Eloquent concretes in the persistence layer. No circular dependencies exist.
- **Concord Compatibility**: Subclasses can override models cleanly using Concord proxies (`InventorySourceProxy`), ensuring no core package modification.

---

## Part 2: Concurrency & Indexing Lifecycle

### 2. Concurrency Safety
- **Locking Strategy**: The adjust inventory handlers apply explicit row-level locks:
  ```php
  $inventory = DB::table('product_inventories')
      ->where('product_id', $productId)
      ->where('inventory_source_id', $sourceId)
      ->lockForUpdate()
      ->first();
  ```
  This isolates reads and blocks write races, eliminating double-allocation and overselling risks.
- **Retry Mechanism**: In case of transient deadlock exceptions, application controllers apply a 3-pass retry loop around the command bus dispatch.

### 3. Indexing Lifecycle Triggers
- **Trigger Points**:
  - `catalog.product.update.after` -> Dispatches `UpdateCreateInventoryIndex` (updates salable quantities index).
  - `checkout.order.save.after` -> Triggers `Order@afterCancelOrCreate` listener (reserves stock counts in `ordered_inventories` table).
  - `sales.order.cancel.after` -> Triggers `Order@afterCancelOrCreate` (decrements `ordered_inventories` counts).

---

## Part 3: Enterprise Inventory Implementation Specification (ECIS)

```
app/
├── Application/
│   ├── DTO/
│   │   ├── AdjustInventoryQtyDto.php
│   │   └── AllocateInventoryDto.php
│   └── Handlers/
│       ├── AdjustInventoryQtyHandler.php
│       └── AllocateInventoryHandler.php
└── Http/
    ├── Controllers/
    │   └── Admin/
    │       └── InventoryController.php
    └── Requests/
        └── InventoryAdjustmentRequest.php
```

### 4. DTO & Command Catalog
- **AdjustInventoryQtyDto**  
  - *Attributes*: `productId` (int), `inventorySourceId` (int), `qty` (int).
- **AdjustInventoryQtyHandler**  
  - *Action*: Loads inventory row with `lockForUpdate()`, validates stock level constraints, updates the quantity, and dispatches the inventory index job chain.

### 5. Concurrency Matrix
| Use Case | Locking Strategy | Deadlock Risk | Mitigation |
|---|---|---|---|
| **Inventory Adjustment** | Pessimistic `lockForUpdate()` | Low | Wrap in sequential database transaction block |
| **Order Checkout Reservation**| Row lock on `ordered_inventories` | Low | Pre-sort item IDs before applying locks |

### 6. Testing Blueprint
- **Unit Tests**: Asserts quantity boundary values and `StockQty` Value Object validation rules.
- **Integration Tests**: Tests concurrent HTTP updates using mock multi-thread requests to verify lock behaviors.

---

## Part 4: Upgrade Safety & Quality Audit

- **Upgrade Safety**: Standalone application layers ensure zero modifications inside `vendor/` or `packages/Webkul/`.
- **Performance Budget**: Write executions must commit within 30ms using indexed columns.

### Verification Verdict

🏆 **GO FOR IMPLEMENTATION**
