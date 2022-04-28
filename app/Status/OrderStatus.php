<?php


namespace App\Status;

abstract class OrderStatus
{
    const PENDING = "PENDING";
    const PICKUP = "PICKUP";
    const DELIVERED = "DELIVERED";
    const CANCELLED = "CANCELLED";
}
