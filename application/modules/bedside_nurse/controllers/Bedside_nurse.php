<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bedside_nurse extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Bedside_nurse_model');
        if (!$this->ion_auth->in_group('admin')) {
            redirect('home/permission');
        }
    }

    // list
    function index()
    {

        $data['nurses'] = $this->Bedside_nurse_model->getBedsideNurses();

        $this->load->view('home/dashboard');
        $this->load->view('bedside_nurse/index', $data);
        $this->load->view('home/footer');
    }


    // add form
    function add()
    {

        $this->load->view('home/dashboard');
        $this->load->view('bedside_nurse/add');
        $this->load->view('home/footer');
    }


    function save()
    {

        $name  = $this->input->post('name', true);
        $email = $this->input->post('email', true);
        $password = $this->input->post('password', true);
 
        /* ===================== */
        /* MAIN DATA */
        /* ===================== */
        $available_days = $this->input->post('available_days');

        if (!empty($available_days)) {
            $available_days = implode(',', $available_days);
        } else {
            $available_days = null;
        }


        $file_name = $_FILES['profile_photo']['name'];
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
        $profile_photo = NULL;
        if ($this->upload->do_upload('profile_photo')) {
            $path = $this->upload->data();
            $profile_photo = "uploads/" . $path['file_name'];
        }



        /* ===================== */
        /* PROFILE PDF */
        /* ===================== */

        $config['upload_path']   = FCPATH . 'uploads/nurses/profile/';
        $config['allowed_types'] = 'pdf';
        $config['max_size']      = 2048;
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);
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


        $id = NULL;
        $data = array(
            'img_url' => $profile_photo,  
            'name' => $name,
            'email' => $email,
            'phone' => $this->input->post('phone', true),
            'address' => $this->input->post('residence', true),
            //newly added info
            'age'  => $this->input->post('age', true),
            'sex'  => $this->input->post('sex', true),
            'availability' => $this->input->post('availability', true),
            'available_days' => $available_days,
            'nurse_profile_pdf' => $nurse_profile_pdf,
            'nurse_license_pdf' => $nurse_license_pdf,
            'license_expiry_date' => $this->input->post('license_expiry_date', true),
            'status' => $this->input->post('status', true),
            'discontinued_reason' => $this->input->post('discontinued_reason', true),
        );

        

        $username = $this->input->post('name');
        if ($this->ion_auth->email_check($email)) {
            $this->session->set_flashdata('feedback', lang('this_email_address_is_already_registered'));
            redirect('bedside_nurse/add');
        } else {
            $dfg = 6;
            $this->ion_auth->register($username, $password, $email, $dfg);
            $ion_user_id = $this->db->get_where('users', array('email' => $email))->row()->id;
            $this->Bedside_nurse_model->insertNurse($data);
            $nurse_user_id = $this->db->get_where('nurse', array('email' => $email))->row()->id;
            $id_info = array('ion_user_id' => $ion_user_id);
            $this->Bedside_nurse_model->updateNurse($nurse_user_id, $id_info);
            $this->session->set_flashdata('feedback', lang('added'));
        }

        $this->session->set_flashdata('success', 'Bedside Nurse Added Successfully');
        redirect('bedside_nurse');
    }

    // edit
    function edit($id)
    {

        $data['bedside'] = $this->Bedside_nurse_model->getById($id);

        if (!$data['bedside']) {
            show_error('Record not found');
        }

        $this->load->view('home/dashboard');
        $this->load->view('bedside_nurse/edit', $data);
        $this->load->view('home/footer');
    }


    // update
    function update()
    {

        $id = $this->input->post('id', true);

        if (empty($id)) {
            show_error('Invalid Request: ID missing');
        }
        $available_days = $this->input->post('available_days');

        if (!empty($available_days)) {
            $available_days = implode(',', $available_days);
        } else {
            $available_days = null;
        }

        
        $data = array(

            'name' => $this->input->post('name', true),
            'email' => $this->input->post('email', true),

            'age' => $this->input->post('age', true),
            'sex' => $this->input->post('sex', true),
            'phone' => $this->input->post('phone', true),
            'residence' => $this->input->post('residence', true),

            'availability' => $this->input->post('availability', true),
            'available_days' => $available_days,

            'license_expiry_date' => $this->input->post('license_expiry_date', true),

            'status' => $this->input->post('status', true),
            'discontinued_reason' => $this->input->post('discontinued_reason', true),

            'updated_at' => date('Y-m-d H:i:s')

        );







        /* ========================= */
        /* PROFILE PDF UPLOAD */
        /* ========================= */

        $config['upload_path'] = FCPATH . 'uploads/nurses/profile/';
        $config['allowed_types'] = 'pdf';
        $config['max_size']      = 2048;
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        if (!empty($_FILES['nurse_profile_pdf']['name'])) {

            if ($this->upload->do_upload('nurse_profile_pdf')) {

                $file = $this->upload->data();

                $data['nurse_profile_pdf'] = 'uploads/nurses/profile/' . $file['file_name'];
            } else {

                echo $this->upload->display_errors();
                exit;
            }
        }


        /* ========================= */
        /* LICENSE PDF UPLOAD */
        /* ========================= */

        $config['upload_path'] = FCPATH . 'uploads/nurses/license/';

        $this->upload->initialize($config);

        if (!empty($_FILES['nurse_license_pdf']['name'])) {

            if ($this->upload->do_upload('nurse_license_pdf')) {

                $file = $this->upload->data();

                $data['nurse_license_pdf'] = 'uploads/nurses/license/' . $file['file_name'];
            } else {

                echo $this->upload->display_errors();
                exit;
            }
        }


        /* ========================= */
        /* UPDATE NURSE */
        /* ========================= */

        $this->Bedside_nurse_model->updateBedsideNurse($id, $data);


        /* ========================= */
        /* PASSWORD UPDATE (users table) */
        /* ========================= */

        $password = $this->input->post('password');

        if (!empty($password)) {

            $this->db->where('email', $this->input->post('email'));
            $this->db->update('users', [

                'password' => password_hash($password, PASSWORD_BCRYPT)

            ]);
        }


        $this->session->set_flashdata('success', 'Bedside Nurse Updated Successfully');

        redirect('bedside_nurse');
    }


    // delete
    function delete($id)
    {

        if (empty($id)) {
            show_error('Invalid Request: ID missing');
        }

        $record = $this->Bedside_nurse_model->getById($id);

        if (!$record) {
            show_error('Record not found');
        }

        $this->Bedside_nurse_model->deleteBedsideNurse($id);

        $this->session->set_flashdata('success', 'Bedside Nurse Deleted Successfully');

        redirect('bedside_nurse');
    }



    // assign nurse to patient
    function assign($id)
    {

        if (empty($id)) {
            show_error('Invalid Request');
        }

        $data['bedside'] = $this->Bedside_nurse_model->getById($id);

        if (!$data['bedside']) {
            show_error('Nurse not found');
        }

        $data['patients'] = $this->db->get('patient')->result();

        $this->load->view('home/dashboard');
        $this->load->view('bedside_nurse/assign', $data);
        $this->load->view('home/footer');
    }



    // save assignment




    // view assignments
    // function assignments()
    // {

    //     $data['assignments'] = $this->Bedside_nurse_model->getAssignments();

    //     $this->load->view('home/dashboard');
    //     $this->load->view('bedside_nurse/assignments', $data);
    //     $this->load->view('home/footer');
    // }

    function saveAssign()
    {

        $nurse_id = $this->input->post('nurse_id');


        /* check if nurse already assigned */

        $assigned = $this->Bedside_nurse_model->isNurseAssigned($nurse_id);

        if ($assigned > 0) {

            $this->session->set_flashdata(
                'error',
                'Nurse already assigned to another patient'
            );

            redirect('bedside_nurse');
        }


        /* assign nurse */

        $data = array(

            'nurse_id'   => $nurse_id,
            'patient_id' => $this->input->post('patient_id'),
            'start_date' => $this->input->post('start_date'),
            'end_date'   => $this->input->post('end_date'),
            'status'     => 'Active'

        );

        $this->db->insert('nurse_patient_assignment', $data);

        $this->session->set_flashdata('success', 'Nurse Assigned Successfully');

        redirect('bedside_nurse');
    }


    function editAssignment($id)
    {

        $assignment = $this->db
            ->where('id', $id)
            ->get('nurse_patient_assignment')
            ->row();

        if (!$assignment) {
            show_error('Assignment not found');
        }

        $data['assignment'] = $assignment;

        $data['patients'] = $this->db->get('patient')->result();

        $this->load->view('home/dashboard');
        $this->load->view('bedside_nurse/edit_assignment', $data);
        $this->load->view('home/footer');
    }

    function updateAssignment()
    {

        $id = $this->input->post('id');

        $data = array(

            'patient_id' => $this->input->post('patient_id'),
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'status' => $this->input->post('status')

        );

        $this->db->where('id', $id);
        $this->db->update('nurse_patient_assignment', $data);

        redirect('bedside_nurse/assignments');
    }

    function deleteAssignment($id)
    {

        $this->db->where('id', $id);
        $this->db->delete('nurse_patient_assignment');

        $this->session->set_flashdata('success', 'Assignment Deleted');

        redirect('bedside_nurse/assignments');
    }

    function assignments()
    {

        // nurse filter value
        $nurse_name = $this->input->get('nurse_name');

        // nurse dropdown list
        $data['nurses'] = $this->db
            ->select('id,name')
            ->from('bedside_nurse_program')
            ->get()
            ->result();

        // assignments with filter
        $data['assignments'] = $this->Bedside_nurse_model->getAssignments($nurse_name);

        // load views
        $this->load->view('home/dashboard');
        $this->load->view('bedside_nurse/assignments', $data);
        $this->load->view('home/footer');
    }
}
