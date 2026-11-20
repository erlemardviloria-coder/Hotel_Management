<?php
namespace App\Domain;
final class ScholarRent extends RentPlan {
 public function monthly(int $base): int { return intdiv($base*9+5,10); }
}
