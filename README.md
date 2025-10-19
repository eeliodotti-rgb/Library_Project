# Library Lending Service


Self-contained Symfony API + MariaDB implementing real library rules.


## Run


```sh
make up
docker compose exec php composer install
make migrate
make seed


API base: http://localhost:8080


   --- Domain Diagram ---
 
Members --(borrow)-> Loans --(for)-> Copies --(of)-> Books __ fines (payments) Books -- reservations queue (FIFO) --> Members

States: Copy [AVAILABLE -> ON_LOAN -> AVAILABLE/RESERVED; any -> LOST]

   --- Core Rules ---

Max active loans/member: 5

Loan = 14d; Renewal: +14d up to 2, only if not overdue and no reservations

Overdue fine: €0.50/day; suspension if unpaid > €20

Reservations FIFO; return triggers 48h hold for head member


   --- Exemple calls ---

- Borrow : 
curl -X POST :8080/api/borrow -H 'Content-Type: application/json' -d '{"memberId":"<m>","bookId":"<b>"}'

- Return :
curl -X POST :8080/api/return/<loanId>

- Renew :
curl -X POST :8080/api/renew/<loanId>

- Reserve : 
curl -X POST :8080/api/reservations -H 'Content-Type: application/json' -d '{"memberId":"<m>","bookId":"<b>"}'

- Pay fine :
curl -X POST :8080/api/fines/pay -H 'Content-Type: application/json' -d '{"memberId":"<m>","amountCents":500}'

- Testing : 
make test







---


## What’s intentionally kept lean
- Minimal controllers (validation can be added with Symfony Validator).
- Entities for BookEntity/MemberEntity/LoanEntity shown conceptually to keep the document focused.
- Error handling: map domain exceptions to HTTP codes (e.g., `NoCopiesAvailable` → 409). Add an ExceptionListener.


---


## HTTP Error Mapping (suggested)
- 400: invalid input
- 401/403: auth (if added)
- 404: not found
- 409: `NO_COPIES_AVAILABLE`, `RESERVATIONS_EXIST`, etc.


---


## Next Steps (nice-to-haves)
- AuthN/Z with API tokens
- OpenAPI/Swagger docs
- Prometheus metrics endpoint


