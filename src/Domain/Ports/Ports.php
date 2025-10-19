<?php
namespace App\Domain\Ports;


use App\Domain\Book\CopyStatus;
use App\Domain\Lending\{Loan, Reservation};
use App\Domain\Member\Member;


interface BookRepository { public function find(string $bookId): ?object; }
interface MemberRepository { public function find(string $id): ?Member; public function save(Member $m): void; }
interface LoanRepository { public function save(Loan $l): void; public function findActiveById(string $id): ?Loan; public function countActiveForMember(string $memberId): int; public function existsActiveForMemberAndBook(string $memberId,string $bookId): bool; }
interface ReservationRepository { public function save(Reservation $r): void; public function findQueueHead(string $bookId): ?Reservation; public function countActiveForBook(string $bookId): int; public function existsForMemberAndBook(string $memberId,string $bookId): bool; public function popHeadAndReindex(string $bookId): ?Reservation; }
interface CopyRepository { public function lockAvailableCopyForBook(string $bookId): ?object; public function lockReservedCopyForBookAndMember(string $bookId,string $memberId): ?object; public function updateStatus(string $copyId, CopyStatus $status): void; }
interface FinePaymentRepository { public function record(string $id,string $memberId,?string $loanId,int $cents,\DateTimeImmutable $paidAt): void; }
