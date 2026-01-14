<?php
declare(strict_types=1);

namespace Src\Domain;

/**
 * Rappresenta un utente (membro) della biblioteca.
 */
final class Member
{
    public function __construct(
        private string $id,
        private string $fullName
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }
}
