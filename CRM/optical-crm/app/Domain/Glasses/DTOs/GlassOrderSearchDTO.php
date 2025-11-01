<?php

namespace App\Domain\Glasses\DTOs;

use App\Domain\Glasses\Models\GlassStatus;

class GlassOrderSearchDTO
{
    public function __construct(
        public ?string $search = null,
        public ?GlassStatus $status = null,
        public ?\DateTime $dateFrom = null,
        public ?\DateTime $dateTo = null,
        public int $perPage = 15,
        public int $page = 1
    ) {
    }

    public static function fromRequest(array $request): self
    {
        return new self(
            search: !empty($request['search']) ? trim($request['search']) : null,
            status: !empty($request['status']) ? GlassStatus::fromString($request['status']) : null,
            dateFrom: !empty($request['date_from']) ? new \DateTime($request['date_from']) : null,
            dateTo: !empty($request['date_to']) ? new \DateTime($request['date_to']) : null,
            perPage: (int) ($request['per_page'] ?? 15),
            page: (int) ($request['page'] ?? 1)
        );
    }

    public function hasFilters(): bool
    {
        return $this->search !== null ||
            $this->status !== null ||
            $this->dateFrom !== null ||
            $this->dateTo !== null;
    }
}
