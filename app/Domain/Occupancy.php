<?php
namespace App\Domain;
final class Occupancy {
 public function __construct(private int $capacity, private int $occupied) {}
 public function admit(): void {
    if ($this->occupied >= $this->capacity) throw new \DomainException('This room is full.');
    ++$this->occupied;
 }
 public function available(): int { return $this->capacity - $this->occupied; }
}
