<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class GlassesStock extends Model
{
    use HasFactory;

    protected $table = 'glasses_stock';

    protected $fillable = [
        'item_name',
        'item_code',
        'item_type',
        'brand',
        'description',
        'quantity',
        'min_quantity',
        'cost_price',
        'selling_price',
        'supplier',
        'location',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer',
        'min_quantity' => 'integer',
    ];

    // Relationships
    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'stock_id');
    }

    // Accessors
    public function getIsLowStockAttribute()
    {
        return $this->quantity <= $this->min_quantity;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->quantity == 0) {
            return 'bg-red-100 text-red-800';
        } elseif ($this->is_low_stock) {
            return 'bg-yellow-100 text-yellow-800';
        }
        return 'bg-green-100 text-green-800';
    }

    public function getStatusTextAttribute()
    {
        if ($this->quantity == 0) {
            return 'Out of Stock';
        } elseif ($this->is_low_stock) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    // Methods
    public function addStock($quantity, $notes = null, $referenceType = null, $referenceId = null)
    {
        $quantityBefore = $this->quantity;
        $this->quantity += $quantity;
        $this->save();

        StockMovement::create([
            'stock_id' => $this->id,
            'movement_type' => 'in',
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);

        return $this;
    }

    public function removeStock($quantity, $notes = null, $referenceType = null, $referenceId = null)
    {
        if ($this->quantity < $quantity) {
            throw new \Exception('Insufficient stock. Available: ' . $this->quantity);
        }

        $quantityBefore = $this->quantity;
        $this->quantity -= $quantity;
        $this->save();

        StockMovement::create([
            'stock_id' => $this->id,
            'movement_type' => 'out',
            'quantity' => -$quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);

        return $this;
    }

    public function adjustStock($newQuantity, $notes = null)
    {
        $quantityBefore = $this->quantity;
        $difference = $newQuantity - $quantityBefore;
        $this->quantity = $newQuantity;
        $this->save();

        StockMovement::create([
            'stock_id' => $this->id,
            'movement_type' => 'adjustment',
            'quantity' => $difference,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->quantity,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);

        return $this;
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_quantity');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('item_type', $type);
    }
}
