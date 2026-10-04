<?php

namespace App\Models\Backlog;

use App\Models\BaseModel;
use CodeIgniter\Model;

class DocumentModel extends BaseModel
{
    /**
    * @author Murugadass
    * @return int
    * @param array document details
    */

    public function insertDocument($documentDetails):int
    {
        $sql = "INSERT INTO 
                    orvexa_document(
                        r_module_id,
                        r_document_type_id,
                        reference_id,
                        document_name,
                        document_path,
                        r_user_id_created,
                        created_date,
                        is_deleted
                    )
                VALUES (
                    :r_module_id:,
                    :r_document_type_id:,
                    :reference_id:,
                    :document_name:,
                    :document_path:,
                    :r_user_id_created:,
                    NOW(),
                    'N'
                )
        ";
        $query = $this->db->query($sql, [
            'r_module_id' => $documentDetails['r_module_id'],
            'r_document_type_id' => $documentDetails['r_document_type_id'],
            'reference_id' => $documentDetails['r_reference_id'],
            'document_name' => $documentDetails['document_name'],
            'document_path' => $documentDetails['document_path'],
            'r_user_id_created' => $documentDetails['r_user_id_created']
        ]);
        if($query){
            return 1;
        }
        return 0;
    }

    /**
     * @author Murugadass
     * @return array
     * @param int reference id & int module id 
     * retrives the information about all the documents attached for a backlog item
     */
    public function getDocumentDetails($pblId, $moduleId, $docId = null): array
    {
        $sql = "SELECT
                    d.document_id,
                    d.document_name,
                    d.document_path,
                    dt.document_type,
                    d.created_date,
                    us.first_name,
                    us.last_name
                    FROM orvexa_document d
                    INNER JOIN orvexa_document_type dt ON dt.document_type_id = d.r_document_type_id
                    INNER JOIN orvexa_user us ON us.external_employee_id = d.r_user_id_created ";

                        if ($docId) {
                            $sql .= "WHERE document_id = :docId:
                    AND d.is_deleted = 'N';";

                            $query = $this->query($sql, [
                                'docId' => $docId
                            ]);
                        } else {
                            $sql .= "WHERE reference_id = :rid:
                    AND r_module_id = :module:
                    AND d.is_deleted = 'N'";

            $query = $this->query($sql, [
                'rid' => $pblId,
                'module' => $moduleId
            ]);
        }


        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return [];
    }


    /**
     * @author Murugadass
     * @param $id
     * @return array
     */
    public function getDocumentType($id): array
    {
        $sql = "SELECT 
                    document_type 
                FROM 
                    orvexa_document_type 
                WHERE 
                    document_type_id =:id:";
        $res = $this->query($sql, [
            'id' => $id
        ]);
        if ($res) {
            return $res->getResultArray();
        }
        return [];
    }

}