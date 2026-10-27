<?php
/**
 * NotesController.php
 *
 * @category   Controller
 * @package    App\Controllers
 * @author     Jeril
 * @created    27 August 2024
 * @purpose    Manages CRUD operations for notes, including insertion and retrieval.
 *             Interacts with the NotesModel to handle note data operations.
 *             Handles HTTP requests for inserting and retrieving notes.
 */
namespace App\Controllers;

use CodeIgniter\Model;
use CodeIgniter\RESTful\ResourceController;


class NotesController extends ResourceController {
    private $NotesModel;
    private $SprintModel;
    public function __construct() {
        $this->NotesModel = model( \App\Models\NotesModel::class );
        $this->SprintModel = model( \App\Models\SprintModel::class );
    }

    /**
     * Validate the data against the model's detail validation rules.
     * (Previously patched into the framework's ResourceController.)
     *
     * @return bool|array true when valid, otherwise the validation errors
     */
    private function hasInvalidInput(Model $model, $data)
    {
        $validation = service('validation');
        $validation->setRules($model->getDetailValidationRules(), $model->getValidationMessages());

        return $validation->run($data) ? true : $validation->getErrors();
    }

    public function insertNotes() {

        $jsonInput = $this->request->getJSON( true );
        $formData = isset( $jsonInput[ 'formData' ] ) ? $jsonInput[ 'formData' ] : null;
        $formData['r_user_id']=session()->get( 'employee_id' );
        $validationErrors = $this->hasInvalidInput($this->NotesModel, $formData);
        if ($validationErrors !== true) {
            return $this->response->setJSON(['errors' => $validationErrors]);
        }
        $data = $this->NotesModel->insertNotes( $formData );
        return $this->response->setJSON( ['success'=>$data] );

    }
    public function getNotes(){
        
        $jsonInput = $this->request->getJSON( true );
        $formData = isset( $jsonInput[ 'formData' ] ) ? $jsonInput[ 'formData' ] : null;
        $data = $this->NotesModel->getNotes($formData);
        if($formData['notestype']=='9'||$formData['notestype']=='10'||$formData['notestype']=='11')
        {
            $retrospectiveDate=$this->SprintModel->getSprintRetrospectiveDate($formData['sprintid']);
            
            
        }
     
        return $this->response->setJSON( [
            'success'=>"",
            'data'=>$data,
            'retrospectiveDate'=>$retrospectiveDate   
        ] );
    }   
}

?>