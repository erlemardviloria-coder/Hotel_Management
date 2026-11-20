<?php
namespace App\Domain;
abstract class RentPlan {
 abstract public function monthly(int $base): int;
 public static function select(string $name): self {
    return match($name){'standard'=>new StandardRent,'scholar'=>new ScholarRent,default=>throw new \DomainException('Unknown rent plan.')};
 }
}
