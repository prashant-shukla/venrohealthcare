<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Nurse extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('nurse_model');
        if (!$this->ion_auth->in_group('admin')) {
            redirect('home/permission');
        }
    }

    public function index()
    {
        $data['nurses'] = $this->nurse_model->getNurse();
        $this->load->view('home/dashboard'); // just the header file
        $this->load->view('nurse', $data);
        $this->load->view('home/footer'); // just the header file
    }

    public function addNewView()
    {
        $this->load->view('home/dashboard'); // just the header file
        $this->load->view('add_new');
        $this->load->view('home/footer'); // just the header file
    }

    public function addNew()
    {
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $password = $this->input->post('password');
        $email = $this->input->post('email');
        $address = $this->input->post('address');
        $phone = $this->input->post('phone');
        $age = $this->input->post('age');
        $availability = $this->input->post('availability');
        $license_expiry_date = $this->input->post('license_expiry_date');


        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        // Validating Name Field
        $this->form_validation->set_rules('name', 'Name', 'trim|required|min_length[5]|max_length[100]|xss_clean');
        // Validating Password Field
        if (empty($id)) {
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[5]|max_length[100]|xss_clean');
        }
        // Validating Email Field
        $this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[5]|max_length[100]|xss_clean');
        // Validating Address Field   
        $this->form_validation->set_rules('address', 'Address', 'trim|required|min_length[5]|max_length[500]|xss_clean');
        // Validating Phone Field           
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required|min_length[5]|max_length[50]|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            if (!empty($id)) {
                $data = array();
                $data['nurse'] = $this->nurse_model->getNurseById($id);
                $this->load->view('home/dashboard'); // just the header file
                $this->load->view('add_new', $data);
                $this->load->view('home/footer'); // just the footer file
            } else {
                $data = array();
                $data['setval'] = 'setval';
                $this->load->view('home/dashboard'); // just the header file
                $this->load->view('add_new', $data);
                $this->load->view('home/footer'); // just the header file
            }
        } else {
            $file_name = $_FILES['img_url']['name'];
            $file_name_pieces = explode('_', $file_name);
            $new_file_name = '';
            $count = 1;
            foreach ($file_name_pieces as $piece) {
                if ($count !== 1) {
                    $piece = ucfirst($piece);
                }
                $new_file_name .= $piece;
                $count++;
            }
            $config = array(
                'file_name' => $new_file_name,
                'upload_path' => "./uploads/",
                'allowed_types' => "gif|jpg|png|jpeg|pdf",
                'overwrite' => False,
                'max_size' => "20480000", // Can be set to particular file size , here it is 2 MB(2048 Kb)
                'max_height' => "1768",
                'max_width' => "2024"
            );

            $this->load->library('Upload', $config);
            $this->upload->initialize($config);

            $img_url = NULL;
            if ($this->upload->do_upload('img_url')) {
                $path = $this->upload->data();
                $img_url = "uploads/" . $path['file_name'];
                $data = array();
                $data = array(
                    'img_url' => $img_url,
                    'name' => $name,
                    'email' => $email,
                    'address' => $address,
                    'phone' => $phone,
                    'age'  => $age
                );
            }



            /* ===================== */
            /* PROFILE PDF */
            /* ===================== */

            $config['upload_path']   = FCPATH . 'uploads/nurses/profile/';
            $config['allowed_types'] = 'pdf';
            $config['max_size']      = 2048;
            $config['encrypt_name']  = TRUE;

            $this->upload->initialize($config);
            $nurse_profile_pdf = NULL;
            if (!empty($_FILES['nurse_profile_pdf']['name'])) {
                if ($this->upload->do_upload('nurse_profile_pdf')) {
                    $file = $this->upload->data();
                    $nurse_profile_pdf = 'uploads/nurses/profile/' . $file['file_name'];
                }
            }




            /* ===================== */
            /* LICENSE PDF */
            /* ===================== */

            $config['upload_path'] = FCPATH . 'uploads/nurses/license/';
            $this->upload->initialize($config);

            $nurse_license_pdf = NULL;
            if (!empty($_FILES['nurse_license_pdf']['name'])) {

                if ($this->upload->do_upload('nurse_license_pdf')) {

                    $file = $this->upload->data();
                    $nurse_license_pdf = 'uploads/nurses/license/' . $file['file_name'];
                }
            }

            /* ===================== */
            /* MAIN DATA */
            /* ===================== */
            $available_days = $this->input->post('available_days');

            if (!empty($available_days)) {
                $available_days = implode(',', $available_days);
            } else {
                $available_days = null;
            }

            $data = array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                //newly added info
                'age'  => $age,
                'sex'  => $this->input->post('sex', true),

                'availability' => $availability,
                'available_days' => $available_days,
                'license_expiry_date' => $license_expiry_date,
                'status' => $this->input->post('status', true),
                'discontinued_reason' => $this->input->post('discontinued_reason', true),
            );
            if ($img_url) {
                $data['img_url'] = $img_url;
            }
            if ($nurse_profile_pdf) {
                $data['nurse_profile_pdf'] = $nurse_profile_pdf;
            }
            if ($nurse_license_pdf) {
                $data['nurse_license_pdf'] = $nurse_license_pdf;
            }

            $username = $this->input->post('name');
            if (empty($id)) {     // Adding New Nurse
                if ($this->ion_auth->email_check($email)) {
                    $this->session->set_flashdata('feedback', lang('this_email_address_is_already_registered'));
                    redirect('nurse/addNewView');
                } else {
                    $dfg = 6;
                    $this->ion_auth->register($username, $password, $email, $dfg);
                    $ion_user_id = $this->db->get_where('users', array('email' => $email))->row()->id;
                    $this->nurse_model->insertNurse($data);
                    $nurse_user_id = $this->db->get_where('nurse', array('email' => $email))->row()->id;
                    $id_info = array('ion_user_id' => $ion_user_id);
                    $this->nurse_model->updateNurse($nurse_user_id, $id_info);
                    $this->session->set_flashdata('feedback', lang('added'));
                }
            } else { // Updating Nurse
                $ion_user_id = $this->db->get_where('nurse', array('id' => $id))->row()->ion_user_id;
                if (empty($password)) {
                    $password = $this->db->get_where('users', array('id' => $ion_user_id))->row()->password;
                } else {
                    $password = $this->ion_auth_model->hash_password($password);
                }
                $this->nurse_model->updateIonUser($username, $email, $password, $ion_user_id);
                $this->nurse_model->updateNurse($id, $data);
                $this->session->set_flashdata('feedback', lang('updated'));
            }
            // Loading View
            redirect('nurse');
        }
    }

    function getNurse()
    {
        $data['nurses'] = $this->nurse_model->get_nurse();
        $this->load->view('nurse', $data);
    }

    function editNurse()
    {
        $data = array();
        $id = $this->input->get('id');
        $data['nurse'] = $this->nurse_model->getNurseById($id);
        $this->load->view('home/dashboard');
        // just the header file
        $this->load->view('add_new', $data);
        $this->load->view('home/footer'); // just the footer file
    }

    function editNurseByJason()
    {
        $id = $this->input->get('id');
        $data['nurse'] = $this->nurse_model->getNurseById($id);
        if ($data['nurse']) {
            $html = $this->load->view('edit_form', $data, true);
        } else {
            $html = '<p>' . lang('nurse_not_found') . '</p>';
        }
        echo json_encode(['html' => $html]);
    }



    function delete()
    {
        $data = array();
        $id = $this->input->get('id');
        $user_data = $this->db->get_where('nurse', array('id' => $id))->row();
        $path = $user_data->img_url;

        if (!empty($path)) {
            unlink($path);
        }
        $ion_user_id = $user_data->ion_user_id;
        $this->db->where('id', $ion_user_id);
        $this->db->delete('users');
        $this->nurse_model->delete($id);
        $this->session->set_flashdata('feedback', lang('deleted'));
        redirect('nurse');
    }







    public function assign($nurse_id)
    {

        // nurse detail
        $data['nurse'] = $this->nurse_model->getNurseById($nurse_id);

        // patients list
        $data['patients'] = $this->db->get('patient')->result();

        // 🔥 nurse assignment history
        $this->db->select('
        nurse_assignments.*,
        patient.name as patient_name
    ');

        $this->db->from('nurse_assignments');
        $this->db->join('patient', 'patient.id = nurse_assignments.patient_id', 'left');

        $this->db->where('nurse_assignments.nurse_id', $nurse_id);
        $this->db->order_by('nurse_assignments.id', 'DESC');

        $data['history'] = $this->db->get()->result();

        // views load
        $this->load->view('home/dashboard');
        $this->load->view('assign_nurse', $data);
        $this->load->view('home/footer');
    }




    // public function saveAssign()
    // {

    //     $nurse_id = $this->input->post('nurse_id');
    //     $start_date = $this->input->post('start_date');
    //     $end_date = $this->input->post('end_date');

    //     // Check Nurse Already Assigned
    //     $this->db->where('nurse_id', $nurse_id);
    //     $this->db->where("(
    //     (start_date <= '$end_date' AND end_date >= '$start_date')
    //     )");

    //     $query = $this->db->get('nurse_assignments');

    //     if ($query->num_rows() > 0) {

    //         $this->session->set_flashdata('error', 'Nurse already assigned for selected dates');
    //         redirect($_SERVER['HTTP_REFERER']);
    //     }

    //     // ✅ Save Assignment
    //     $data = array(

    //         'nurse_id' => $nurse_id,
    //         'patient_id' => $this->input->post('patient_id'),

    //         'start_date' => $start_date,
    //         'end_date' => $end_date,

    //         'per_day_fee' => $this->input->post('per_day_fee'),
    //         'per_week_fee' => $this->input->post('per_week_fee'),
    //         'per_month_fee' => $this->input->post('per_month_fee'),

    //         'payment_term' => $this->input->post('payment_term'),

    //         'transport_charge' => $this->input->post('transport_charge')
    //     );

    //     $this->db->insert('nurse_assignments', $data);

    //     // ✅ 🔥 IMPORTANT: assignment_id lo
    //     $assignment_id = $this->db->insert_id();

    //     // 🔥 billing auto calculate
    //     $payment_term = $this->input->post('payment_term');

    //     if ($payment_term == 'Day') {
    //         $billing_type = 'Day';
    //         $billing_amount = $this->input->post('per_day_fee');
    //     } elseif ($payment_term == 'Week') {
    //         $billing_type = 'Week';
    //         $billing_amount = $this->input->post('per_week_fee');
    //     } else {
    //         $billing_type = 'Month';
    //         $billing_amount = $this->input->post('per_month_fee');
    //     }

    //     // insert billing
    //     $billing = array(

    //         'assignment_id' => $assignment_id,
    //         'billing_type' => $billing_type,
    //         'billing_amount' => $billing_amount,

    //         'billing_start_date' => $start_date,

    //         'transport_charge' => $this->input->post('transport_charge'),
    //         'transport_start_date' => $start_date,
    //         'transport_end_date' => $end_date

    //     );

    //     $this->db->insert('nurse_billing', $billing);



    //     $this->session->set_flashdata('success', 'Nurse Assigned Successfully');

    //     redirect('nurse/assignments');
    // }


    public function saveAssign()
    {

        $nurse_id = $this->input->post('nurse_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        // Check Nurse Already Assigned
        $this->db->where('nurse_id', $nurse_id);
        $this->db->where("(
        (start_date <= '$end_date' AND end_date >= '$start_date')
      )");

        $query = $this->db->get('nurse_assignments');

        if ($query->num_rows() > 0) {

            $this->session->set_flashdata(
                'error',
                'Nurse already assigned for selected dates'
            );

            // same page redirect
            redirect('nurse/assign/' . $nurse_id);
        }

        // Save Assignment
        $data = array(

            'nurse_id' => $nurse_id,
            'patient_id' => $this->input->post('patient_id'),

            'start_date' => $start_date,
            'end_date' => $end_date,

            'per_day_fee' => $this->input->post('per_day_fee'),
            'per_week_fee' => $this->input->post('per_week_fee'),
            'per_month_fee' => $this->input->post('per_month_fee'),

            'payment_term' => $this->input->post('payment_term'),

            'transport_charge' => $this->input->post('transport_charge'),

            // Transport Dates
            'transport_start_date' => $this->input->post('Transport_start_date'),
            'transport_end_date' => $this->input->post('Transport_end_date')

        );

        $this->db->insert('nurse_assignments', $data);

        // assignment_id
        $assignment_id = $this->db->insert_id();

        // billing auto calculate
        $payment_term = $this->input->post('payment_term');

        if ($payment_term == 'Day') {

            $billing_type = 'Day';
            $billing_amount = $this->input->post('per_day_fee');
        } elseif ($payment_term == 'Week') {

            $billing_type = 'Week';
            $billing_amount = $this->input->post('per_week_fee');
        } else {

            $billing_type = 'Month';
            $billing_amount = $this->input->post('per_month_fee');
        }

        // insert billing
        $billing = array(

            'assignment_id' => $assignment_id,
            'billing_type' => $billing_type,
            'billing_amount' => $billing_amount,

            'billing_start_date' => $start_date,

            'transport_charge' => $this->input->post('transport_charge'),

            // Transport Dates
            'transport_start_date' => $this->input->post('Transport_start_date'),
            'transport_end_date' => $this->input->post('Transport_end_date')

        );

        $this->db->insert('nurse_billing', $billing);

        $this->session->set_flashdata(
            'success',
            'Nurse Assigned Successfully'
        );

        // same assign page redirect
        redirect('nurse/assign/' . $nurse_id);
    }







    public function assignments()
    {
        $nurse = $this->input->get('nurse');
        $search = $this->input->get('search');

        $data['nurses'] = $this->nurse_model->getNurse();

        $data['assignments'] = $this->nurse_model->getAssignments($nurse, $search);

        $this->load->view('home/dashboard');
        $this->load->view('assigned_nurses', $data);
        $this->load->view('home/footer');
    }

    public function deleteAssignments($id)
    {
        // assignment detail fetch karo
        $assignment = $this->db
            ->where('id', $id)
            ->get('nurse_assignments')
            ->row();

        // agar record mil gaya
        if ($assignment) {

            // delete record
            $this->db->where('id', $id);
            $this->db->delete('nurse_assignments');

            $this->session->set_flashdata(
                'success',
                'Assignment Deleted Successfully'
            );

            // same assign page par redirect
            redirect('nurse/assign/' . $assignment->nurse_id);
        } else {

            $this->session->set_flashdata(
                'error',
                'Assignment Not Found'
            );

            redirect('assigned_nurses');
        }
    }

    public function deleteAssignment($id)
    {

        $this->db->where('id', $id);
        $this->db->delete('nurse_assignments');

        $this->session->set_flashdata('success', 'Assignment Deleted Successfully');

        redirect('nurse/assignments');
    }


    public function editAssignment($id)
    {

        $data['assignment'] = $this->db
            ->where('id', $id)
            ->get('nurse_assignments')
            ->row();

        $data['nurses'] = $this->nurse_model->getNurse();
        $data['patients'] = $this->db->get('patient')->result();

        $this->load->view('home/dashboard');
        $this->load->view('edit_assignment', $data);
        $this->load->view('home/footer');
    }
    public function updateAssignment()
    {

        $id = $this->input->post('id');

        $data = array(

            'patient_id' => $this->input->post('patient_id'),

            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),

            'per_day_fee' => $this->input->post('per_day_fee'),
            'per_week_fee' => $this->input->post('per_week_fee'),
            'per_month_fee' => $this->input->post('per_month_fee'),

            'payment_term' => $this->input->post('payment_term'),

            'transport_charge' => $this->input->post('transport_charge')

        );

        $this->db->where('id', $id);
        $this->db->update('nurse_assignments', $data);

        $this->session->set_flashdata('success', 'Assignment Updated');

        redirect('nurse/assignments');
    }



// ================= PAYMENT PAGE =================

public function payment($nurse_id)
{

    $this->db->select('
        nurse_billing.*,
        nurse_assignments.nurse_id,
        nurse_assignments.start_date,
        nurse_assignments.end_date,
        patient.name as patient_name,
        nurse.name as nurse_name
    ');

    $this->db->from('nurse_billing');

    $this->db->join(
        'nurse_assignments',
        'nurse_assignments.id = nurse_billing.assignment_id'
    );

    $this->db->join(
        'patient',
        'patient.id = nurse_assignments.patient_id'
    );

    $this->db->join(
        'nurse',
        'nurse.id = nurse_assignments.nurse_id'
    );

    $this->db->where('nurse_assignments.nurse_id', $nurse_id);

    $data['records'] = $this->db->get()->result();

    // Payment History
    $this->db->where('nurse_id', $nurse_id);
    $data['payments'] = $this->db->get('nurse_payments')->result();

    $this->load->view('home/dashboard');
    $this->load->view('nurse_payment', $data);
    $this->load->view('home/footer');
}



// ================= SAVE PAYMENT =================

public function addPayment()
{

    $data = array(

        'nurse_id' => $this->input->post('nurse_id'),
        'amount' => $this->input->post('amount'),
        'payment_date' => $this->input->post('payment_date'),
        'note' => $this->input->post('note')

    );

    $this->db->insert('nurse_payments', $data);

    $this->session->set_flashdata(
        'success',
        'Payment Added Successfully'
    );

    redirect($_SERVER['HTTP_REFERER']);
}
}

/* End of file nurse.php */
/* Location: ./application/modules/nurse/controllers/nurse.php */
