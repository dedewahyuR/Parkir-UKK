<?php

namespace App\Controllers;
use App\Models\TarifModel;

class Tarif extends BaseController
{
    public function index(): string
    {
        $model = new TarifModel();
        $data['tarif'] = $model->findAll();

        return view('tarif/index',$data);
    }

    public function simpan()
    {
        $model = new TarifModel();

        $model->save([
            'jenisKendaraan'=>$this->request->getPost('jenis'),
            'tarifPerJam'=>$this->request->getPost('tarif')
        ]);

        return redirect()->to('/tarif');
    }

    public function edit($id)
    {
        $model = new TarifModel();
        $data['tarif'] = $model->find($id);

        return view('tarif/edit',$data);
    }

    public function update($id)
    {
        $model = new TarifModel();

        $model->update($id,[
            'jenisKendaraan'=>$this->request->getPost('jenis'),
            'tarifPerJam'=>$this->request->getPost('tarif')
        ]);

        return redirect()->to('/tarif');
    }

    public function hapus($id)
    {
        $model = new TarifModel();
        $model->delete($id);

        return redirect()->to('/tarif');
    }
}
