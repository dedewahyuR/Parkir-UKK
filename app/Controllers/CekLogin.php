<?php

namespace App\Controllers;

use App\Models\UserModel;

class CekLogin extends BaseController
{
    protected $model;
    public function login()
    {
        return view('login');
    }

    public function validasi()
    {
        $this->model = new UserModel();
        $session = session();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->model->table('User')
            ->where('username', $username)
            ->where('password', $password)
            ->get()
            ->getRowArray();

        if ($user) {
            $session->set([
                'namaLengkap' => $user['namaLengkap'],
                'id' => $user['idUser'],
                'username' => $user['username'],
                'level' => $user['level'],
                'login' => true
            ]);

            return redirect()->to('/dashboard')->with("Selamat Datang" , "Selamat datang '$user[namaLengkap]'. Ini adalah halaman dashboard");
        }

        return redirect()->back()->with('error', 'Username / Password salah');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
