<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Nurse extends MX_Controller
{
    // Assignment payment_term => billing frequency / fee column
    private static $TERM_FREQUENCY = array('Day' => 'Daily', 'Week' => 'Weekly', 'Month' => 'Monthly');
    private static $TERM_FEE_FIELD = array('Day' => 'per_day_fee', 'Week' => 'per_week_fee', 'Month' => 'per_month_fee');

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
        $license_expiry_date = $this->input->post('license_expiry_date') ?: null;


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
                    $this->_record_certificates($nurse_user_id, $nurse_license_pdf, $nurse_profile_pdf, $license_expiry_date);
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

                if ($old_nurse && (string) $old_nurse->license_expiry_date !== (string) $license_expiry_date) {
                    audit_log(array(
                        'module'      => 'nurse',
                        'record_type' => 'nurse',
                        'record_id'   => $id,
                        'action'      => 'Licence expiry date changed',
                        'field'       => 'license_expiry_date',
                        'old_value'   => $old_nurse->license_expiry_date,
                        'new_value'   => $license_expiry_date,
                    ));
                    // Keep the expiry on the current licence in step when no new file was uploaded
                    if (!$nurse_license_pdf) {
                        $this->db->where(array('nurse_id' => $id, 'cert_type' => 'Licence', 'is_current' => 1));
                        $this->db->update('nurse_certificates', array('expiry_date' => $license_expiry_date ?: null));
                    }
                }
                $this->_record_certificates($id, $nurse_license_pdf, $nurse_profile_pdf, $license_expiry_date);

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

    // Keep every uploaded licence / profile document; the newest of each type is the current one
    private function _record_certificates($nurse_id, $license_pdf, $profile_pdf, $license_expiry)
    {
        $u = $this->_current_user();
        $uploads = array(
            'Licence' => array($license_pdf, $license_expiry ?: null),
            'Profile' => array($profile_pdf, null),
        );
        foreach ($uploads as $type => $upload) {
            if (empty($upload[0])) {
                continue;
            }
            $this->db->where(array('nurse_id' => $nurse_id, 'cert_type' => $type));
            $this->db->update('nurse_certificates', array('is_current' => 0));
            $this->db->insert('nurse_certificates', array(
                'nurse_id'         => $nurse_id,
                'cert_type'        => $type,
                'file_path'        => $upload[0],
                'expiry_date'      => $upload[1],
                'is_current'       => 1,
                'uploaded_by'      => $u ? $u->id : null,
                'uploaded_by_name' => $u ? $u->username : null,
            ));
            audit_log(array(
                'module'      => 'nurse',
                'record_type' => 'nurse',
                'record_id'   => $nurse_id,
                'action'      => $type == 'Licence' ? 'Licence uploaded' : 'Profile document uploaded',
                'new_value'   => $upload[1] ? 'Expires ' . date('d M Y', strtotime($upload[1])) : null,
            ));
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
        $reason = trim((string) $this->input->get('reason', true));

        $user_data = $this->db->get_where('nurse', array('id' => $id))->row();
        if (empty($user_data)) {
            redirect('nurse');
            return;
        }
        if ($reason === '') {
            $this->session->set_flashdata('feedback', 'A reason is required to deactivate a nurse.');
            redirect('nurse');
            return;
        }

        $this->db->where(array('nurse_id' => $id, 'is_active' => 1));
        $this->db->where('end_date >=', date('Y-m-d'));
        if ($this->db->count_all_results('nurse_assignments') > 0) {
            $this->session->set_flashdata('feedback', 'This nurse still has current or upcoming placements. End or remove those assignments before deactivating the nurse.');
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
        $data['payments'] = array_reverse($this->nurse_model->getPayments($nurse_id));

        // Current and previous certificates / licences
        $this->db->where('nurse_id', $nurse_id);
        $this->db->order_by('uploaded_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        $data['certificates'] = $this->db->get('nurse_certificates')->result();

        // Status history + other audit activity for this nurse
        $data['audit'] = audit_get('nurse', $nurse_id);

        // Feedback received about this nurse
        $this->db->select('feedback.*, patient.name as patient_name');
        $this->db->from('feedback');
        $this->db->join('patient', 'patient.id = feedback.patient_id', 'left');
        $this->db->where('feedback.nurse_id', $nurse_id);
        $this->db->where('feedback.status', 'Submitted');
        $this->db->order_by('feedback.id', 'DESC');
        $data['feedback'] = $this->db->get()->result();

        $this->load->view('home/dashboard');
        $this->load->view('nurse_record', $data);
        $this->load->view('home/footer');
    }







    public function assign($nurse_id)
    {

        // nurse detail
        $data['nurse'] = $this->nurse_model->getNurseById($nurse_id);
        if (empty($data['nurse'])) {
            show_404();
            return;
        }

        // patients list (archived patients cannot be assigned)
        $data['patients'] = $this->db->where('is_active', 1)->get('patient')->result();

        // Patients that currently have an active nurse assignment (colour flag in the dropdown)
        $this->db->distinct();
        $this->db->select('patient_id');
        $this->db->where('is_active', 1);
        $this->db->where('end_date >=', date('Y-m-d'));
        $data['assigned_patient_ids'] = array_column($this->db->get('nurse_assignments')->result_array(), 'patient_id');

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

        // Current fee / transport arrangement of each assignment
        foreach ($data['history'] as $row) {
            $row->current = $this->nurse_model->getCurrentBillingPeriod($row->id);
        }

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
        $assignment_role = $this->input->post('assignment_role') === 'Additional' ? 'Additional' : 'Primary';
        $back = $this->input->post('redirect_to') === 'patient' ? 'patient' : 'nurse/assign/' . $nurse_id;

        $payment_term = $this->input->post('payment_term');
        if (!isset(self::$TERM_FREQUENCY[$payment_term])) {
            $payment_term = 'Day';
        }
        $billing_amount = $this->input->post(self::$TERM_FEE_FIELD[$payment_term]);
        $transport = $this->_transport_input();

        $error = null;
        if (empty($nurse_id) || empty($patient_id) || empty($start_date) || empty($end_date)) {
            $error = 'Please select a nurse and a patient and provide start and end dates.';
        } elseif ($end_date < $start_date) {
            $error = 'End date cannot be before start date.';
        } elseif (!is_numeric($billing_amount) || $billing_amount < 0) {
            $error = 'Please enter a valid billing amount.';
        } elseif (is_string($transport)) {
            $error = $transport;
        } elseif (!$this->db->where(array('id' => $nurse_id, 'is_active' => 1))->count_all_results('nurse')) {
            $error = 'This nurse is deactivated and cannot be assigned.';
        } elseif (!$this->db->where(array('id' => $patient_id, 'is_active' => 1))->count_all_results('patient')) {
            $error = 'This patient is archived and cannot be assigned a nurse.';
        }
        if ($error) {
            $this->session->set_flashdata('error', $error);
            redirect($back);
            return;
        }

        // Check the nurse isn't already assigned for overlapping dates.
        $this->db->where('nurse_id', $nurse_id);
        $this->db->where('is_active', 1);
        $this->db->where('start_date <=', $end_date);
        $this->db->where('end_date >=', $start_date);
        if ($this->db->get('nurse_assignments')->num_rows() > 0) {
            $this->session->set_flashdata('error', 'Nurse already assigned for selected dates');
            redirect($back);
            return;
        }

        // Roles are never changed automatically; just warn about another Primary nurse.
        $other_primary = $assignment_role === 'Primary'
            ? $this->nurse_model->getOverlappingPrimary($patient_id, $start_date, $end_date)
            : array();

        $fees = array('per_day_fee' => null, 'per_week_fee' => null, 'per_month_fee' => null);
        $fees[self::$TERM_FEE_FIELD[$payment_term]] = $billing_amount;

        $this->db->insert('nurse_assignments', array_merge(array(
            'nurse_id' => $nurse_id,
            'patient_id' => $patient_id,
            'assignment_role' => $assignment_role,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'payment_term' => $payment_term,
        ), $fees, $transport));
        $assignment_id = $this->db->insert_id();

        $billing_frequency = self::$TERM_FREQUENCY[$payment_term];

        $this->db->insert('nurse_billing', array_merge(array(
            'assignment_id' => $assignment_id,
            'nurse_id' => $nurse_id,
            'patient_id' => $patient_id,
            'billing_type' => $billing_frequency,
            'billing_amount' => $billing_amount,
            'billing_start_date' => $start_date,
        ), $transport));

        // Initial effective-dated billing period for this placement
        $u = $this->_current_user();
        $this->nurse_model->insertBillingPeriod(array_merge(array(
            'assignment_id'     => $assignment_id,
            'nurse_id'          => $nurse_id,
            'patient_id'        => $patient_id,
            'billing_frequency' => $billing_frequency,
            'rate'              => $billing_amount,
            'effective_from'    => $start_date,
            'effective_to'      => $end_date,
            'note'              => 'Initial billing arrangement',
            'created_by'        => $u ? $u->id : null,
            'created_by_name'   => $u ? $u->username : null,
        ), $transport));

        $patient = $this->db->get_where('patient', array('id' => $patient_id))->row();
        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse_assignment',
            'record_id'   => $assignment_id,
            'action'      => 'Nurse assigned to patient',
            'new_value'   => ($patient ? $patient->name : 'Patient #' . $patient_id) . ' (' . $assignment_role . ')',
        ));
        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse',
            'record_id'   => $nurse_id,
            'action'      => 'Assigned to patient',
            'new_value'   => ($patient ? $patient->name : 'Patient #' . $patient_id) . ' (' . $assignment_role . ', ' . date('d M Y', strtotime($start_date)) . ' – ' . date('d M Y', strtotime($end_date)) . ')',
        ));

        $this->session->set_flashdata('success', 'Nurse Assigned Successfully');
        if (!empty($other_primary)) {
            $this->session->set_flashdata('warning', $this->_primary_warning($other_primary));
        }

        redirect($back);
    }

    private function _primary_warning($other_primary)
    {
        $names = array();
        foreach ($other_primary as $op) {
            $names[] = $op->nurse_name . ' (' . date('d M Y', strtotime($op->start_date)) . ' – ' . date('d M Y', strtotime($op->end_date)) . ')';
        }
        return 'Note: this patient already has a Primary nurse for overlapping dates: ' . implode(', ', $names)
            . '. Both assignments are kept as Primary. Edit the assignment if one of them should be Additional.';
    }

    /**
     * Transport / commute fields from the posted form.
     *
     * @return array|string transport_frequency, transport_charge, transport_note — or an error message
     */
    private function _transport_input()
    {
        $frequency = $this->input->post('transport_frequency');
        $charge = $this->input->post('transport_charge');
        $note = $this->input->post('transport_note', true);

        if (!in_array($frequency, array('Daily', 'Weekly', 'Monthly', 'Flexible'), true)) {
            $frequency = null;
        }
        if ($charge === null || $charge === '') {
            $charge = 0;
        }
        if (!is_numeric($charge) || $charge < 0) {
            return 'Please enter a valid transport charge.';
        }
        if ($charge > 0 && $frequency === null) {
            return 'Please choose a commute frequency for the transport charge.';
        }
        if ($charge == 0) {
            $frequency = null;
        }

        return array(
            'transport_frequency' => $frequency,
            'transport_charge'    => (float) $charge,
            'transport_note'      => $frequency === 'Flexible' ? $note : null,
        );
    }







    public function assignments()
    {
        $nurse = $this->input->get('nurse');
        $search = $this->input->get('search');

        $data['nurses'] = $this->nurse_model->getNurse();

        $data['assignments'] = $this->nurse_model->getAssignments($nurse, $search);
        foreach ($data['assignments'] as $row) {
            $row->current = $this->nurse_model->getCurrentBillingPeriod($row->id);
        }

        $this->load->view('home/dashboard');
        $this->load->view('assigned_nurses', $data);
        $this->load->view('home/footer');
    }

    // Remove from the nurse's assign page
    public function deleteAssignments($id)
    {
        $assignment = $this->db->where('id', $id)->get('nurse_assignments')->row();
        $this->_remove_assignment($assignment, $assignment ? 'nurse/assign/' . $assignment->nurse_id : 'nurse/assignments');
    }

    // Remove from the "Assigned Nurses" list
    public function deleteAssignment($id)
    {
        $assignment = $this->db->where('id', $id)->get('nurse_assignments')->row();
        $this->_remove_assignment($assignment, 'nurse/assignments');
    }

    // Soft delete (deactivate) an assignment with a mandatory reason; the record and its billing stay in history
    private function _remove_assignment($assignment, $back)
    {
        if (empty($assignment) || $assignment->is_active == 0) {
            $this->session->set_flashdata('error', 'Assignment not found or already removed.');
            redirect($back);
            return;
        }
        $reason = trim((string) $this->input->get('reason', true));
        if ($reason === '') {
            $this->session->set_flashdata('error', 'A reason is required to remove an assignment.');
            redirect($back);
            return;
        }

        $this->db->where('id', $assignment->id);
        $this->db->update('nurse_assignments', array(
            'is_active'  => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
        ));

        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse_assignment',
            'record_id'   => $assignment->id,
            'action'      => 'Assignment removed (soft delete)',
            'reason'      => $reason,
        ));
        audit_log(array(
            'module'      => 'nurse',
            'record_type' => 'nurse',
            'record_id'   => $assignment->nurse_id,
            'action'      => 'Assignment removed',
            'new_value'   => $this->db->get_where('patient', array('id' => $assignment->patient_id))->row('name'),
            'reason'      => $reason,
        ));

        $this->session->set_flashdata('success', 'Assignment removed. Historical record preserved.');
        redirect($back);
    }


    public function editAssignment($id)
    {
        $data['assignment'] = $this->nurse_model->getAssignmentDetail($id);
        if (empty($data['assignment'])) {
            show_404();
            return;
        }

        $data['current'] = $this->nurse_model->getCurrentBillingPeriod($id);
        $data['periods'] = $this->nurse_model->getBillingPeriods($id);
        $data['audit'] = audit_get('nurse_assignment', $id);
        $this->db->group_start();
        $this->db->where('is_active', 1);
        $this->db->or_where('id', $data['assignment']->patient_id);
        $this->db->group_end();
        $data['patients'] = $this->db->get('patient')->result();
        $data['return'] = $this->input->get('return') === 'assign' ? 'assign' : 'list';

        $this->load->view('home/dashboard');
        $this->load->view('edit_assignment', $data);
        $this->load->view('home/footer');
    }

    // Edit an assignment. Date/patient/role changes are written to the audit trail;
    // fee and transport changes become a new effective-dated billing period.
    public function updateAssignment()
    {
        $id = $this->input->post('id');
        $old = $this->nurse_model->getAssignmentDetail($id);
        if (empty($old)) {
            show_404();
            return;
        }

        $return = $this->input->post('return') === 'assign' ? 'assign' : 'list';
        $back_edit = 'nurse/editAssignment/' . $id . '?return=' . $return;
        $back = $return === 'assign' ? 'nurse/assign/' . $old->nurse_id : 'nurse/assignments';

        $patient_id = $this->input->post('patient_id');
        $role = $this->input->post('assignment_role') === 'Additional' ? 'Additional' : 'Primary';
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $reason = $this->input->post('reason', true);

        $payment_term = $this->input->post('payment_term');
        if (!isset(self::$TERM_FREQUENCY[$payment_term])) {
            $payment_term = 'Day';
        }
        $rate = $this->input->post(self::$TERM_FEE_FIELD[$payment_term]);
        $transport = $this->_transport_input();

        $error = null;
        if (empty($patient_id) || empty($start_date) || empty($end_date)) {
            $error = 'Please select a patient and provide start and end dates.';
        } elseif ($end_date < $start_date) {
            $error = 'End date cannot be before start date.';
        } elseif (!is_numeric($rate) || $rate < 0) {
            $error = 'Please enter a valid fee.';
        } elseif (is_string($transport)) {
            $error = $transport;
        }
        if (!$error) {
            $this->db->where('nurse_id', $old->nurse_id);
            $this->db->where('is_active', 1);
            $this->db->where('id !=', $id);
            $this->db->where('start_date <=', $end_date);
            $this->db->where('end_date >=', $start_date);
            if ($this->db->get('nurse_assignments')->num_rows() > 0) {
                $error = 'Nurse already assigned to another patient for these dates.';
            }
        }
        if ($error) {
            $this->session->set_flashdata('error', $error);
            redirect($back_edit);
            return;
        }

        // 1) Patient / role / dates
        $changes = array();
        $labels = array('patient_id' => 'Patient', 'assignment_role' => 'Role', 'start_date' => 'Start date', 'end_date' => 'End date');
        $new_values = array('patient_id' => $patient_id, 'assignment_role' => $role, 'start_date' => $start_date, 'end_date' => $end_date);
        foreach ($labels as $field => $label) {
            if ((string) $old->$field !== (string) $new_values[$field]) {
                $changes[$field] = $label;
            }
        }

        if (!empty($changes)) {
            $this->db->where('id', $id);
            $this->db->update('nurse_assignments', $new_values);

            if (isset($changes['patient_id'])) {
                $this->db->where('assignment_id', $id);
                $this->db->update('nurse_billing_periods', array('patient_id' => $patient_id));
            }
            if (isset($changes['start_date'])) {
                $first = $this->nurse_model->getFirstBillingPeriod($id);
                if ($first && ($first->effective_from == $old->start_date || $start_date < $first->effective_from)) {
                    $this->nurse_model->updateBillingPeriod($first->id, array('effective_from' => $start_date));
                }
            }
            if (isset($changes['end_date'])) {
                $last = $this->nurse_model->getCurrentBillingPeriod($id);
                if ($last) {
                    $this->nurse_model->closeBillingPeriod($last->id, $end_date);
                }
            }

            foreach ($changes as $field => $label) {
                $old_value = $old->$field;
                $new_value = $new_values[$field];
                if ($field === 'patient_id') {
                    $new_patient = $this->db->get_where('patient', array('id' => $patient_id))->row();
                    $old_value = $old->patient_name;
                    $new_value = $new_patient ? $new_patient->name : '#' . $patient_id;
                }
                audit_log(array(
                    'module'      => 'nurse',
                    'record_type' => 'nurse_assignment',
                    'record_id'   => $id,
                    'action'      => $label . ' changed',
                    'field'       => $field,
                    'old_value'   => $old_value,
                    'new_value'   => $new_value,
                    'reason'      => $reason,
                ));
            }
        }

        // 2) Fee / transport (effective-dated)
        $assignment = $this->nurse_model->getAssignmentDetail($id);
        $current = $this->nurse_model->getCurrentBillingPeriod($id);
        $new = array_merge(array(
            'billing_frequency' => self::$TERM_FREQUENCY[$payment_term],
            'rate' => $rate,
        ), $transport);

        $billing_changed = false;
        if (!$current || $this->_arrangement_differs($current, $new)) {
            $effective_from = $this->input->post('effective_from') ?: $start_date;
            $result = $this->_apply_billing_change($assignment, $new, $effective_from, $reason);
            if ($result !== true) {
                $this->session->set_flashdata('error', (!empty($changes) ? 'Dates/role were saved, but the fee/transport change was not applied: ' : '') . $result);
                redirect($back_edit);
                return;
            }
            $billing_changed = true;
        }

        if (empty($changes) && !$billing_changed) {
            $this->session->set_flashdata('success', 'No changes were made.');
        } else {
            $this->session->set_flashdata('success', 'Assignment updated. Previous values are kept in the history.');
        }

        if ($role === 'Primary' && (isset($changes['assignment_role']) || isset($changes['start_date']) || isset($changes['end_date']) || isset($changes['patient_id']))) {
            $other_primary = $this->nurse_model->getOverlappingPrimary($patient_id, $start_date, $end_date, $id);
            if (!empty($other_primary)) {
                $this->session->set_flashdata('warning', $this->_primary_warning($other_primary));
            }
        }

        redirect($back);
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
    $data['accrued'] = billing_assignment_breakdown(
        $data['assignment']->start_date,
        $data['assignment']->end_date,
        $data['periods'],
        date('Y-m-d')
    );
    $data['audit'] = audit_get('nurse_assignment', $assignment_id);

    $this->load->view('home/dashboard');
    $this->load->view('nurse_billing', $data);
    $this->load->view('home/footer');
}

// Apply an effective-dated billing change without overwriting previous arrangement
public function changeBilling()
{
    $assignment_id = $this->input->post('assignment_id');
    $assignment = $this->nurse_model->getAssignmentDetail($assignment_id);
    if (empty($assignment)) {
        show_404();
        return;
    }

    $transport = $this->_transport_input();
    if (is_string($transport)) {
        $result = $transport;
    } else {
        $result = $this->_apply_billing_change($assignment, array_merge(array(
            'billing_frequency' => $this->input->post('billing_frequency'),
            'rate' => $this->input->post('rate'),
        ), $transport), $this->input->post('effective_from'), $this->input->post('note', true));
    }

    if ($result === true) {
        $this->session->set_flashdata('success', 'Billing arrangement updated. Previous arrangement preserved in history.');
    } else {
        $this->session->set_flashdata('error', $result);
    }
    redirect('nurse/billing/' . $assignment_id);
}

private function _arrangement_differs($current, $new)
{
    $cur_tf = (float) $current->transport_charge > 0 ? $current->transport_frequency : null;
    $new_tf = (float) $new['transport_charge'] > 0 ? $new['transport_frequency'] : null;

    return $current->billing_frequency !== $new['billing_frequency']
        || round((float) $current->rate, 2) != round((float) $new['rate'], 2)
        || $cur_tf !== $new_tf
        || round((float) $current->transport_charge, 2) != round((float) $new['transport_charge'], 2)
        || ($new_tf === 'Flexible' && (string) $current->transport_note !== (string) $new['transport_note']);
}

private function _describe_arrangement($p)
{
    $p = (object) $p;
    $text = $p->billing_frequency . ' @ ' . number_format((float) $p->rate, 2);
    if ((float) $p->transport_charge > 0) {
        $text .= '; transport ' . $p->transport_frequency . ' @ ' . number_format((float) $p->transport_charge, 2);
    }
    return $text;
}

/**
 * Start a new billing arrangement (rate, frequency, transport) from $effective_from.
 * The current arrangement is closed the day before; if the new one starts on the
 * same day as the current one, the current one is marked superseded (a correction).
 * Nothing is deleted.
 *
 * @return true|string true on success, otherwise an error message
 */
private function _apply_billing_change($assignment, $new, $effective_from, $note)
{
    if (!in_array($new['billing_frequency'], array('Daily', 'Weekly', 'Monthly'), true) || !is_numeric($new['rate']) || $new['rate'] < 0) {
        return 'Please provide a valid billing frequency and a non-negative rate.';
    }
    if (empty($effective_from) || !strtotime($effective_from)) {
        return 'Please provide a valid effective date.';
    }
    $effective_from = date('Y-m-d', strtotime($effective_from));

    if (!empty($assignment->start_date) && $effective_from < $assignment->start_date) {
        return 'The effective date cannot be before the placement start date (' . date('d M Y', strtotime($assignment->start_date)) . ').';
    }
    if (!empty($assignment->end_date) && $effective_from > $assignment->end_date) {
        return 'The effective date is after the placement ends (' . date('d M Y', strtotime($assignment->end_date)) . '). Extend the service duration first.';
    }

    $current = $this->nurse_model->getCurrentBillingPeriod($assignment->id);
    if ($current && $effective_from < $current->effective_from) {
        return 'The effective date must be on or after the date the current arrangement started (' . date('d M Y', strtotime($current->effective_from)) . ').';
    }
    if ($current && !$this->_arrangement_differs($current, $new)) {
        return 'Nothing changed: the rate, frequency and transport are the same as the current arrangement.';
    }

    $correction = $current && $effective_from == $current->effective_from;
    if ($correction) {
        $this->nurse_model->supersedeBillingPeriod($current->id);
    } elseif ($current) {
        $this->nurse_model->closeBillingPeriod($current->id, billing_add_days($effective_from, -1));
    }

    $u = $this->_current_user();
    $this->nurse_model->insertBillingPeriod(array(
        'assignment_id'       => $assignment->id,
        'nurse_id'            => $assignment->nurse_id,
        'patient_id'          => $assignment->patient_id,
        'billing_frequency'   => $new['billing_frequency'],
        'rate'                => $new['rate'],
        'transport_frequency' => $new['transport_frequency'],
        'transport_charge'    => $new['transport_charge'],
        'transport_note'      => $new['transport_note'],
        'effective_from'      => $effective_from,
        'effective_to'        => $assignment->end_date,
        'note'                => $note,
        'created_by'          => $u ? $u->id : null,
        'created_by_name'     => $u ? $u->username : null,
    ));

    // Keep the assignment's "headline" values in sync with the latest arrangement
    $term = array_search($new['billing_frequency'], self::$TERM_FREQUENCY, true);
    $headline = array('per_day_fee' => null, 'per_week_fee' => null, 'per_month_fee' => null);
    $headline[self::$TERM_FEE_FIELD[$term]] = $new['rate'];
    $transport = array(
        'transport_frequency' => $new['transport_frequency'],
        'transport_charge'    => $new['transport_charge'],
        'transport_note'      => $new['transport_note'],
    );

    $this->db->where('id', $assignment->id);
    $this->db->update('nurse_assignments', array_merge(array('payment_term' => $term), $headline, $transport));

    $this->db->where('assignment_id', $assignment->id);
    $this->db->update('nurse_billing', array_merge(array(
        'billing_type'   => $new['billing_frequency'],
        'billing_amount' => $new['rate'],
    ), $transport));

    audit_log(array(
        'module'      => 'nurse',
        'record_type' => 'nurse_assignment',
        'record_id'   => $assignment->id,
        'action'      => $correction ? 'Billing arrangement corrected' : 'Billing arrangement changed',
        'field'       => 'billing',
        'old_value'   => $current ? $this->_describe_arrangement($current) : null,
        'new_value'   => $this->_describe_arrangement($new) . ' (from ' . date('d M Y', strtotime($effective_from)) . ')',
        'reason'      => $note,
    ));

    return true;
}

// Change (extend or shorten) the service duration of a placement
public function extendService()
{
    $assignment_id = $this->input->post('assignment_id');
    $new_end_date = $this->input->post('end_date');

    $assignment = $this->nurse_model->getAssignmentDetail($assignment_id);
    if (empty($assignment)) {
        show_404();
        return;
    }

    $current = $this->nurse_model->getCurrentBillingPeriod($assignment_id);

    if (empty($new_end_date) || (!empty($assignment->start_date) && $new_end_date < $assignment->start_date)) {
        $this->session->set_flashdata('error', 'Please provide a valid end date on or after the placement start date.');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }
    if ($current && $new_end_date < $current->effective_from) {
        $this->session->set_flashdata('error', 'The new end date is before the current billing arrangement started (' . date('d M Y', strtotime($current->effective_from)) . ').');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }
    if ($new_end_date == $assignment->end_date) {
        $this->session->set_flashdata('error', 'The end date is unchanged.');
        redirect('nurse/billing/' . $assignment_id);
        return;
    }

    $this->nurse_model->updateAssignmentEndDate($assignment_id, $new_end_date);

    // The current billing arrangement keeps applying up to the new end date
    if ($current) {
        $this->nurse_model->closeBillingPeriod($current->id, $new_end_date);
    }

    audit_log(array(
        'module'      => 'nurse',
        'record_type' => 'nurse_assignment',
        'record_id'   => $assignment_id,
        'action'      => $new_end_date > $assignment->end_date ? 'Service duration extended' : 'Service duration shortened',
        'field'       => 'end_date',
        'old_value'   => $assignment->end_date,
        'new_value'   => $new_end_date,
        'reason'      => $this->input->post('note', true),
    ));

    $this->session->set_flashdata('success', 'Service end date changed from ' . date('d M Y', strtotime($assignment->end_date)) . ' to ' . date('d M Y', strtotime($new_end_date)) . '.');
    redirect('nurse/billing/' . $assignment_id);
}


// ================= PAYMENT LEDGER =================

// Billing arrangement built from the assignment itself, for placements with no billing period recorded
private function _legacy_period($a)
{
    $term = isset(self::$TERM_FREQUENCY[$a->payment_term]) ? $a->payment_term : 'Day';
    $fee_field = self::$TERM_FEE_FIELD[$term];
    return (object) array(
        'id'                  => null,
        'effective_from'      => $a->start_date,
        'effective_to'        => $a->end_date,
        'billing_frequency'   => self::$TERM_FREQUENCY[$term],
        'rate'                => (float) $a->$fee_field,
        'transport_frequency' => $a->transport_frequency,
        'transport_charge'    => (float) $a->transport_charge,
        'is_superseded'       => 0,
    );
}

/**
 * Everything owed to / paid to a nurse up to $today: per active assignment the
 * contract total, the amount accrued till today, payments applied and the amount
 * due; plus the payable units (days/weeks/months) used by the unpaid page.
 */
private function _ledger($nurse_id, $today)
{
    $assignments = $this->nurse_model->getNurseAssignments($nurse_id, true);
    $all_units = array();

    foreach ($assignments as $a) {
        $periods = $this->nurse_model->getBillingPeriods($a->id);
        $active = billing_active_periods($periods);
        if (empty($active)) {
            $periods = array($this->_legacy_period($a));
            $active = $periods;
        }
        $a->current = end($active);
        $a->contract = billing_assignment_breakdown($a->start_date, $a->end_date, $periods);
        $a->units = billing_build_units($a->id, $a->start_date, $a->end_date, $periods, $today);
        $all_units = array_merge($all_units, $a->units);
    }

    $payments = $this->nurse_model->getPayments($nurse_id);
    $allocation = billing_allocate_payments($all_units, $payments);

    $totals = array('contract' => 0.0, 'till_today' => 0.0, 'due_today' => 0.0, 'paid' => 0.0);
    foreach ($assignments as $a) {
        $a->till_today = 0.0;
        $a->paid_applied = 0.0;
        $a->unpaid_units = 0;
        foreach ($a->units as $unit) {
            $a->till_today += $unit->amount;
            $a->paid_applied += $unit->paid;
            if ($unit->amount - $unit->paid > 0.004) {
                $a->unpaid_units++;
            }
        }
        $a->due_today = round($a->till_today - $a->paid_applied, 2);
        $a->credit = isset($allocation['assignment_credit'][$a->id]) ? $allocation['assignment_credit'][$a->id] : 0.0;

        $totals['contract'] += $a->contract['total'];
        $totals['till_today'] += $a->till_today;
        $totals['due_today'] += $a->due_today;
    }
    foreach ($payments as $p) {
        $totals['paid'] += $p->amount;
    }
    $totals['balance_till_today'] = round($totals['till_today'] - $totals['paid'], 2);
    $totals['remaining_contract'] = round($totals['contract'] - $totals['paid'], 2);
    $totals['credit'] = $allocation['general_credit'] + array_sum($allocation['assignment_credit']);

    return array(
        'today'       => $today,
        'assignments' => $assignments,
        'payments'    => $payments,
        'totals'      => $totals,
    );
}

public function payment($nurse_id)
{
    $nurse = $this->nurse_model->getNurseById($nurse_id);
    if (empty($nurse)) {
        show_404();
        return;
    }

    $data = $this->_ledger($nurse_id, date('Y-m-d'));
    $data['nurse'] = $nurse;
    $data['prefill'] = array(
        'assignment_id' => $this->input->get('assignment_id'),
        'period_from'   => $this->input->get('period_from'),
        'period_to'     => $this->input->get('period_to'),
        'amount'        => $this->input->get('amount'),
    );

    $this->load->view('home/dashboard');
    $this->load->view('nurse_payment', $data);
    $this->load->view('home/footer');
}

// Days (daily billing), weeks (weekly) or months (monthly) the nurse has not been paid for yet
public function unpaid($nurse_id)
{
    $nurse = $this->nurse_model->getNurseById($nurse_id);
    if (empty($nurse)) {
        show_404();
        return;
    }

    $data = $this->_ledger($nurse_id, date('Y-m-d'));
    $data['nurse'] = $nurse;
    $data['show_all'] = (bool) $this->input->get('all');

    $this->load->view('home/dashboard');
    $this->load->view('nurse_unpaid', $data);
    $this->load->view('home/footer');
}


// ================= SAVE PAYMENT =================

public function addPayment()
{
    $nurse_id = $this->input->post('nurse_id');
    $nurse = $this->nurse_model->getNurseById($nurse_id);
    if (empty($nurse)) {
        redirect('nurse');
        return;
    }

    $back = $this->input->post('return') === 'unpaid' ? 'nurse/unpaid/' . $nurse_id : 'nurse/payment/' . $nurse_id;
    $amount = $this->input->post('amount');
    $payment_date = $this->input->post('payment_date') ?: date('Y-m-d');
    $assignment_id = $this->input->post('assignment_id') ?: null;
    $period_from = $this->input->post('period_from') ?: null;
    $period_to = $this->input->post('period_to') ?: null;

    $assignment = null;
    if ($assignment_id) {
        $assignment = $this->db->get_where('nurse_assignments', array('id' => $assignment_id, 'nurse_id' => $nurse_id))->row();
    }

    $error = null;
    if (!is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid payment amount.';
    } elseif (!strtotime($payment_date)) {
        $error = 'Please enter a valid payment date.';
    } elseif ($assignment_id && empty($assignment)) {
        $error = 'The selected assignment does not belong to this nurse.';
    } elseif (($period_from && !$period_to) || (!$period_from && $period_to)) {
        $error = 'Please provide both "covers from" and "covers to" dates, or leave both empty.';
    } elseif ($period_from && $period_to < $period_from) {
        $error = '"Covers to" cannot be before "covers from".';
    } elseif ($period_from && !$assignment_id) {
        $error = 'Please choose the assignment the covered period belongs to.';
    }
    if ($error) {
        $this->session->set_flashdata('error', $error);
        redirect($back);
        return;
    }

    $u = $this->_current_user();
    $is_advance = $this->input->post('is_advance') ? 1 : 0;
    $this->nurse_model->insertPayment(array(
        'nurse_id'         => $nurse_id,
        'assignment_id'    => $assignment_id,
        'amount'           => $amount,
        'payment_date'     => $payment_date,
        'period_from'      => $period_from,
        'period_to'        => $period_to,
        'is_advance'       => $is_advance,
        'note'             => $this->input->post('note', true),
        'recorded_by_name' => $u ? $u->username : null,
    ));

    $detail = number_format((float) $amount, 2) . ' on ' . date('d M Y', strtotime($payment_date));
    if ($period_from) {
        $detail .= ' for ' . date('d M Y', strtotime($period_from)) . ' – ' . date('d M Y', strtotime($period_to));
    }
    audit_log(array(
        'module'      => 'nurse',
        'record_type' => 'nurse',
        'record_id'   => $nurse_id,
        'action'      => $is_advance ? 'Advance payment recorded' : 'Payment recorded',
        'new_value'   => $detail,
        'reason'      => $this->input->post('note', true),
    ));

    $this->session->set_flashdata('success', 'Payment Added Successfully');
    redirect($back);
}
}

/* End of file nurse.php */
/* Location: ./application/modules/nurse/controllers/nurse.php */
