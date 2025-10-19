<?php
namespace App\Domain\Lending;


class DomainException extends \RuntimeException {}
class NoCopiesAvailable extends DomainException {}
class MemberSuspended extends DomainException {}
class LoanLimitExceeded extends DomainException {}
class LoanOverdue extends DomainException {}
class MaxRenewalsReached extends DomainException {}
class ReservationExists extends DomainException {}
class AlreadyHoldingBook extends DomainException {}
class NotReservationHolder extends DomainException {}
