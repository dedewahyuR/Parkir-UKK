<?php

namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'User';
    protected $primaryKey = 'idUser';

    protected $allowedFields = [
        'namaLengkap',
        'username',
        'password',
        'level'
    ];
}
