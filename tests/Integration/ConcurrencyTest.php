<?php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


final class ConcurrencyTest extends WebTestCase
{
public function test_two_borrows_do_not_get_same_copy(): void
{
$c1 = static::createClient();
$c2 = static::createClient();
$m1 = $this->getMemberId('Alice'); $m2 = $this->getMemberId('Bob'); $book = $this->getBookId('The Odyssey');
$r1 = $c1->request('POST','/api/borrow',[ 'json'=>['memberId'=>$m1,'bookId'=>$book] ]);
$r2 = $c2->request('POST','/api/borrow',[ 'json'=>['memberId'=>$m2,'bookId'=>$book] ]);
$id1 = $r1->toArray()['copyId'];
$id2 = $r2->toArray()['copyId'];
$this->assertNotSame($id1,$id2);
}
}
