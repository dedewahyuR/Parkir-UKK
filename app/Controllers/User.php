<?php

namespace App\Controllers;
use App\Models\UserModel;

class User extends BaseController
{
    public function index()
    {
        $model = new UserModel();
        $data['user'] = $model->findAll();

        return view('user',$data);
    }

    public function simpan()
    {
        $model = new UserModel();

        $model->save([
            'namaLengkap'=>$this->request->getPost('namaLengkap'),
            'username'=>$this->request->getPost('username'),
            'password'=>$this->request->getPost('password'),
            'level'=>$this->request->getPost('level')
        ]);

        return redirect()->to('/user');
    }

    public function hapus($id)
    {
        $model = new UserModel();
        $model->delete($id);

        return redirect()->to('/user');
    }
}
