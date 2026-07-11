# Enterprise Compatibility Module — Architectural Blueprint & ECIS

This document contains the completed **Forensic Audit**, **Gap Analysis**, **Technical Design Specification (TDS)**, **Architecture Freeze Review**, **Architectural Decision Record (ADR)**, and **Enterprise Compatibility Implementation Specification (ECIS)**. 

No production code modifications or migrations have been executed. We stop after this specification is delivered to await approval.

---

## Part 1: Bounded Context Forensic Audit

### 1. Bagisto Native Component Assessment
- **Product Types & Configurable Variants**  
  - *Location*: `packages/Webkul/Product/src/Type/`
  - *Details*: Configurable products group child variants together but do not enforce inter-product hardware compatibility (e.g., matching a selected CPU with a compatible motherboard).
- **Attributes System (EAV)**  
  - *Location*: `packages/Webkul/Attribute/src/Models/Attribute.php`
  - *Details*: Native attributes (like `socket` or `ddr_generation`) store raw strings/integers per product, but lack logic to link and validate values across different products.
- **Elasticsearch & Flat Index**  
  - *Details*: Native flat tables (`product_flat`) and Elasticsearch indexes serve query filtering, but do not contain relational logic for checking multi-product compatibility.

---

## Part 2: Gap Analysis & Reuse Matrix

| Hardware Feature | Native Status | Custom Extension Design |
|---|---|---|
| **CPU Socket Mapping** | NATIVE (String EAV) | CUSTOM (Relational match socket value validation) |
| **RAM Generation (DDR3/DDR4/DDR5)** | NATIVE (String EAV) | CUSTOM (DDR type parity check) |
| **PCIe Lane & Gen Support** | NATIVE (String EAV) | CUSTOM (Sum of lanes availability validation) |
| **Case GPU / Cooler Clearance** | NATIVE (Integer EAV) | CUSTOM (Dimension threshold validator: Case length >= GPU length) |
| **PSU Power Headroom** | NATIVE (Integer EAV) | CUSTOM (Total estimated draw <= PSU wattage * efficiency factor) |
| **M.2 & SATA Ports Availability** | NATIVE (Integer EAV) | CUSTOM (Deduction tracking logic) |

---

## Part 3: Technical Design Specification (TDS)

We will construct a **Compatibility Rule Engine** designed to handle three layers of compatibility:
1. **Physical Parity (EAV Equal)**: Socket match, RAM type match.
2. **Dimension Constraints (Inequality)**: GPU length <= Case clearance.
3. **Power Budgeting (Sum Allocation)**: Total PC power draw <= PSU Wattage.

### 1. Bounded Context Structure
```
app/
├── Application/
│   ├── DTO/
│   │   ├── CheckCompatibilityDto.php
│   │   └── RegisterCompatibilityRuleDto.php
│   └── Handlers/
│       ├── CheckCompatibilityHandler.php
│       └── RegisterCompatibilityRuleHandler.php
├── Domain/
│   ├── Rules/
│   │   ├── SocketCompatibilityRule.php
│   │   ├── MemoryCompatibilityRule.php
│   │   └── PowerBudgetCompatibilityRule.php
│   ├── Services/
│   │   └── CompatibilityEngine.php
│   └── Specifications/
│       └── CompatibilitySpecification.php
```

### 2. Compatibility Rule Engine Architecture
The engine will load all selected product EAV models into a composite collection (`HardwareProfile`) and evaluate them against registered rules:
```php
interface CompatibilityRuleInterface
{
    public function evaluate(HardwareProfile $profile): EvaluationResult;
}
```

---

## Part 4: Architectural Decision Record (ADR)

### Decision: Relational Mapping Combined with Dynamic EAV Evaluators
- **Approach**: Compatibility rules are saved in `compatibility_rules` mapping product pairs for custom restrictions, while general physical rules (e.g., socket matching) are evaluated dynamically by loading Product EAV attributes.
- **Justification**:
  1. **Minimal DB Overhead**: General rules do not require storing millions of pivot rows; socket matches check `cpu_socket` attribute values directly.
  2. **Rule Extensibility**: Developers can register composite rules (e.g., AI config helpers) by implementing `CompatibilityRuleInterface`.

---

## Part 5: Enterprise Compatibility ECIS

### 1. Data Model & Relationships (ERD Mapping)
- **`compatibility_rules` table**:
  - `id` (primary key)
  - `parent_product_id` (foreign key referencing `products(id)` on delete cascade)
  - `child_product_id` (foreign key referencing `products(id)` on delete cascade)
  - `rule_type` (string, e.g. `'socket'`, `'ram'`)
  - Indexes: `Composite (parent_product_id, child_product_id)`

### 2. Scalability Plan (1M Products, 50M Rules)
- **Caching**: EAV compatibility profiles are cached using tags: `product_profile_{id}`.
- **Bulk Loading**: Evaluations load profiles using `whereIn` queries, resolving N+1 database queries.

---

### STOP. Awaiting Implementation Approval.
*Forensic audit and architectural designs are compiled. No code has been written.*
