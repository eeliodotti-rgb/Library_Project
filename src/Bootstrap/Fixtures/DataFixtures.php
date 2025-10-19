<?php
namespace App\Bootstrap\Fixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Infrastructure\Doctrine\Entity\{BookEntity,CopyEntity,MemberEntity};
use App\Domain\Book\CopyStatus;
use Ramsey\Uuid\Uuid;


class DataFixtures extends Fixture
{
public function load(ObjectManager $om): void
{
// Members
$m1 = new MemberEntity(Uuid::uuid4()->toString(),'Alice','alice@example.com');
$m2 = new MemberEntity(Uuid::uuid4()->toString(),'Bob','bob@example.com');
$om->persist($m1); $om->persist($m2);


// Book with 2 copies
$b = new BookEntity(Uuid::uuid4()->toString(),'9780140449136','The Odyssey',['Homer'],['Epic']);
$om->persist($b);
$om->persist(new CopyEntity(Uuid::uuid4()->toString(), $b->id, CopyStatus::AVAILABLE));
$om->persist(new CopyEntity(Uuid::uuid4()->toString(), $b->id, CopyStatus::AVAILABLE));


$om->flush();
}
}
