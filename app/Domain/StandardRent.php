<?php
namespace App\Domain;
final class StandardRent extends RentPlan {
 public function monthly(int $base): int { return $base; }
}
