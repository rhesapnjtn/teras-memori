<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * User yang sudah login boleh melihat daftar order miliknya.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Pemilik order atau admin boleh melihat detail order.
     */
    public function view(User $user, Order $order): bool
    {
        return $this->isAdmin($user) || $this->ownsOrder($user, $order);
    }

    /**
     * Hanya pemilik order (atau admin) yang boleh membatalkan,
     * dan hanya selama order masih berstatus pending.
     */
    public function cancel(User $user, Order $order): bool
    {
        if (! $this->isAdmin($user) && ! $this->ownsOrder($user, $order)) {
            return false;
        }

        return $order->status === 'pending';
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    private function ownsOrder(User $user, Order $order): bool
    {
        $customerEmail = $order->customer?->email;

        if ($customerEmail === null) {
            return false;
        }

        return strtolower(trim($customerEmail))
            === strtolower(trim($user->email));
    }
}
