# Dorm Desk design
The front desk uses a staff-only MVC application. Routes select a controller, controllers validate input, the service enforces business rules inside transactions, and Blade renders records and errors.

## Class diagram
```mermaid
classDiagram
class DeskController {
 -DormService service
 +admit(request)
 +stay(id, action)
 +collect(id)
 +reports(request)
}
class DormService {
 +admit(tenant, roomId, plan)
 +updateStay(id, action)
 +collect(id)
 -issue(stay, rate)
 +log(description)
}
class Occupancy {
 -int capacity
 -int occupied
 +admit()
 +available() int
}
class RentPlan {
 <<abstract>>
 +select(name) RentPlan
 +monthly(base) int
}
class StandardRent {
 +monthly(base) int
}
class ScholarRent {
 +monthly(base) int
}
DeskController --> DormService
DormService ..> Occupancy
DormService ..> RentPlan
RentPlan <|-- StandardRent
RentPlan <|-- ScholarRent
```

## Entity relationship diagram
```mermaid
erDiagram
 BUILDINGS ||--o{ ROOMS : contains
 ROOMS ||--o{ STAYS : accommodates
 TENANTS ||--o{ STAYS : occupies
 STAYS ||--|{ BILLS : incurs
 USERS ||--o{ EVENTS : records
 BUILDINGS { bigint id PK
 string name UK }
 ROOMS { bigint id PK
 bigint building_id FK
 string name UK
 int capacity
 int rent }
 TENANTS { bigint id PK
 string name
 string email UK }
 STAYS { bigint id PK
 bigint room_id FK
 bigint tenant_id FK
 date start
 date end
 string plan
 int monthly_rent }
 BILLS { bigint id PK
 bigint stay_id FK
 date month
 int amount
 date paid_on }
 USERS { bigint id PK
 string name
 string email UK
 string password
 string role }
 EVENTS { bigint id PK
 bigint user_id FK
 string description
 timestamp created_at }
```

## OOP walkthrough
- Encapsulation: app/Domain/Occupancy.php keeps occupancy private. admit() can increment it only when capacity permits.
- Abstraction: app/Domain/RentPlan.php defines monthly() and supplies select() to resolve a named plan.
- Inheritance: app/Domain/StandardRent.php and app/Domain/ScholarRent.php extend RentPlan. Each overrides monthly().
- Polymorphism: app/Domain/DormService.php calls monthly() on the selected plan during admit(). Its return value sets the agreed rent and first bill.
- Database invariants: each bill has a unique (stay_id, month). A null end means an active stay. A null paid_on means an unpaid bill. Parent foreign keys protect history.
