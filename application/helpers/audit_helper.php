<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Audit trail helper.
 *
 * Records important actions (assignment/removal, billing/rate changes,
 * frequency changes, status changes, record deletion/deactivation) so that
 * historical activity is preserved even when records are soft-deleted.
 */

if (!function_exists('audit_log')) {
    /**
     * Write an audit trail entry.
     *
     * @param array $data keys: module, record_type, record_id, action,
     *                    field, old_value, new_value, reason
     */
    function audit_log($data)
    {
        $CI =& get_instance();

        // Fail-safe: if the audit_trail table is missing, skip logging rather
        // than breaking the operation that triggered it.
        if (!$CI->db->table_exists('audit_trail')) {
            return;
        }

        // Stamp the acting user automatically.
        $performed_by = null;
        $performed_by_name = null;
        if (isset($CI->ion_auth) && $CI->ion_auth->logged_in()) {
            $u = $CI->ion_auth->user()->row();
            if ($u) {
                $performed_by = $u->id;
                $performed_by_name = $u->username;
            }
        }

        $row = array(
            'module'            => isset($data['module']) ? $data['module'] : null,
            'record_type'       => isset($data['record_type']) ? $data['record_type'] : null,
            'record_id'         => isset($data['record_id']) ? $data['record_id'] : null,
            'action'            => isset($data['action']) ? $data['action'] : null,
            'field'             => isset($data['field']) ? $data['field'] : null,
            'old_value'         => isset($data['old_value']) ? $data['old_value'] : null,
            'new_value'         => isset($data['new_value']) ? $data['new_value'] : null,
            'reason'            => isset($data['reason']) ? $data['reason'] : null,
            'performed_by'      => $performed_by,
            'performed_by_name' => $performed_by_name,
        );

        $CI->db->insert('audit_trail', $row);
    }
}

if (!function_exists('audit_get')) {
    /**
     * Fetch audit trail entries for a record (or all).
     */
    function audit_get($record_type = null, $record_id = null, $limit = 200)
    {
        $CI =& get_instance();
        if ($record_type !== null) {
            $CI->db->where('record_type', $record_type);
        }
        if ($record_id !== null) {
            $CI->db->where('record_id', $record_id);
        }
        $CI->db->order_by('created_at', 'DESC');
        $CI->db->order_by('id', 'DESC');
        $CI->db->limit($limit);
        return $CI->db->get('audit_trail')->result();
    }
}
