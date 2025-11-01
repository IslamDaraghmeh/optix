# Glasses Business Logic Extraction

## Overview

This document outlines the extraction of glasses business logic from the Laravel controller into a clean architecture structure. The refactoring follows the user's preference for applying clean architecture at the module level.

## Architecture Structure

### 1. Domain Layer (`app/Domain/Glasses/`)

#### Models

-   **`GlassOrder.php`** - Core domain model representing a glasses order

    -   Encapsulates business rules for status transitions
    -   Validates edit/deletion permissions based on status
    -   Contains domain-specific logic for glass order management

-   **`GlassStatus.php`** - Enum for glass order status
    -   Defines valid statuses: PENDING, READY, DELIVERED
    -   Provides display names, badge classes, and descriptions
    -   Includes validation for status transitions

#### DTOs (Data Transfer Objects)

-   **`GlassOrderDTO.php`** - For transferring complete glass order data
-   **`CreateGlassOrderDTO.php`** - For creating new glass orders
-   **`UpdateGlassOrderDTO.php`** - For updating existing glass orders
-   **`GlassOrderSearchDTO.php`** - For search and filtering operations

#### Repositories

-   **`GlassOrderRepositoryInterface.php`** - Contract for data access operations

### 2. Infrastructure Layer (`app/Infrastructure/Repositories/`)

-   **`EloquentGlassOrderRepository.php`** - Concrete implementation using Eloquent ORM
    -   Maps between domain models and Eloquent models
    -   Handles database operations
    -   Implements the repository interface

### 3. Application Layer (`app/Application/`)

#### Services

-   **`GlassOrderService.php`** - Main service containing business logic
    -   Glass order CRUD operations
    -   Business rule validation
    -   Status management
    -   Search and filtering
    -   Statistics generation
    -   Available options management (lens types, frame types)

#### Providers

-   **`GlassOrderServiceProvider.php`** - Dependency injection configuration

### 4. Presentation Layer (Updated)

-   **`GlassController.php`** - Refactored controller
    -   Now uses service layer instead of direct model access
    -   Handles HTTP requests/responses only
    -   Delegates business logic to services

## Key Business Logic Extracted

### 1. Status Management

-   **Status Transitions**: Validates proper status flow (PENDING → READY → DELIVERED)
-   **Business Rules**: Prevents invalid status changes
-   **Permissions**: Controls when orders can be edited/deleted based on status

### 2. Validation Rules

-   **Minimum Price**: $50 minimum for glass orders
-   **Lens/Frame Types**: Validates against predefined lists
-   **Patient Existence**: Ensures patient exists before creating orders

### 3. Business Operations

-   **Price Calculations**: Tax calculations for pricing
-   **Search & Filtering**: Advanced search capabilities
-   **Statistics**: Order counts by status
-   **Available Options**: Management of lens and frame type options

### 4. Data Integrity

-   **Audit Trail**: Maintains creation and update timestamps
-   **Soft Constraints**: Business rules that prevent data inconsistencies
-   **Error Handling**: Comprehensive exception handling with meaningful messages

## Benefits of This Architecture

### 1. Separation of Concerns

-   **Controller**: Only handles HTTP concerns
-   **Service**: Contains business logic
-   **Repository**: Handles data access
-   **Domain Models**: Encapsulate business rules

### 2. Testability

-   Each layer can be tested independently
-   Business logic is isolated and easily unit tested
-   Repository can be mocked for service testing

### 3. Maintainability

-   Business rules are centralized in domain models
-   Changes to business logic don't affect other layers
-   Clear boundaries between layers

### 4. Reusability

-   Service layer can be used by multiple controllers
-   Repository interface allows different implementations
-   DTOs provide consistent data structures

### 5. Scalability

-   Easy to add new features without affecting existing code
-   Repository pattern allows for different data sources
-   Service layer can be extended with new business operations

## Usage Examples

### Creating a Glass Order

```php
$createDTO = CreateGlassOrderDTO::fromArray($request->validated());
$glassOrder = $this->glassOrderService->createGlassOrder($createDTO);
```

### Updating Status

```php
$status = GlassStatus::fromString('ready');
$this->glassOrderService->updateGlassOrderStatus($id, $status);
```

### Searching Orders

```php
$searchDTO = GlassOrderSearchDTO::fromRequest($request->all());
$results = $this->glassOrderService->searchGlassOrders($searchDTO);
```

## Configuration

The service provider is registered in `config/app.php`:

```php
App\Application\Providers\GlassOrderServiceProvider::class,
```

This enables dependency injection and makes the service available throughout the application.

## Future Enhancements

1. **Event System**: Add domain events for status changes
2. **Notifications**: Automatic notifications when status changes
3. **Reporting**: Advanced reporting capabilities
4. **Integration**: API endpoints for external systems
5. **Caching**: Add caching layer for frequently accessed data

## Conclusion

The glasses business logic has been successfully extracted into a clean, maintainable architecture that follows Laravel best practices and clean architecture principles. The code is now more testable, maintainable, and follows the single responsibility principle throughout all layers.
