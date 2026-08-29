<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Feedback extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    // Only admin/staff may manage feedback (public form methods are exempt)
    private function _require_admin()
    {
        if (!$this->ion_auth->in_group(array('admin', 'Nurse', 'Doctor', 'Receptionist', 'Accountant'))) {
            redirect('home/permission');
        }
    }

    private function _current_user()
    {
        return $this->ion_auth->logged_in() ? $this->ion_auth->user()->row() : null;
    }

    // ---------------- ADMIN: list ----------------
    public function index()
    {
        $this->_require_admin();

        $this->db->select('feedback.*, nurse.name as nurse_name, patient.name as patient_name');
        $this->db->from('feedback');
        $this->db->join('nurse', 'nurse.id = feedback.nurse_id', 'left');
        $this->db->join('patient', 'patient.id = feedback.patient_id', 'left');
        $this->db->order_by('feedback.id', 'DESC');
        $data['feedback'] = $this->db->get()->result();

        $data['nurses'] = $this->db->get('nurse')->result();
        $data['patients'] = $this->db->get('patient')->result();

        $this->load->view('home/dashboard');
        $this->load->view('feedback_list', $data);
        $this->load->view('home/footer');
    }

    // ---------------- ADMIN: manual entry ----------------
    public function manualAdd()
    {
        $this->_require_admin();
        $u = $this->_current_user();

        $this->db->insert('feedback', array(
            'feedback_type'    => $this->input->post('feedback_type', true),
            'nurse_id'         => $this->input->post('nurse_id') ?: null,
            'patient_id'       => $this->input->post('patient_id') ?: null,
            'rating'           => $this->input->post('rating') ?: null,
            'comments'         => $this->input->post('comments', true),
            'customer_name'    => $this->input->post('customer_name', true),
            'source'           => 'Manual',
            'status'           => 'Submitted',
            'submitted_at'     => date('Y-m-d H:i:s'),
            'recorded_by'      => $u ? $u->id : null,
            'recorded_by_name' => $u ? $u->username : null,
        ));

        $this->session->set_flashdata('feedback_msg', 'Feedback recorded manually.');
        redirect('feedback');
    }

    // ---------------- ADMIN: generate a feedback request link ----------------
    public function generateLink()
    {
        $this->_require_admin();
        $u = $this->_current_user();

        $token = bin2hex(random_bytes(16));

        $this->db->insert('feedback', array(
            'feedback_type'    => $this->input->post('feedback_type', true),
            'nurse_id'         => $this->input->post('nurse_id') ?: null,
            'patient_id'       => $this->input->post('patient_id') ?: null,
            'source'           => 'Customer',
            'status'           => 'Pending',
            'token'            => $token,
            'recorded_by'      => $u ? $u->id : null,
            'recorded_by_name' => $u ? $u->username : null,
        ));

        $this->session->set_flashdata('feedback_link', base_url('feedback/form/' . $token));
        redirect('feedback');
    }

    // ---------------- PUBLIC: feedback form (via request link) ----------------
    public function form($token)
    {
        $row = $this->db->get_where('feedback', array('token' => $token))->row();

        if (empty($row)) {
            $this->load->view('feedback_public', array('error' => 'This feedback link is invalid.'));
            return;
        }
        if ($row->status == 'Submitted') {
            $this->load->view('feedback_public', array('done' => true));
            return;
        }

        // Provide context (nurse / patient names) for the form
        $row->nurse_name = $row->nurse_id ? $this->db->get_where('nurse', array('id' => $row->nurse_id))->row('name') : null;
        $this->load->view('feedback_public', array('row' => $row));
    }

    // ---------------- PUBLIC: submit feedback ----------------
    public function submit()
    {
        $token = $this->input->post('token', true);
        $row = $this->db->get_where('feedback', array('token' => $token))->row();

        if (empty($row) || $row->status == 'Submitted') {
            $this->load->view('feedback_public', array('done' => true));
            return;
        }

        $this->db->where('id', $row->id);
        $this->db->update('feedback', array(
            'rating'        => $this->input->post('rating') ?: null,
            'comments'      => $this->input->post('comments', true),
            'customer_name' => $this->input->post('customer_name', true),
            'status'        => 'Submitted',
            'submitted_at'  => date('Y-m-d H:i:s'),
        ));

        $this->load->view('feedback_public', array('thanks' => true));
    }
}
