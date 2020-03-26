<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Power extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['date_id', 'time_id', 'value'];

	public function date()
	{
		return $this->belongsTo('App\Date');
    }

	public function time()
	{
		return $this->belongsTo('App\Time');
    }
}
