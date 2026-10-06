<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Patient_model extends CI_model
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // The patient list and patient pickers show active patients; true = archived (soft deleted) only, null = all
    public $show_archived = false;

    private function _scope()
    {
        if ($this->show_archived !== null) {
            $this->db->where('patient.is_active', $this->show_archived ? 0 : 1);
        }
    }

    // Ordering for the patient list. 'assigned_nurse' sorts by the name of the current/upcoming
    // bedside nurse; patients without a nurse always come last.
    private function _order($order, $dir)
    {
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        if ($order === 'assigned_nurse') {
            $nurse = "(SELECT MIN(TRIM(nurse.name)) FROM nurse_assignments"
                . " JOIN nurse ON nurse.id = nurse_assignments.nurse_id"
                . " WHERE nurse_assignments.patient_id = patient.id AND nurse_assignments.is_active = 1"
                . " AND (nurse_assignments.end_date >= CURDATE() OR nurse_assignments.end_date IS NULL))";
            $this->db->order_by($nurse . ' IS NULL', 'ASC', false);
            $this->db->order_by($nurse, $dir, false);
            $this->db->order_by('patient.id', 'DESC');
        } elseif ($order === 'status' || $order === 'bedside') {
            // ENUM columns sort by declaration index unless cast to text
            $this->db->order_by('CAST(patient.' . $order . ' AS CHAR)', $dir, false);
            $this->db->order_by('patient.id', 'DESC');
        } elseif ($order === 'name') {
            $this->db->order_by('TRIM(patient.name)', $dir, false);
            $this->db->order_by('patient.id', 'DESC');
        } elseif ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
    }

    function insertPatient($data)
    {
        $this->db->insert('patient', $data);
    }

    // All patients, including archived ones (used to look up names on historical records)
    function getPatient()
    {
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('patient');
        return $query->result();
    }

    function countPatients($search = null, $bedside = null)
    {
        $this->_scope();
        if (!empty($bedside)) {
            $this->db->where('bedside', $bedside);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('id', $search);
            $this->db->or_like('name', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('address', $search);
            $this->db->group_end();
        }
        return $this->db->count_all_results('patient');
    }

    function getPatientWithoutSearch($order, $dir)
    {
        $this->_scope();
        $this->_order($order, $dir);
        $query = $this->db->get('patient');
        return $query->result();
    }

    function getPatientBySearch($search, $order, $dir)
    {
        $this->_scope();
        $this->_order($order, $dir);
        $this->db->group_start();
        $this->db->like('id', $search);
        $this->db->or_like('name', $search);
        $this->db->group_end();
        $query = $this->db->get('patient');
        return $query->result();
    }

    // function getPatientByLimit($limit, $start, $order, $dir) {
    //     if ($order != null) {
    //         $this->db->order_by($order, $dir);
    //     } else {
    //         $this->db->order_by('id', 'desc');
    //     }
    //     $this->db->limit($limit, $start);
    //     $query = $this->db->get('patient');
    //     return $query->result();
    // }

    function getPatientByLimitBySearch($limit, $start, $search, $order, $dir)
    {
        $this->_scope();
        $this->db->group_start();
        $this->db->like('id', $search);
        $this->db->or_like('name', $search);
        $this->db->or_like('phone', $search);
        $this->db->or_like('address', $search);
        $this->db->group_end();

        $this->_order($order, $dir);

        $this->db->limit($limit, $start);
        $query = $this->db->get('patient');
        return $query->result();
    }

    function getPatientById($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('patient');
        return $query->row();
    }

    function getPatientByIonUserId($id)
    {
        $this->db->where('ion_user_id', $id);
        $query = $this->db->get('patient');
        return $query->row();
    }

    function getPatientByEmail($email)
    {
        $this->db->where('email', $email);
        $query = $this->db->get('patient');
        return $query->row();
    }

    function updatePatient($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('patient', $data);
    }

    function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('patient');
    }

    function insertMedicalHistory($data)
    {
        $this->db->insert('medical_history', $data);
    }

    function getMedicalHistoryByPatientId($id)
    {
        $this->db->where('patient_id', $id);
        $query = $this->db->get('medical_history');
        return $query->result();
    }

    function getMedicalHistory()
    {
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('medical_history');
        return $query->result();
    }

    function getMedicalHistoryWithoutSearch($order, $dir)
    {
        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $query = $this->db->get('medical_history');
        return $query->result();
    }

    function getMedicalHistoryBySearch($search, $order, $dir)
    {
        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $this->db->like('id', $search);
        $this->db->or_like('patient_name', $search);
        $query = $this->db->get('medical_history');
        return $query->result();
    }

    function getMedicalHistoryByLimit($limit, $start, $order, $dir)
    {
        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $this->db->limit($limit, $start);
        $query = $this->db->get('medical_history');
        return $query->result();
    }

    function getMedicalHistoryByLimitBySearch($limit, $start, $search, $order, $dir)
    {

        $this->db->like('id', $search);

        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }

        $this->db->or_like('patient_name', $search);
        $this->db->or_like('patient_phone', $search);
        $this->db->or_like('patient_address', $search);

        $this->db->or_like('description', $search);

        $this->db->limit($limit, $start);
        $query = $this->db->get('medical_history');
        return $query->result();
    }

    function getMedicalHistoryById($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('medical_history');
        return $query->row();
    }

    function updateMedicalHistory($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('medical_history', $data);
    }

    function insertDiagnosticReport($data)
    {
        $this->db->insert('diagnostic_report', $data);
    }

    function updateDiagnosticReport($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('diagnostic_report', $data);
    }

    function getDiagnosticReport()
    {
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('diagnostic_report');
        return $query->result();
    }

    function getDiagnosticReportById($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('diagnostic_report');
        return $query->row();
    }

    function getDiagnosticReportByInvoiceId($id)
    {
        $this->db->where('invoice', $id);
        $query = $this->db->get('diagnostic_report');
        return $query->row();
    }

    function getDiagnosticReportByPatientId($id)
    {
        $this->db->where('patient', $id);
        $query = $this->db->get('diagnostic_report');
        return $query->result();
    }

    function insertPatientMaterial($data)
    {
        $this->db->insert('patient_material', $data);
    }

    function getPatientMaterial()
    {
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('patient_material');
        return $query->result();
    }

    function getPatientMaterialWithoutSearch($order, $dir)
    {
        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $query = $this->db->get('patient_material');
        return $query->result();
    }

    function getDocumentBySearch($search, $order, $dir)
    {
        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $this->db->like('id', $search);
        $this->db->or_like('patient_name', $search);
        $query = $this->db->get('patient_material');
        return $query->result();
    }

    function getDocumentByLimit($limit, $start, $order, $dir)
    {
        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }
        $this->db->limit($limit, $start);
        $query = $this->db->get('patient_material');
        return $query->result();
    }

    function getDocumentByLimitBySearch($limit, $start, $search, $order, $dir)
    {

        $this->db->like('id', $search);

        if ($order != null) {
            $this->db->order_by($order, $dir);
        } else {
            $this->db->order_by('id', 'desc');
        }

        $this->db->or_like('date_string', $search);

        $this->db->or_like('patient_name', $search);
        $this->db->or_like('patient_phone', $search);
        $this->db->or_like('patient_address', $search);

        $this->db->or_like('title', $search);

        $this->db->limit($limit, $start);
        $query = $this->db->get('patient_material');
        return $query->result();
    }

    function getPatientMaterialById($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('patient_material');
        return $query->row();
    }

    function getPatientMaterialByPatientId($id)
    {
        $this->db->where('patient', $id);
        $query = $this->db->get('patient_material');
        return $query->result();
    }

    function deletePatientMaterial($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('patient_material');
    }

    function deleteMedicalHistory($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('medical_history');
    }

    function updateIonUser($username, $email, $password, $ion_user_id)
    {
        $uptade_ion_user = array(
            'username' => $username,
            'email' => $email,
            'password' => $password
        );
        $this->db->where('id', $ion_user_id);
        $this->db->update('users', $uptade_ion_user);
    }

    function getDueBalanceByPatientId($patient)
    {
        $query = $this->db->get_where('payment', array('patient' => $patient))->result();
        $deposits = $this->db->get_where('patient_deposit', array('patient' => $patient))->result();
        $balance = array();
        $deposit_balance = array();
        foreach ($query as $gross) {
            $balance[] = $gross->gross_total;
        }
        $balance = array_sum($balance);


        foreach ($deposits as $deposit) {
            $deposit_balance[] = $deposit->deposited_amount;
        }
        $deposit_balance = array_sum($deposit_balance);



        $bill_balance = $balance;

        return $due_balance = $bill_balance - $deposit_balance;
    }

    function getPatientInfo($searchTerm)
    {
        if (!empty($searchTerm)) {
            $this->db->select('*');
            $this->_scope();
            $this->db->group_start();
            $this->db->like('name', $searchTerm);
            $this->db->or_like('id', $searchTerm);
            $this->db->group_end();
            $fetched_records = $this->db->get('patient');
            $users = $fetched_records->result_array();
        } else {
            $this->db->select('*');
            $this->_scope();
            $this->db->limit(10);
            $fetched_records = $this->db->get('patient');
            $users = $fetched_records->result_array();
        }
        // Initialize Array with fetched data
        $data = array();
        foreach ($users as $user) {
            $data[] = array("id" => $user['id'], "text" => $user['name'] . ' (' . lang('id') . ': ' . $user['id'] . ')');
        }
        return $data;
    }

    function getPatientinfoWithAddNewOption($searchTerm)
    {
        if (!empty($searchTerm)) {
            $this->db->select('*');
            $this->_scope();
            $this->db->group_start();
            $this->db->like('name', $searchTerm);
            $this->db->or_like('id', $searchTerm);
            $this->db->group_end();
            $fetched_records = $this->db->get('patient');
            $users = $fetched_records->result_array();
        } else {
            $this->db->select('*');
            $this->_scope();
            $this->db->limit(10);
            $fetched_records = $this->db->get('patient');
            $users = $fetched_records->result_array();
        }
        // Initialize Array with fetched data
        $data = array();
        $data[] = array("id" => 'add_new', "text" => lang('add_new'));
        foreach ($users as $user) {
            $data[] = array("id" => $user['id'], "text" => $user['name'] . ' (' . lang('id') . ': ' . $user['id'] . ')');
        }
        return $data;
    }



    function getPatientByLimit($limit, $start, $order, $dir, $bedside = null)
    {
        $this->_scope();

        if (!empty($bedside)) {
            $this->db->where('bedside', $bedside);
        }

        $this->_order($order, $dir);

        $this->db->limit($limit, $start);
        $query = $this->db->get('patient');

        return $query->result();
    }
}
