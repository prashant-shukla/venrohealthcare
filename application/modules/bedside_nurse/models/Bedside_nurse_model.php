<?php
class Bedside_nurse_model extends CI_Model
{


    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function insertNurse($data) {
        $this->db->insert('nurse', $data);
    }

    function getNurse() {
        $query = $this->db->get('nurse');
        return $query->result();
    }

    function getNurseById($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('nurse');
        return $query->row();
    }

    function updateNurse($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('nurse', $data);
    }

    function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('nurse');
    }

    function updateIonUser($username, $email, $password, $ion_user_id) {
        $uptade_ion_user = array(
            'username' => $username,
            'email' => $email,
            'password' => $password
        );
        $this->db->where('id', $ion_user_id);
        $this->db->update('users', $uptade_ion_user);
    }

    
    // get all bedside nurses
    function getBedsideNurses()
    {
        return $this->db->get('bedside_nurse_program')->result();
    }


    // insert bedside nurse
    function insertBedsideNurse($data)
    {
        return $this->db->insert('bedside_nurse_program', $data);
    }


    // get single nurse
    function getById($id)
    {
        return $this->db->where('id', $id)
            ->get('bedside_nurse_program')
            ->row();
    }


    // update nurse
    function updateBedsideNurse($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('bedside_nurse_program', $data);
    }


    // delete nurse
    function deleteBedsideNurse($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('bedside_nurse_program');
    }


    // assign nurse to patient
    function assignNurse($data)
    {
        return $this->db->insert('nurse_patient_assignment', $data);
    }


    // nurse billing
    function insertBilling($data)
    {
        return $this->db->insert('nurse_billing', $data);
    }


    // get nurse patient assignments
    // function getAssignments()
    // {

    //     $this->db->select('
    //         nurse_patient_assignment.*,
    //         bedside_nurse_program.name as nurse_name,
    //         patient.name as patient_name
    //     ');

    //     $this->db->from('nurse_patient_assignment');

    //     $this->db->join(
    //         'bedside_nurse_program',
    //         'bedside_nurse_program.id = nurse_patient_assignment.nurse_id'
    //     );

    //     $this->db->join(
    //         'patient',
    //         'patient.id = nurse_patient_assignment.patient_id'
    //     );

    //     return $this->db->get()->result();
    // }

    function getLicenseExpiry()
    {

        $this->db->where('license_expiry_date <=', date('Y-m-d', strtotime('+30 days')));

        return $this->db->get('bedside_nurse_program')->result();
    }
    function isNurseAssigned($nurse_id)
    {
        $this->db->where('nurse_id', $nurse_id);
        $this->db->where('status', 'Active');

        $query = $this->db->get('nurse_patient_assignment');

        return $query->num_rows();
    }

    function getAssignments($nurse_name = null)
    {

        $this->db->select('
        nurse_patient_assignment.*,
        bedside_nurse_program.name as nurse_name,
        patient.name as patient_name
     ');

        $this->db->from('nurse_patient_assignment');

        $this->db->join(
            'bedside_nurse_program',
            'bedside_nurse_program.id = nurse_patient_assignment.nurse_id'
        );

        $this->db->join(
            'patient',
            'patient.id = nurse_patient_assignment.patient_id'
        );

        // filter nurse
        if (!empty($nurse_name)) {
            $this->db->where('bedside_nurse_program.name', $nurse_name);
        }

        return $this->db->get()->result();
    }
}
