<?php

namespace App\Observers;

use App\Models\MenuIngredient;
use App\Models\OrderItem;

class OrderItemObserver
{
    public function created(OrderItem $orderItem): void
    {
        $recipe = MenuIngredient::query()
            ->with(['ingredient.unit'])
            ->where('menu_id', $orderItem->menu_id)
            ->orderBy('ingredient_id')
            ->get();

        foreach ($recipe as $composition) {
            $perItemMilli = (int) round((float) $composition->quantity * 1000);
            $requiredMilli = $perItemMilli * (int) $orderItem->quantity;

            $orderItem->ingredientSnapshots()->create([
                'ingredient_id' => $composition->ingredient_id,
                'ingredient_name' => $composition->ingredient->name,
                'unit_abbreviation' => $composition->ingredient->unit->abbreviation,
                'quantity_per_item' => $composition->quantity,
                'required_quantity' => intdiv($requiredMilli, 1000) . '.' . str_pad((string) ($requiredMilli % 1000), 3, '0', STR_PAD_LEFT),
            ]);
        }

        $orderItem->forceFill(['recipe_snapshotted_at' => now()])->saveQuietly();
    }
}
