<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Nurse extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('nurse_model');
        $this->load->helper('billing');
        $this->load->helper('audit');
        if (!$this->ion_auth->in_group('admin')) {
            redirect('home/permission');
        }
    }

    // Current logged-in user (for audit fields)
    private function _current_user()
    {
        $u = $this->ion_auth->user()->row();
        return $u;
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

            // Clean, dedicated config for PDF uploads (no leftover image-dimension keys).
            $pdf_config = array(
                'allowed_types' => 'pdf',
                'max_size'      => 10240, // 10 MB - certificates can be large scans
                'encrypt_name'  => TRUE,
                'overwrite'     => FALSE,
            );

            $upload_errors = array();

            $pdf_config['upload_path'] = FCPATH . 'uploads/nurses/profile/';
            $this->upload->initialize($pdf_config);
            $nurse_profile_pdf = NULL;
            if (!empty($_FILES['nurse_profile_pdf']['name'])) {
                if ($this->upload->do_upload('nurse_profile_pdf')) {
                    $file = $this->upload->data();
                    $nurse_profile_pdf = 'uploads/nurses/profile/' . $file['file_name'];
                } else {
                    $upload_errors[] = 'Profile PDF: ' . strip_tags($this->upload->display_errors('', ''));
                }
            }




            /* ===================== */
            /* LICENSE PDF */
            /* ===================== */

            $pdf_config['upload_path'] = FCPATH . 'uploads/nurses/license/';
            $this->upload->initialize($pdf_config);

            $nurse_license_pdf = NULL;
            if (!empty($_FILES['nurse_license_pdf']['name'])) {

                if ($this->upload->do_upload('nurse_license_pdf')) {

                    $file = $this->upload->data();
                    $nurse_license_pdf = 'uploads/nurses/license/' . $file['file_name'];
                } else {
                    $upload_errors[] = 'License PDF: ' . strip_tags($this->upload->display_errors('', ''));
                }
            }

            // (Upload errors are surfaced after the save, before redirect, so they
            //  are not overwritten by the "added/updated" message below.)

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
                // Capture previous state for audit (e.g. status changes)
                $old_nurse = $this->nurse_model->getNurseById($id);

                $ion_user_id = $this->db->get_where('nurse', array('id' => $id))->row()->ion_user_id;
                if (empty($password)) {
                    $password = $this->db->get_where('users', array('id' => $ion_user_id))->row()->password;
                } else {
                    $password = $this->ion_auth_model->hash_password($password);
                }
                $this->nurse_model->updateIonUser($username, $email, $password, $ion_user_id);
                $this->nurse_model->updateNurse($id, $data);

                // Log status change to the audit trail (status history)
                $new_status = $this->input->post('status', true);
                if ($old_nurse && $new_status && $old_nurse->status != $new_status) {
                    audit_log(array(
                        'module'      => 'nurse',
                        'record_type' => 'nurse',
                        'record_id'   => $id,
                        'action'      => 'Status changed',
                        'field'       => 'status',
                        'old_value'   => $old_nurse->status,
                        'new_value'   => $new_status,
                        'reason'      => $this->input->post('discontinued_reason', true),
                    ));
                }

                $this->session->set_flashdata('feedback', lang('updated'));
            }

            // Surface any file-upload problems (e.g. non-PDF) so they are not
            // silently dropped. This overrides the added/updated message because
            // a failed certificate upload is more important to know about.
            if (!empty($upload_errors)) {
                $this->session->set_flashdata('feedback', 'Saved, but file NOT uploaded — ' . implode(' | ', $upload_errors));
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



    // Soft delete / deactivate a nurse. Historical records are preserved.
    function delete()
    {
        $id = $this->input->get('id');
        $reason = $this->input->get('reason');

        $user_data = $this->db->get_where('nurse', array('id' => $id))->row();
        if (empty($user_data)) {
            redirect('nurse');
            return;
        }

        // Deactivate rather than physically delete, so the nurse's history
        // (assignments, payments, certificates, status history) remains available.
        $this->nurse_model->updateNurse($id, array(
            'is_active'  => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
            'status'     => 'Discontinued',
        ));

        // Deactivate the login account (do not delete it).
        if (!empty($user_data->ion_user_id)) {
            $this->ion_auth->deactivate($user_data->ion_user_id);
        }

        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse',
            'record_id'   => $id,
            'action'      => 'Deactivated (soft delete)',
            'reason'      => $reason,
        ));

        $this->session->set_flashdata('feedback', 'Nurse deactivated. Historical records preserved.');
        redirect('nurse');
    }

    // Restore a soft-deleted / deactivated nurse
    function restore()
    {
        $id = $this->input->get('id');
        $user_data = $this->db->get_where('nurse', array('id' => $id))->row();
        if (empty($user_data)) {
            redirect('nurse');
            return;
        }

        $this->nurse_model->updateNurse($id, array(
            'is_active'  => 1,
            'deleted_at' => null,
            'status'     => 'Available',
        ));

        if (!empty($user_data->ion_user_id)) {
            $this->ion_auth->activate($user_data->ion_user_id);
        }

        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse',
            'record_id'   => $id,
            'action'      => 'Restored (reactivated)',
        ));

        $this->session->set_flashdata('feedback', 'Nurse restored.');
        redirect('nurse');
    }

    // Consolidated nurse historical record (remains available even when discontinued)
    function record($nurse_id)
    {
        $data['nurse'] = $this->nurse_model->getNurseById($nurse_id);
        if (empty($data['nurse'])) {
            show_404();
            return;
        }

        // Previous & current patient assignments (with the patient's associated doctor)
        $this->db->select('nurse_assignments.*, patient.name as patient_name, doctor.name as doctor_name');
        $this->db->from('nurse_assignments');
        $this->db->join('patient', 'patient.id = nurse_assignments.patient_id', 'left');
        $this->db->join('doctor', 'doctor.id = patient.doctor', 'left');
        $this->db->where('nurse_assignments.nurse_id', $nurse_id);
        $this->db->order_by('nurse_assignments.start_date', 'DESC');
        $data['assignments'] = $this->db->get()->result();

        // Professional fee / payment records
        $this->db->where('nurse_id', $nurse_id);
        $this->db->order_by('payment_date', 'DESC');
        $data['payments'] = $this->db->get('nurse_payments')->result();

        // Status history + other audit activity for this nurse
        $data['audit'] = audit_get('nurse', $nurse_id);

        $this->load->view('home/dashboard');
        $this->load->view('nurse_record', $data);
        $this->load->view('home/footer');
    }







    public function assign($nurse_id)
    {

        // nurse detail
        $data['nurse'] = $this->nurse_model->getNurseById($nurse_id);

        // patients list
        $data['patients'] = $this->db->get('patient')->result();

        // 🔥 nurse assignment history (with the patient's associated doctor)
        $this->db->select('
        nurse_assignments.*,
        patient.name as patient_name,
        patient.doctor as doctor_id,
        doctor.name as doctor_name
    ');

        $this->db->from('nurse_assignments');
        $this->db->join('patient', 'patient.id = nurse_assignments.patient_id', 'left');
        $this->db->join('doctor', 'doctor.id = patient.doctor', 'left');

        $this->db->where('nurse_assignments.nurse_id', $nurse_id);
        // Only show active (not removed) assignments here; removed ones remain
        // visible in the nurse's full historical record.
        $this->db->where('nurse_assignments.is_active', 1);
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
        $patient_id = $this->input->post('patient_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        // Basic validation
        if (empty($nurse_id) || empty($patient_id) || empty($start_date) || empty($end_date)) {
            $this->session->set_flashdata('error', 'Please select a patient and provide start and end dates.');
            redirect('nurse/assign/' . $nurse_id);
            return;
        }
        if ($end_date < $start_date) {
            $this->session->set_flashdata('error', 'End date cannot be before start date.');
            redirect('nurse/assign/' . $nurse_id);
            return;
        }

        // Check the nurse isn't already assigned for overlapping dates.
        // Use parameter-bound conditions (no string interpolation) to avoid SQL injection.
        $this->db->where('nurse_id', $nurse_id);
        $this->db->where('is_active', 1);
        $this->db->where('start_date <=', $end_date);
        $this->db->where('end_date >=', $start_date);
        $query = $this->db->get('nurse_assignments');

        if ($query->num_rows() > 0) {

            $this->session->set_flashdata(
                'error',
                'Nurse already assigned for selected dates'
            );

            // same page redirect
            redirect('nurse/assign/' . $nurse_id);
            return;
        }

        $assignment_role = $this->input->post('assignment_role');

        // Enforce a single Active (Primary) nurse per patient at a time:
        // if this new assignment is Primary, demote any existing active Primary
        // for the same patient to Additional.
        if ($assignment_role === 'Primary') {
            $this->db->where('patient_id', $patient_id);
            $this->db->where('is_active', 1);
            $this->db->where('assignment_role', 'Primary');
            $this->db->update('nurse_assignments', array('assignment_role' => 'Additional'));
        }

        // Save Assignment
        $data = array(

            'nurse_id' => $nurse_id,
            'patient_id' => $patient_id,
            'assignment_role' => $assignment_role,

            'start_date' => $start_date,
            'end_date' => $end_date,

            'per_day_fee' => $this->input->post('per_day_fee'),
            'per_week_fee' => $this->input->post('per_week_fee'),
            'per_month_fee' => $this->input->post('per_month_fee'),

            'payment_term' => $this->input->post('payment_term'),

            'transport_charge' => $this->input->post('transport_charge'),

            // Transport / Commute frequency (replaces separate start/end dates)
            'transport_frequency' => $this->input->post('transport_frequency'),
            'transport_note' => $this->input->post('transport_note')

        );

        $this->db->insert('nurse_assignments', $data);

        // assignment_id
        $assignment_id = $this->db->insert_id();

        // billing auto calculate
        $payment_term = $this->input->post('payment_term');

        // Note: nurse_billing.billing_type is ENUM('Daily','Weekly','Monthly'),
        // so store the matching enum value (not 'Day'/'Week'/'Month').
        if ($payment_term == 'Day') {

            $billing_type = 'Daily';
            $billing_amount = $this->input->post('per_day_fee');
        } elseif ($payment_term == 'Week') {

            $billing_type = 'Weekly';
            $billing_amount = $this->input->post('per_week_fee');
        } else {

            $billing_type = 'Monthly';
            $billing_amount = $this->input->post('per_month_fee');
        }

        // insert billing
        $billing = array(

            'assignment_id' => $assignment_id,
            'billing_type' => $billing_type,
            'billing_amount' => $billing_amount,

            'billing_start_date' => $start_date,

            'transport_charge' => $this->input->post('transport_charge'),

            // Transport / Commute frequency (replaces separate start/end dates)
            'transport_frequency' => $this->input->post('transport_frequency'),
            'transport_note' => $this->input->post('transport_note')

        );

        $this->db->insert('nurse_billing', $billing);

        // Initial effective-dated billing period for this placement
        $freq_map = array('Day' => 'Daily', 'Week' => 'Weekly', 'Month' => 'Monthly');
        $u = $this->_current_user();
        $this->nurse_model->insertBillingPeriod(array(
            'assignment_id'   => $assignment_id,
            'nurse_id'        => $nurse_id,
            'patient_id'      => $this->input->post('patient_id'),
            'billing_frequency' => isset($freq_map[$payment_term]) ? $freq_map[$payment_term] : 'Daily',
            'rate'            => $billing_amount,
            'effective_from'  => $start_date,
            'effective_to'    => $end_date,
            'note'            => 'Initial billing arrangement',
            'created_by'      => $u ? $u->id : null,
            'created_by_name' => $u ? $u->username : null,
        ));

        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse_assignment',
            'record_id'   => $assignment_id,
            'action'      => 'Nurse assigned to patient',
            'new_value'   => 'Patient #' . $this->input->post('patient_id') . ' (' . $this->input->post('assignment_role') . ')',
        ));

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

            // Soft delete (deactivate) - preserve the historical record
            $this->db->where('id', $id);
            $this->db->update('nurse_assignments', array(
                'is_active'  => 0,
                'deleted_at' => date('Y-m-d H:i:s'),
            ));

            audit_log(array(
                'module'      => 'nurse',
                'record_type' => 'nurse_assignment',
                'record_id'   => $id,
                'action'      => 'Assignment removed (soft delete)',
                'reason'      => $this->input->get('reason'),
            ));

            $this->session->set_flashdata(
                'success',
                'Assignment removed. Historical record preserved.'
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
        // Soft delete (deactivate) - preserve the historical record
        $this->db->where('id', $id);
        $this->db->update('nurse_assignments', array(
            'is_active'  => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
        ));

        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse_assignment',
            'record_id'   => $id,
            'action'      => 'Assignment removed (soft delete)',
            'reason'      => $this->input->get('reason'),
        ));

        $this->session->set_flashdata('success', 'Assignment removed. Historical record preserved.');

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
            'assignment_role' => $this->input->post('assignment_role'),

            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),

            'per_day_fee' => $this->input->post('per_day_fee'),
            'per_week_fee' => $this->input->post('per_week_fee'),
            'per_month_fee' => $this->input->post('per_month_fee'),

            'payment_term' => $this->input->post('payment_term'),

            'transport_charge' => $this->input->post('transport_charge'),

            // Transport / Commute frequency (replaces separate start/end dates)
            'transport_frequency' => $this->input->post('transport_frequency'),
            'transport_note' => $this->input->post('transport_note')

        );

        $this->db->where('id', $id);
        $this->db->update('nurse_assignments', $data);

        $this->session->set_flashdata('success', 'Assignment Updated');

        redirect('nurse/assignments');
    }



// ================= BILLING PLAN & EFFECTIVE-DATED HISTORY =================

public function billing($assignment_id)
{
    $data['assignment'] = $this->nurse_model->getAssignmentDetail($assignment_id);

    if (empty($data['assignment'])) {
        show_404();
        return;
    }

    $data['periods'] = $this->nurse_model->getBillingPeriods($assignment_id);
    $data['current'] = $this->nurse_model->getCurrentBillingPeriod($assignment_id);

    // Full billing breakdown across all periods, clamped to the service duration
    $data['breakdown'] = billing_assignment_breakdown(
        $data['assignment']->start_date,
        $data['assignment']->end_date,
        $data['periods']
    );

    $this->load->view('home/dashboard');
    $this->load->view('nurse_billing', $data);
    $this->load->view('home/footer');
}

// Apply an effective-dated billing change without overwriting previous arrangement
public function changeBilling()
{
    $assignment_id = $this->input->post('assignment_id');
    $new_frequency = $this->input->post('billing_frequency');
    $new_rate = $this->input->post('rate');
    $effective_from = $this->input->post('effective_from');
    $note = $this->input->post('note');

    $assignment = $this->nurse_model->getAssignmentDetail($assignment_id);
    if (empty($assignment)) {
        show_404();
        return;
    }

    // Validate input
    $allowed_freq = array('Daily', 'Weekly', 'Monthly');
    if (!in_array($new_frequency, $allowed_freq, true) || !is_numeric($new_rate) || $new_rate < 0 || empty($effective_from)) {
        $this->session->set_flashdata('error', 'Please provide a valid frequency, non-negative rate and effective date.');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }

    $current = $this->nurse_model->getCurrentBillingPeriod($assignment_id);

    // The new arrangement must start after the current one began, and within the placement.
    if ($current && $effective_from <= $current->effective_from) {
        $this->session->set_flashdata('error', 'The effective date must be after the current arrangement started (' . $current->effective_from . ').');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }
    if (!empty($assignment->start_date) && $effective_from < $assignment->start_date) {
        $this->session->set_flashdata('error', 'The effective date cannot be before the placement start date.');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }

    // Close the current open period the day before the new one takes effect,
    // preserving the previous arrangement in history.
    if ($current) {
        $close_date = date('Y-m-d', strtotime($effective_from . ' -1 day'));
        $this->nurse_model->closeBillingPeriod($current->id, $close_date);
    }

    $u = $this->_current_user();
    $this->nurse_model->insertBillingPeriod(array(
        'assignment_id'     => $assignment_id,
        'nurse_id'          => $assignment->nurse_id,
        'patient_id'        => $assignment->patient_id,
        'billing_frequency' => $new_frequency,
        'rate'              => $new_rate,
        'effective_from'    => $effective_from,
        'effective_to'      => $assignment->end_date,
        'note'              => $note,
        'created_by'        => $u ? $u->id : null,
        'created_by_name'   => $u ? $u->username : null,
    ));

    // Keep the assignment's "headline" term/rate in sync with the latest arrangement
    $term_map = array('Daily' => 'Day', 'Weekly' => 'Week', 'Monthly' => 'Month');
    $this->db->where('id', $assignment_id);
    $this->db->update('nurse_assignments', array(
        'payment_term' => isset($term_map[$new_frequency]) ? $term_map[$new_frequency] : 'Day',
    ));

    audit_log(array(
        'module'      => 'nurse',
        'record_type' => 'nurse_assignment',
        'record_id'   => $assignment_id,
        'action'      => 'Billing arrangement changed',
        'field'       => 'billing',
        'old_value'   => $current ? ($current->billing_frequency . ' @ ' . $current->rate) : null,
        'new_value'   => $new_frequency . ' @ ' . $new_rate . ' (from ' . $effective_from . ')',
        'reason'      => $note,
    ));

    $this->session->set_flashdata('success', 'Billing arrangement updated. Previous arrangement preserved in history.');
    redirect('nurse/billing/' . $assignment_id);
}

// Extend the service duration of a placement
public function extendService()
{
    $assignment_id = $this->input->post('assignment_id');
    $new_end_date = $this->input->post('end_date');

    $assignment = $this->nurse_model->getAssignmentDetail($assignment_id);
    if (empty($assignment)) {
        show_404();
        return;
    }

    // Validate: a valid new end date, not before the placement start
    if (empty($new_end_date) || (!empty($assignment->start_date) && $new_end_date < $assignment->start_date)) {
        $this->session->set_flashdata('error', 'Please provide a valid end date on or after the placement start date.');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }

    $this->nurse_model->updateAssignmentEndDate($assignment_id, $new_end_date);

    // Extend the current open billing period to the new end date so it keeps applying
    $current = $this->nurse_model->getCurrentBillingPeriod($assignment_id);
    if ($current) {
        $this->nurse_model->closeBillingPeriod($current->id, $new_end_date);
    }

    $this->session->set_flashdata('success', 'Service duration extended.');
    redirect('nurse/billing/' . $assignment_id);
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

    $records = $this->db->get()->result();

    // Compute each placement's billing using the effective-dated periods,
    // rounding up to whole billing periods (see billing_helper).
    foreach ($records as $rec) {
        $periods = $this->nurse_model->getBillingPeriods($rec->assignment_id);
        if (!empty($periods)) {
            $bd = billing_assignment_breakdown($rec->start_date, $rec->end_date, $periods);
            $rec->calc_total = $bd['total'];
        } else {
            // Fallback for legacy rows with no billing period recorded
            $days = billing_days_inclusive($rec->start_date, $rec->end_date);
            $freq = ($rec->billing_type == 'Weekly') ? 'Weekly' : (($rec->billing_type == 'Monthly') ? 'Monthly' : 'Daily');
            $rec->calc_total = billing_period_charge($rec->billing_amount, $freq, $days);
        }
        $rec->calc_days = billing_days_inclusive($rec->start_date, $rec->end_date);
    }
    $data['records'] = $records;

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
    $nurse_id = $this->input->post('nurse_id');
    $amount = $this->input->post('amount');

    // Validate
    if (empty($nurse_id) || !is_numeric($amount) || $amount < 0) {
        $this->session->set_flashdata('error', 'Please enter a valid payment amount.');
        redirect('nurse/payment/' . $nurse_id);
        return;
    }

    $data = array(
        'nurse_id' => $nurse_id,
        'amount' => $amount,
        'payment_date' => $this->input->post('payment_date'),
        'note' => $this->input->post('note')
    );

    $this->db->insert('nurse_payments', $data);

    $this->session->set_flashdata(
        'success',
        'Payment Added Successfully'
    );

    // Redirect to the nurse's payment page (avoid trusting the Referer header)
    redirect('nurse/payment/' . $nurse_id);
}
}

/* End of file nurse.php */
/* Location: ./application/modules/nurse/controllers/nurse.php */
