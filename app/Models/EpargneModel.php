<?php
namespace App\Models;

use CodeIgniter\Model;
use Override;

class EpargneModel extends Model{
    protected $table ='epargne';
    protected $primaryKey='id';
    protected $allowedFields = ['utilisateur_id','nb'];

    public function enregister($id_user,$nb){
        $this->insert([
            'utilisateur_id'=>$id_user,
            'nb'=>$nb
        ]);
    }

    public function findbyiduser($id_user){

    }
}
?>