<?php

namespace App\Application\Services;

use App\Domain\Glasses\Models\GlassOrder;
use App\Domain\Glasses\Models\GlassStatus;
use App\Domain\Glasses\DTOs\CreateGlassOrderDTO;
use App\Domain\Glasses\DTOs\UpdateGlassOrderDTO;
use App\Domain\Glasses\DTOs\GlassOrderSearchDTO;
use App\Domain\Glasses\DTOs\GlassOrderDTO;
use App\Domain\Glasses\Repositories\GlassOrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GlassOrderService
{
    public function __construct(
        private GlassOrderRepositoryInterface $glassOrderRepository
    ) {
    }

    /**
     * Create a new glass order
     */
    public function createGlassOrder(CreateGlassOrderDTO $createDTO): GlassOrderDTO
    {
        // Validate the DTO
        $createDTO->validate();

        // Check if patient exists
        if (!$this->glassOrderRepository->patientExists($createDTO->patientId)) {
            throw new \InvalidArgumentException('Patient not found');
        }

        // Create domain model
        $glassOrder = new GlassOrder(
            patientId: $createDTO->patientId,
            lensType: $createDTO->lensType,
            frameType: $createDTO->frameType,
            price: $createDTO->price,
            status: $createDTO->status
        );

        // Save to repository
        $savedGlassOrder = $this->glassOrderRepository->create($glassOrder);

        return $this->mapToDTO($savedGlassOrder);
    }

    /**
     * Update an existing glass order
     */
    public function updateGlassOrder(int $id, UpdateGlassOrderDTO $updateDTO): GlassOrderDTO
    {
        // Validate the DTO
        $updateDTO->validate();

        // Find existing glass order
        $glassOrder = $this->glassOrderRepository->findById($id);
        if (!$glassOrder) {
            throw new \InvalidArgumentException('Glass order not found');
        }

        // Check if patient exists
        if (!$this->glassOrderRepository->patientExists($updateDTO->patientId)) {
            throw new \InvalidArgumentException('Patient not found');
        }

        // Check if order can be edited
        if (!$glassOrder->canBeEdited()) {
            throw new \InvalidArgumentException('Cannot edit delivered glass orders');
        }

        // Update the glass order
        $glassOrder->updateDetails(
            lensType: $updateDTO->lensType,
            frameType: $updateDTO->frameType,
            price: $updateDTO->price
        );

        $glassOrder->updateStatus($updateDTO->status);

        // Save changes
        $updatedGlassOrder = $this->glassOrderRepository->update($glassOrder);

        return $this->mapToDTO($updatedGlassOrder);
    }

    /**
     * Update only the status of a glass order
     */
    public function updateGlassOrderStatus(int $id, GlassStatus $newStatus): GlassOrderDTO
    {
        $glassOrder = $this->glassOrderRepository->findById($id);
        if (!$glassOrder) {
            throw new \InvalidArgumentException('Glass order not found');
        }

        $glassOrder->updateStatus($newStatus);
        $updatedGlassOrder = $this->glassOrderRepository->update($glassOrder);

        return $this->mapToDTO($updatedGlassOrder);
    }

    /**
     * Get a glass order by ID
     */
    public function getGlassOrder(int $id): GlassOrderDTO
    {
        $glassOrder = $this->glassOrderRepository->findById($id);
        if (!$glassOrder) {
            throw new \InvalidArgumentException('Glass order not found');
        }

        return $this->mapToDTO($glassOrder);
    }

    /**
     * Search glass orders with filters
     */
    public function searchGlassOrders(GlassOrderSearchDTO $searchDTO): LengthAwarePaginator
    {
        return $this->glassOrderRepository->search($searchDTO);
    }

    /**
     * Delete a glass order
     */
    public function deleteGlassOrder(int $id): bool
    {
        $glassOrder = $this->glassOrderRepository->findById($id);
        if (!$glassOrder) {
            throw new \InvalidArgumentException('Glass order not found');
        }

        if (!$glassOrder->canBeDeleted()) {
            throw new \InvalidArgumentException('Cannot delete glass orders that are not pending');
        }

        return $this->glassOrderRepository->delete($id);
    }

    /**
     * Get glass orders for a specific patient
     */
    public function getGlassOrdersByPatient(int $patientId): Collection
    {
        $glassOrders = $this->glassOrderRepository->findByPatientId($patientId);

        return collect($glassOrders)->map(function ($glassOrder) {
            return $this->mapToDTO($glassOrder);
        });
    }

    /**
     * Get glass orders by status
     */
    public function getGlassOrdersByStatus(string $status): Collection
    {
        $glassOrders = $this->glassOrderRepository->findByStatus($status);

        return collect($glassOrders)->map(function ($glassOrder) {
            return $this->mapToDTO($glassOrder);
        });
    }

    /**
     * Get glass order statistics
     */
    public function getGlassOrderStatistics(): array
    {
        $counts = $this->glassOrderRepository->getCountByStatus();

        $statistics = [];
        foreach (GlassStatus::getAll() as $status) {
            $statistics[$status->value] = [
                'count' => $counts[$status->value] ?? 0,
                'display_name' => $status->getDisplayName(),
                'badge_class' => $status->getBadgeClass(),
                'description' => $status->getDescription(),
            ];
        }

        return $statistics;
    }

    /**
     * Get available lens types (business logic for predefined options)
     */
    public function getAvailableLensTypes(): array
    {
        return [
            'Single Vision',
            'Progressive',
            'Bifocal',
            'Trifocal',
            'Reading',
            'Computer',
            'Photochromatic',
            'Polarized',
            'High Index',
            'Polycarbonate',
        ];
    }

    /**
     * Get available frame types (business logic for predefined options)
     */
    public function getAvailableFrameTypes(): array
    {
        return [
            'Metal',
            'Plastic',
            'Titanium',
            'Stainless Steel',
            'Acetate',
            'TR90',
            'Wood',
            'Horn',
            'Carbon Fiber',
        ];
    }

    /**
     * Calculate price with tax (business logic)
     */
    public function calculatePriceWithTax(float $basePrice, float $taxRate = 0.08): float
    {
        return $basePrice * (1 + $taxRate);
    }

    /**
     * Validate glass order business rules
     */
    public function validateGlassOrderBusinessRules(CreateGlassOrderDTO $dto): array
    {
        $errors = [];

        // Business rule: Minimum price validation
        if ($dto->price < 50) {
            $errors[] = 'Minimum price for glasses order is $50';
        }

        // Business rule: Lens type validation
        $availableLensTypes = $this->getAvailableLensTypes();
        if (!in_array($dto->lensType, $availableLensTypes)) {
            $errors[] = 'Invalid lens type. Must be one of: ' . implode(', ', $availableLensTypes);
        }

        // Business rule: Frame type validation
        $availableFrameTypes = $this->getAvailableFrameTypes();
        if (!in_array($dto->frameType, $availableFrameTypes)) {
            $errors[] = 'Invalid frame type. Must be one of: ' . implode(', ', $availableFrameTypes);
        }

        return $errors;
    }

    /**
     * Map domain model to DTO
     */
    private function mapToDTO(GlassOrder $glassOrder): GlassOrderDTO
    {
        return new GlassOrderDTO(
            id: $glassOrder->getId(),
            patientId: $glassOrder->getPatientId(),
            lensType: $glassOrder->getLensType(),
            frameType: $glassOrder->getFrameType(),
            price: $glassOrder->getPrice(),
            status: $glassOrder->getStatus(),
            createdAt: $glassOrder->getCreatedAt(),
            updatedAt: $glassOrder->getUpdatedAt()
        );
    }
}
