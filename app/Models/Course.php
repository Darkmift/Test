<?php 

namespace App\Models;

use illuminate\Database\Eloquent\Model;

class Course extends Model {
	protected $table = 'courses';

	protected $fillable =[
		'name',
		'description',
		'image',
	];
}