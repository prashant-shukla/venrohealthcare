<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Nurse_model extends CI_model
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    function insertNurse($data)
    {
        $this->db->insert('nurse', $data);
    }

    function getNurse()
    {
        $query = $this->db->get('nurse');
        return $query->result();
    }

    function getNurseById($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('nurse');
        return $query->row();
    }

    function updateNurse($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('nurse', $data);
    }

    function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('nurse');
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







    function assignNurse($data)
    {

        $this->db->insert('nurse_assignments', $data);
    }

    // function getAssignments()
    // {

    //     $this->db->select('nurse_assignments.*, nurse.name as nurse, patient.name as patient');

    //     $this->db->from('nurse_assignments');

    //     $this->db->join('nurse', 'nurse.id = nurse_assignments.nurse_id');

    //     $this->db->join('patient', 'patient.id = nurse_assignments.patient_id');

    //     $query = $this->db->get();

    //     return $query->result();
    // }


    public function getAssignments($nurse = null, $search = null)
    {

        $this->db->select('
nurse_assignments.*,
nurse.name as nurse_name,
patient.name as patient_name
');

        $this->db->from('nurse_assignments');

        $this->db->join('nurse', 'nurse.id = nurse_assignments.nurse_id');
        $this->db->join('patient', 'patient.id = nurse_assignments.patient_id');

        // Exclude soft-deleted (removed) assignments from the active list
        $this->db->where('nurse_assignments.is_active', 1);

        if (!empty($nurse)) {
            $this->db->where('nurse.name', $nurse);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nurse.name', $search);
            $this->db->or_like('patient.name', $search);
            $this->db->or_like('nurse_assignments.start_date', $search);
            $this->db->or_like('nurse_assignments.end_date', $search);
            $this->db->group_end();
        }

        $this->db->order_by('nurse_assignments.id', 'DESC');

        $query = $this->db->get();

        return $query->result();
    }


    /* ================= EFFECTIVE-DATED BILLING ================= */

    function getAssignmentDetail($assignment_id)
    {
        $this->db->select('nurse_assignments.*, nurse.name as nurse_name, patient.name as patient_name');
        $this->db->from('nurse_assignments');
        $this->db->join('nurse', 'nurse.id = nurse_assignments.nurse_id', 'left');
        $this->db->join('patient', 'patient.id = nurse_assignments.patient_id', 'left');
        $this->db->where('nurse_assignments.id', $assignment_id);
        return $this->db->get()->row();
    }

    function getBillingPeriods($assignment_id)
    {
        $this->db->where('assignment_id', $assignment_id);
        $this->db->order_by('effective_from', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('nurse_billing_periods')->result();
    }

    // The current (open / latest) billing period for an assignment
    function getCurrentBillingPeriod($assignment_id)
    {
        $this->db->where('assignment_id', $assignment_id);
        $this->db->order_by('effective_from', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('nurse_billing_periods')->row();
    }

    function insertBillingPeriod($data)
    {
        $this->db->insert('nurse_billing_periods', $data);
        return $this->db->insert_id();
    }

    function closeBillingPeriod($period_id, $effective_to)
    {
        $this->db->where('id', $period_id);
        $this->db->update('nurse_billing_periods', array('effective_to' => $effective_to));
    }

    function updateAssignmentEndDate($assignment_id, $end_date)
    {
        $this->db->where('id', $assignment_id);
        $this->db->update('nurse_assignments', array('end_date' => $end_date));
    }
}
