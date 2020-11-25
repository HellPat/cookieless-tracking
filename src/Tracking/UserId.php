<?php

declare(strict_types=1);

namespace App\Tracking;

use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;

final class UserId
{
    private AbstractUid $id;

    private function __construct(AbstractUid $id)
    {
        $this->id = $id;
    }

    public static function create(): self
    {
        return new self(new Ulid());
    }

    public static function fromString(string $id): self
    {
        return new self(Ulid::fromString($id));
    }

    public function toString(): string
    {
        return $this->id->toRfc4122();
    }
}
