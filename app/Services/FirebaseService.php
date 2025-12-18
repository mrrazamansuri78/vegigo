<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected $database;
    protected $isEnabled = false;

    public function __construct()
    {
        $credentialsPath = config('services.firebase.credentials');
        $databaseUrl = config('services.firebase.database_url');

        // Check if Firebase is configured
        if (file_exists($credentialsPath) && $databaseUrl) {
            try {
                $factory = (new Factory)
                    ->withServiceAccount($credentialsPath)
                    ->withDatabaseUri($databaseUrl);

                $this->database = $factory->createDatabase();
                $this->isEnabled = true;
            } catch (\Exception $e) {
                // Firebase not available, continue without it
                \Log::warning('Firebase initialization failed: ' . $e->getMessage());
                $this->isEnabled = false;
            }
        }
    }

    /**
     * Update order location in real-time
     */
    public function updateOrderLocation($orderId, $latitude, $longitude, $status = null)
    {
        if (!$this->isEnabled || !$this->database) {
            return false;
        }

        try {
            $reference = $this->database->getReference("orders/{$orderId}/location");
            
            $data = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'updated_at' => now()->toIso8601String(),
            ];

            if ($status) {
                $data['status'] = $status;
            }

            $reference->set($data);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase updateOrderLocation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order location from Firebase
     */
    public function getOrderLocation($orderId)
    {
        if (!$this->isEnabled || !$this->database) {
            return null;
        }

        try {
            $reference = $this->database->getReference("orders/{$orderId}/location");
            return $reference->getValue();
        } catch (\Exception $e) {
            \Log::error('Firebase getOrderLocation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update order status in real-time
     */
    public function updateOrderStatus($orderId, $status, $data = [])
    {
        if (!$this->isEnabled || !$this->database) {
            return false;
        }

        try {
            $reference = $this->database->getReference("orders/{$orderId}");
            
            $updateData = array_merge([
                'status' => $status,
                'updated_at' => now()->toIso8601String(),
            ], $data);

            $reference->update($updateData);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase updateOrderStatus failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update supply order status in real-time
     */
    public function updateSupplyOrder($orderId, $status, $data = [])
    {
        if (!$this->isEnabled || !$this->database) {
            return false;
        }

        try {
            $reference = $this->database->getReference("supply_orders/{$orderId}");
            
            $updateData = array_merge([
                'status' => $status,
                'updated_at' => now()->toIso8601String(),
            ], $data);

            $reference->update($updateData);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase updateSupplyOrder failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Listen to order changes
     */
    public function listenToOrder($orderId, callable $callback)
    {
        if (!$this->isEnabled || !$this->database) {
            return false;
        }

        try {
            $reference = $this->database->getReference("orders/{$orderId}");
            
            $reference->onValue(function ($snapshot) use ($callback) {
                $callback($snapshot->getValue());
            });
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase listenToOrder failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all active orders
     */
    public function getActiveOrders()
    {
        if (!$this->isEnabled || !$this->database) {
            return [];
        }

        try {
            $reference = $this->database->getReference('orders');
            return $reference->getValue() ?? [];
        } catch (\Exception $e) {
            \Log::error('Firebase getActiveOrders failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update product stock in real-time
     */
    public function updateProductStock($productId, $quantity)
    {
        if (!$this->isEnabled || !$this->database) {
            return false;
        }

        try {
            $reference = $this->database->getReference("products/{$productId}");
            
            $reference->update([
                'quantity' => $quantity,
                'updated_at' => now()->toIso8601String(),
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase updateProductStock failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete order from Firebase
     */
    public function deleteOrder($orderId)
    {
        if (!$this->isEnabled || !$this->database) {
            return false;
        }

        try {
            $reference = $this->database->getReference("orders/{$orderId}");
            $reference->remove();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase deleteOrder failed: ' . $e->getMessage());
            return false;
        }
    }
}

