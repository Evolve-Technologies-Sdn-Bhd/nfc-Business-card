<?php

namespace App\Http\Controllers\Api\Concerns;

trait ScopesBuilderPlan
{
    /**
     * Scope a builder query to items available for the requested plan.
     *
     * Business rule: a free-trial ('free') user receives the 'basic' feature set,
     * PLUS anything the admin explicitly marked as available for the 'free' plan.
     * Items with no plan restriction (NULL or empty available_plans) are always available.
     */
    protected function applyBuilderPlanScope($query, string $column, ?string $plan)
    {
        if (!$plan) {
            return $query;
        }

        $plans = $plan === 'free' ? ['free', 'basic'] : [$plan];

        return $query->where(function ($q) use ($plans, $column) {
            foreach ($plans as $p) {
                $q->orWhereJsonContains($column, $p);
            }
            $q->orWhereNull($column)->orWhereJsonLength($column, 0);
        });
    }
}
