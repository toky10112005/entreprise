<?php
namespace App\Controllers;

use App\Models\EtudiantModel;

class EtudiantController extends BaseController
{
    public function index()
    {
        $model=new EtudiantModel();
       $listEtudiant=$model->list();

       return view('etudiant',['list'=>$listEtudiant]);
    }

    public function show($id)
    {
        $model=new EtudiantModel();
        $etudiant=$model->found($id);
        return view('etudiant_details', ['etudiant' => $etudiant]);
    }
}