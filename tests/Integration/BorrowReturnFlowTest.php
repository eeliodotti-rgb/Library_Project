<?php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


final class BorrowReturnFlowTest extends WebTestCase
{
public function test_end_to_end_borrow_return(): void
{
$c = static::createClient();
$mId = $this->getMemberId('Alice'); $bId = $this->getBookId('The Odyssey');
$resp = $c->request('POST','/api/borrow',[ 'json'=>['memberId'=>$mId,'bookId'=>$bId] ]);
$this->assertResponseStatusCodeSame(201);
$loanId = $resp->toArray()['loanId'];
$c->request('POST', "/api/return/$loanId");
$this->assertResponseIsSuccessful();
}
// helper methods fetch seed ids via DB connection
}
