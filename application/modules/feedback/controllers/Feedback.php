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

    // Only office staff may manage feedback, so nurses/doctors cannot record feedback about themselves
    // (the public form methods are exempt)
    private function _require_admin()
    {
        if (!$this->ion_auth->in_group(array('admin', 'Receptionist'))) {
            redirect('home/permission');
        }
    }

    private function _current_user()
    {
        return $this->ion_auth->logged_in() ? $this->ion_auth->user()->row() : null;
    }

    /**
     * The assignment a feedback entry belongs to: the posted one if it matches the
     * nurse/patient, otherwise the latest assignment of that nurse to that patient.
     */
    private function _resolve_assignment_id($nurse_id, $patient_id, $assignment_id)
    {
        if (!empty($assignment_id)) {
            $this->db->where('id', $assignment_id);
            if ($nurse_id) {
                $this->db->where('nurse_id', $nurse_id);
            }
            if ($patient_id) {
                $this->db->where('patient_id', $patient_id);
            }
            $row = $this->db->get('nurse_assignments')->row();
            if ($row) {
                return $row->id;
            }
        }
        if (empty($nurse_id) || empty($patient_id)) {
            return null;
        }
        $this->db->where('nurse_id', $nurse_id);
        $this->db->where('patient_id', $patient_id);
        $this->db->order_by('is_active', 'DESC');
        $this->db->order_by('start_date', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get('nurse_assignments')->row();
        return $row ? $row->id : null;
    }

    private function _posted_context()
    {
        $type = $this->input->post('feedback_type') === 'Business' ? 'Business' : 'Nurse';
        $nurse_id = $this->input->post('nurse_id') ?: null;
        $patient_id = $this->input->post('patient_id') ?: null;
        return array(
            'feedback_type' => $type,
            'nurse_id'      => $nurse_id,
            'patient_id'    => $patient_id,
            'assignment_id' => $this->_resolve_assignment_id($nurse_id, $patient_id, $this->input->post('assignment_id')),
        );
    }

    // ---------------- ADMIN: list ----------------
    public function index()
    {
        $this->_require_admin();

        $this->db->select('feedback.*, nurse.name as nurse_name, patient.name as patient_name, nurse_assignments.start_date as assignment_start, nurse_assignments.end_date as assignment_end');
        $this->db->from('feedback');
        $this->db->join('nurse', 'nurse.id = feedback.nurse_id', 'left');
        $this->db->join('patient', 'patient.id = feedback.patient_id', 'left');
        $this->db->join('nurse_assignments', 'nurse_assignments.id = feedback.assignment_id', 'left');
        $this->db->order_by('feedback.id', 'DESC');
        $data['feedback'] = $this->db->get()->result();

        $data['nurses'] = $this->db->where('is_active', 1)->order_by('name', 'ASC')->get('nurse')->result();
        $data['patients'] = $this->db->where('is_active', 1)->order_by('name', 'ASC')->get('patient')->result();

        // Pre-selected nurse / patient / assignment when opened from an assignment or patient record
        $data['prefill'] = array(
            'nurse_id'      => $this->input->get('nurse_id'),
            'patient_id'    => $this->input->get('patient_id'),
            'assignment_id' => $this->input->get('assignment_id'),
        );

        $this->load->view('home/dashboard');
        $this->load->view('feedback_list', $data);
        $this->load->view('home/footer');
    }

    // ---------------- ADMIN: manual entry ----------------
    public function manualAdd()
    {
        $this->_require_admin();
        $u = $this->_current_user();

        $comments = trim((string) $this->input->post('comments', true));
        if ($comments === '') {
            $this->session->set_flashdata('feedback_msg', 'Please enter the feedback comments.');
            redirect('feedback');
            return;
        }

        $this->db->insert('feedback', array_merge($this->_posted_context(), array(
            'rating'           => $this->input->post('rating') ?: null,
            'comments'         => $comments,
            'customer_name'    => $this->input->post('customer_name', true),
            'source'           => 'Manual',
            'status'           => 'Submitted',
            'submitted_at'     => date('Y-m-d H:i:s'),
            'recorded_by'      => $u ? $u->id : null,
            'recorded_by_name' => $u ? $u->username : null,
        )));

        $this->session->set_flashdata('feedback_msg', 'Feedback recorded manually.');
        redirect('feedback');
    }

    // ---------------- ADMIN: generate a feedback request link ----------------
    public function generateLink()
    {
        $this->_require_admin();
        $u = $this->_current_user();

        $token = bin2hex(random_bytes(16));

        $this->db->insert('feedback', array_merge($this->_posted_context(), array(
            'source'           => 'Customer',
            'status'           => 'Pending',
            'token'            => $token,
            'recorded_by'      => $u ? $u->id : null,
            'recorded_by_name' => $u ? $u->username : null,
        )));

        $this->session->set_flashdata('feedback_link', base_url('feedback/form/' . $token));
        redirect('feedback');
    }

    // ---------------- PUBLIC: feedback form (via request link) ----------------
    public function form($token = null)
    {
        $row = $token ? $this->db->get_where('feedback', array('token' => $token))->row() : null;

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
        $row = $token ? $this->db->get_where('feedback', array('token' => $token))->row() : null;

        if (empty($row)) {
            $this->load->view('feedback_public', array('error' => 'This feedback link is invalid.'));
            return;
        }
        if ($row->status == 'Submitted') {
            $this->load->view('feedback_public', array('done' => true));
            return;
        }

        $rating = (int) $this->input->post('rating');
        $this->db->where('id', $row->id);
        $this->db->update('feedback', array(
            'rating'        => ($rating >= 1 && $rating <= 5) ? $rating : null,
            'comments'      => $this->input->post('comments', true),
            'customer_name' => $this->input->post('customer_name', true),
            'status'        => 'Submitted',
            'submitted_at'  => date('Y-m-d H:i:s'),
        ));

        $this->load->view('feedback_public', array('thanks' => true));
    }
}
