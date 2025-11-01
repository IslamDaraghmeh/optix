<?php

namespace App\Domain\Glasses\Repositories;

use App\Domain\Glasses\Models\GlassOrder;
use App\Domain\Glasses\DTOs\GlassOrderSearchDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GlassOrderRepositoryInterface
{
    /**
     * Find a glass order by ID
     */
    public function findById(int $id): ?GlassOrder;

    /**
     * Find glass orders with search and filter criteria
     */
    public function search(GlassOrderSearchDTO $searchDTO): LengthAwarePaginator;

    /**
     * Create a new glass order
     */
    public function create(GlassOrder $glassOrder): GlassOrder;

    /**
     * Update an existing glass order
     */
    public function update(GlassOrder $glassOrder): GlassOrder;

    /**
     * Delete a glass order
     */
    public function delete(int $id): bool;

    /**
     * Get all glass orders for a specific patient
     */
    public function findByPatientId(int $patientId): array;

    /**
     * Get glass orders by status
     */
    public function findByStatus(string $status): array;

    /**
     * Get glass orders count by status
     */
    public function getCountByStatus(): array;

    /**
     * Check if patient exists
     */
    public function patientExists(int $patientId): bool;
}
