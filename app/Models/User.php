<?php 

namespace App\Models;

use illuminate\Database\Eloquent\Model;

class User extends Model {
	protected $table = 'users';

	protected $fillable =[
		'name',
		'email',
		'phone',
		'role_id',
		'role',
		'password',
		'image',

		
	];

	public function setPassword($password)
	{
		$this->update([
			'password' => password_hash($password, PASSWORD_DEFAULT)
		]);
	}
}