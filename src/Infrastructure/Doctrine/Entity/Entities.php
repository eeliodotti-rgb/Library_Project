<?php
namespace App\Infrastructure\Doctrine\Entity;


use App\Domain\Book\CopyStatus;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'copies')]
class CopyEntity
{
#[ORM\Id]
#[ORM\Column(type: 'string', length: 36)] public string $id;
#[ORM\Column(type: 'string', length: 36)] public string $bookId;
#[ORM\Column(type: 'string', enumType: CopyStatus::class)] public CopyStatus $status;
#[ORM\Column(type: 'string', length: 36, nullable: true)] public ?string $reservedForMemberId = null;
}
