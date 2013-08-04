<?php
namespace Admin\Model;

class User
{
	public $id;
	public $email;
	public $password;
	public $firstname;
	public $lastname;
	public $active;

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->email = (array_key_exists('email', $data)) ? $data['email'] : null;
		$this->password = (array_key_exists('password', $data)) ? $data['password'] : null;
		$this->firstname = (array_key_exists('firstname', $data)) ? $data['firstname'] : null;
		$this->lastname = (array_key_exists('lastname', $data)) ? $data['lastname'] : null;
		$this->active = (array_key_exists('active', $data)) ? $data['active'] : null;
	}
}