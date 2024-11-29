<?php

namespace App\Message;

class CheckDateMessage
{
    private $entityId;

    public function __construct(int $entityId)
    {
        $this->entityId = $entityId;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}