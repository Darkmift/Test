<?php 

namespace App\Models;

use illuminate\Database\Eloquent\Model;
use App\Controllers\Controller;

class Student extends Model {
	protected $table = 'students';
	protected $fillable =[
		'name',
		'phone',
		'email',
		
	];
}