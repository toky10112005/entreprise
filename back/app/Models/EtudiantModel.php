<?php
    namespace App\Models;

    use CodeIgniter\Model;

    class EtudiantModel extends Model
    {
        protected $list=[
            ['id'=>1,'nom'=>'John','prenom'=>'Doe','age'=>20],
            ['id'=>2,'nom'=>'Jane','prenom'=>'Smith','age'=>22],
            ['id'=>3,'nom'=>'Alice','prenom'=>'Johnson','age'=>19],
        ];

        public function list()
        {
            return $this->list;
        }

        public function found($id){
            if($id<1 || $id>count($this->list)){
                return null;
            }
            return $this->list[$id-1];
        }
    }