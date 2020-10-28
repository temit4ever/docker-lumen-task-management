<?php


namespace App;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;


class Board extends Model {

  protected $fillable = ['user_id', 'name'];

  public function users() {
    return $this->belongsTo(User::class);
  }

}
