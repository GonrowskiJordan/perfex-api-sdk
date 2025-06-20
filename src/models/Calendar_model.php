<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . 'core/App_Model.php');

class Calendar_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->db->table_exists(db_prefix() . 'playground_events')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'playground_events' . '` (
                eventid` int(11) NOT NULL AUTO_INCREMENT,
                `title` longtext NOT NULL,
                `description` mediumtext DEFAULT NULL,
                `userid` int(11) NOT NULL,
                `start` datetime NOT NULL,
                `end` datetime DEFAULT NULL,
                `public` int(11) NOT NULL DEFAULT 0,
                `color` varchar(10) DEFAULT NULL,
                `isstartnotified` tinyint(1) NOT NULL DEFAULT 0,
                `reminder_before` int(11) NOT NULL DEFAULT 0,
                `reminder_before_type` varchar(10) DEFAULT NULL,
                PRIMARY KEY (`eventid`));
            ');
        }
    }    

    public function get_events($id = '', $playground = false)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . ($playground ? 'playground' : '') . 'events');
        if ($id >0) {
            $this->db->where('eventid', $id);
        }
        return $this->db->get()->result_array();
    }

    /**
     * Add new event
     * @param array $data event $_POST data
     */
    public function event($data, $playground = false)
    {
        $data['start']  = to_sql_date($data['start'], true);
        if ($data['end'] == '') {
            unset($data['end']);
        } else {
            $data['end'] = to_sql_date($data['end'], true);
        }

        $data['description'] = nl2br($data['description']);
        if (isset($data['eventid'])) {
            $this->db->where('eventid', $data['eventid']);
            $event = $this->db->get(db_prefix() . ($playground ? 'playground' : '') . 'events')->row();
            if (!$event) {
                return false;
            }
            if ($event->isstartnotified == 1) {
                if ($data['start'] > $event->start) {
                    $data['isstartnotified'] = 0;
                }
            }

            $data = hooks()->apply_filters('event_update_data', $data, $data['eventid']);

            $this->db->where('eventid', $data['eventid']);
            $this->db->update(db_prefix() . ($playground ? 'playground' : '') . 'events', $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }

            return false;
        }

        $data = hooks()->apply_filters('event_create_data', $data);

        $this->db->insert(db_prefix() . ($playground ? 'playground' : '') . 'events', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return true;
        }

        return false;
    }
}