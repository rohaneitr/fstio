# Enterprise Inventory Module — Forensic Audit & Architectural Blueprint

This document contains the completed **Forensic Audit**, **Gap Analysis**, and **Technical Design Specification (TDS)** for the fstio Enterprise Inventory Module on top of Bagisto v2.4.8. 

We stop after this specification is written to await consistency verification and approval before any implementation begins.

---

## Part 1: Bounded Context Forensic Audit

### 1. Model & Entity Configurations
- **InventorySource Entity**  
  - *Location*: `packages/Webkul/Inventory/src/Models/InventorySource.php`
  - *Fields*: `code`, `name`, `description`, `contact_name`, `contact_email`, `contact_number`, `status` (boolean), `street`, `country`, `state`, `city`, `postcode`.
  - *Mappers*: Registered via Concord model mapping proxy class `InventorySourceProxy`.
- **ProductInventory Entity**  
  - *Location*: `packages/Webkul/Product/src/Models/ProductInventory.php`
  - *Fields*: `qty` (int), `product_id` (foreign key), `inventory_source_id` (foreign key), `vendor_id` (nullable).
  - *Lifecycle*: Managed by product repository events.
- **ProductOrderedInventory Entity**  
  - *Location*: `packages/Webkul/Product/src/Models/ProductOrderedInventory.php`
  - *Fields*: `qty` (reserved count), `product_id`, `channel_id`.
  - *Purpose*: Tracks reserved stock counts for active checkout orders before shipment.
- **ProductSalableInventory Entity**  
  - *Location*: `packages/Webkul/Product/src/Models/ProductSalableInventory.php`
  - *Fields*: `qty`, `sold_qty`, `product_id`, `channel_id`.

### 2. Inventory Indexing Flow
- **Inventory Indexer Helper**  
  - *Location*: `packages/Webkul/Product/src/Helpers/Indexers/Inventory.php`
  - *Operations*: Calculates net stock quantity:
    $$\text{Salable Qty} = \sum (\text{Active Warehouse Inventory Qty}) - \text{Ordered Reserved Qty}$$
    Saves index to the `product_inventory_indices` table.
- **Queue/Async Indexing Jobs**  
  - *Job*: `Webkul\Product\Jobs\UpdateCreateInventoryIndex`
  - *Trigger*: Executed asynchronously on product updates or sales events.

### 3. Events & Listeners
- `catalog.product.update.after` -> Dispatches `UpdateCreateInventoryIndex` job chain.
- `checkout.order.save.after` -> Triggers `Order@afterCancelOrCreate` listener to increment `ordered_inventories` reserved quantity.
- `sales.order.cancel.after` -> Triggers `Order@afterCancelOrCreate` to decrement `ordered_inventories` quantity.
- `sales.refund.save.after` -> Triggers `Refund@afterCreate` listener to adjust stock counts.

---

## Part 2: Gap Analysis & Reuse Matrix

| Requirement | Native Bagisto Status | Custom fstio Extension Blueprint | Duplicate Risk |
|---|---|---|---|
| **Inventory Source** | Fully Supported | Direct reuse of `InventorySourceProxy` | Zero (direct reuse) |
| **Product Stock Index** | Fully Supported | Direct reuse of `ProductInventoryIndex` | Zero |
| **Reserved Stock (Checkout)**| Supported via `ordered_inventories` | Extended to support multi-warehouse reservations | Zero |
| **Backorder Management** | Basic settings | Custom backoffice control interface | None |
| **Stock Location Matrix** | *NOT VERIFIED* | Mapping rules for warehouse routes | None |

---

## Part 3: Enterprise Technical Design Specification (TDS)

```
app/
├── Application/
│   ├── DTO/
│   │   ├── AllocateInventoryDto.php
│   │   └── AdjustInventoryQtyDto.php
│   └── Handlers/
│       ├── AllocateInventoryHandler.php
│       └── AdjustInventoryQtyHandler.php
├── Domain/
│   ├── Services/
│   │   ├── InventoryRoutingService.php      # Allocates order items to nearest warehouse
│   │   └── SalableInventoryEngine.php       # Verifies salable headroom
│   └── ValueObjects/
│       └── StockQty.php                     # Enforces positive stock value invariants
└── Http/
    ├── Controllers/
    │   └── Admin/
    │       └── InventoryController.php
    └── Requests/
        └── InventoryAdjustmentRequest.php
```

### 1. Domain Entities & Value Objects
- **StockQty Value Object**: Wraps quantity integers, throwing domain exceptions for negative values unless backorders are explicitly active for the product sku.
- **InventoryRoutingService**: Inspects customer shipping postcode (district, division, upazila) and routes order fulfillment to the nearest active warehouse source with salable stock.

### 2. CQRS Use Cases
- **AdjustInventoryQtyCommand** (`AdjustInventoryQtyDto` -> `AdjustInventoryQtyHandler`)  
  - *Action*: Updates `product_inventories` quantities inside `DB::transaction()` wrapped with optimistic lock checking (`lockForUpdate`). Triggers indexing updates post-commit.
- **AllocateInventoryCommand** (`AllocateInventoryDto` -> `AllocateInventoryHandler`)  
  - *Action*: Deducts quantities from primary warehouse and reserves stock on order creation.

### 3. Caching & Invalidation
- **Repository Caching**: Cache keys are tagged with `inventory_{product_id}`.
- **Invalidation**: Clears cache tags on `catalog.product.update.after` and post-transaction commits in command handlers.

---

## Part 4: Red Team Architecture Review

1. **Race Conditions / Double Allocation Challenge**  
   - *Risk*: Multiple customers check out the last item concurrently, causing overselling.
   - *Mitigation*: The command handler applies lock queries (`SELECT FOR UPDATE`) on the `product_inventories` rows before executing checks or increments, preventing concurrent modifications during order reservation.
2. **N+1 Performance Bottlenecks**  
   - *Risk*: Loop queries on inventory values in listing grids.
   - *Mitigation*: We leverage native `product_inventory_indices` which flat-maps inventory records per channel, allowing single-query grid renders.

---

### STOP. Waiting for Decision Proof Approval.
*Forensic audit and architectural designs are compiled. No implementation has been started.*
