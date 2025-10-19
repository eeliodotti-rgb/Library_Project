<?php
namespace App\Infrastructure\Doctrine;


use Doctrine\ORM\EntityManagerInterface;


class Transactional
{
public function __construct(private EntityManagerInterface $em) {}
public function run(callable $fn) {
return $this->em->wrapInTransaction($fn);
}
}
