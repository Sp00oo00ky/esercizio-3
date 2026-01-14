<?php
declare(strict_types=1);

namespace Src\Storage\Repositories;

use Src\Domain\Member;
use Src\Storage\CsvStorage;

/**
 * Repository dei membri:
 * - legge/scrive su members.csv
 */
final class MemberRepository
{
    private const FILE = 'members.csv';
    private const HEADER = ['id', 'full_name'];

    public function __construct(private CsvStorage $storage) {}

    /**
     * @return Member[]
     */
    public function findAll(): array
    {
        $rows = $this->storage->readAll(self::FILE);
        $members = [];

        foreach ($rows as $row) {
            $members[] = new Member(
                (string)$row['id'],
                (string)$row['full_name']
            );
        }

        return $members;
    }

    public function findById(string $memberId): ?Member
    {
        foreach ($this->findAll() as $member) {
            if ($member->id() === $memberId) {
                return $member;
            }
        }
        return null;
    }
}
