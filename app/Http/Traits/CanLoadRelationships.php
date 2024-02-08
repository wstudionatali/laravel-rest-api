<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait CanLoadRelationships
{
  /* $for may be instanceof Model if 1. $for = $event->latest()  or not
    instanceof Model if 2. $for = Event::query();
    1. variant with collection will requier to apply  $for->load($relation)
    2. variant with query model will require to apply  $q->with($relation)
  */
  public function loadRelationships(
    Model|QueryBuilder|EloquentBuilder|HasMany $for,
    ?array $relations = null
  ): Model|QueryBuilder|EloquentBuilder|HasMany {
    $relations = $relations ?? $this->relations ?? [];

    foreach ($relations as $relation) {
      $for->when(
        $this->shouldIncludeRelation($relation),
        fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation)
      );
    }

    return $for;
  }

  protected function shouldIncludeRelation(string $relation): bool
  {
    $include = request()->query('include');

    if (!$include) {
      return false;
    }

    $relations = array_map('trim', explode(',', $include));

    return in_array($relation, $relations);
  }
}