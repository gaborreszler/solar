<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['value'];

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	public function powers()
	{
		return $this->hasMany('App\Power');
    }
}
