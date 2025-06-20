<?php

namespace PerfexApiSdk\Models;

defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../third-party/firebase-jwt/BeforeValidException.php';
require_once __DIR__ . '/../third-party/firebase-jwt/ExpiredException.php';
require_once __DIR__ . '/../third-party/firebase-jwt/SignatureInvalidException.php';
require_once __DIR__ . '/../third-party/firebase-jwt/JWT.php';

require_once(APPPATH . 'core/App_Model.php');

use Firebase\JWT\JWT as Api_JWT;
use Firebase\JWT\Key as Api_Key;

class REST_model extends \App_Model
{
    /**
     * Token
     */
    protected $token;

    /**
     * Token Key
     */
    protected $token_key;

    /**
     * Token algorithm
     */
    protected $token_algorithm;

    /**
     * Token Expire Time
     * ------------------
     * (1 day) : 60 * 60 * 24 = 86400
     * (1 hour) : 60 * 60 = 3600
     */
    protected $token_expire_time = 315569260; 

    /**
     * Token Request Header Name
     */
    protected $token_header;

    public function __construct()
    {
        parent::__construct();

        $config = array();
        include (__DIR__ . "/../config/jwt.php");
        foreach ($config AS $key => $value) {
            $this->config->set_item($key, $value);
        }

        $config = array();
        include (__DIR__ . "/../config/api.php");
        foreach ($config AS $key => $value) {
            $this->config->set_item($key, $value);
        }

        $config = array();
        include (__DIR__ . "/../config/rest.php");
        foreach ($config AS $key => $value) {
            $this->config->set_item($key, $value);
        }

        include (__DIR__ . "/../helpers/api_helper.php");
        
        if (!$this->db->table_exists(db_prefix() . $this->config->item('rest_keys_table'))) {
            $this->db->query('CREATE TABLE `' . db_prefix() . '' . $this->config->item('rest_keys_table') . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `user` VARCHAR(50) NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `expiration_date` DATETIME NULL,
                `permission_enable` TINYINT(4) DEFAULT 0,
                `quota_limit` INT(11) NOT NULL DEFAULT 1000,
                `quota_remaining` INT(11) NOT NULL DEFAULT 1000,
                `quota_reset` DATETIME NOT NULL,
                `rate_limit`  INT(11) NOT NULL DEFAULT 60,
                `rate_remaining` INT(11) NOT NULL DEFAULT 60,
                `rate_reset` DATETIME NOT NULL,
                `level` INT(11) NULL,
                `ignore_limits` INT(11) NULL,
                PRIMARY KEY (`id`));
            ');
        }
        if (!$this->db->table_exists(db_prefix() . $this->config->item('rest_key_permissions_table'))) {
            $this->db->query('CREATE TABLE `' . db_prefix() . '' . $this->config->item('rest_key_permissions_table') . '` (
                `key_id` int(11) NOT NULL,
                `feature` varchar(50) NOT NULL,
                `capability` varchar(50) NOT NULL);
            ');
        }
        if (!$this->db->table_exists(db_prefix() . $this->config->item('rest_key_limits_table'))) {
            $this->db->query('CREATE TABLE `' . db_prefix() . '' . $this->config->item('rest_key_limits_table') . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `key_id` INT(11) NOT NULL,
                `uri` VARCHAR(511) NOT NULL,
                `class` VARCHAR(511) NOT NULL,
                `method` VARCHAR(511) NOT NULL,
                `ip_address` VARCHAR(63) NOT NULL,
                `time` DATETIME NOT NULL,
                PRIMARY KEY (`id`));
            ');
        }

        $this->token_key            = $this->config->item('jwt_key');
        $this->token_algorithm      = $this->config->item('jwt_algorithm');
        $this->token_header         = $this->config->item('rest_key_name');
        $this->token_expire_time    = $this->config->item('token_expire_time');
    }

    /**
     * Generate Token
     * @param: {array} data
     */
    public function generateToken($data = null)
    {
        if ($data AND is_array($data))
        {
            // add api time key in user array()
            $data['API_TIME'] = time();

            try {
                return Api_JWT::encode($data, $this->token_key, $this->token_algorithm);
            }
            catch(Exception $e) {
                return 'Message: ' .$e->getMessage();
            }
        } else {
            return "Token Data Undefined!";
        }
    }

    public function get_permissions($id = '', $feature = '', $capability = '')
    {
        $this->db->select('*');
        if ('' != $id) {
            $this->db->where('key_id', $id);
            if ('' != $feature) {
                $this->db->where('feature', $feature);
            }
            if ('' != $capability) {
                $this->db->where('capability', $capability);
            }
    
            return $this->db->get(db_prefix() . $this->config->item('rest_key_permissions_table'))->result_array();
        }

        return [];
    }

    public function set_permissions($id, $permissions)
    {
        if ('' != $id) {
            if ($permissions) {
                foreach ($permissions as $feauture => $capabilities) {
                    foreach ($capabilities as $capability) {
                        if (!$this->get_permissions($id, $feauture, $capability)) {
                            $this->add_permissions($id, $feauture, $capability);
                        }
                    }
                    $feature_permissions = $this->get_permissions($id, $feauture);
                    foreach ($feature_permissions as $feature_permission) {
                        if (!in_array($feature_permission['capability'], array_values($capabilities))) {
                            $this->remove_permissions($id, $feauture, $feature_permission['capability']);
                        }
                    }
                }
            }
            $api_permissions = $this->get_permissions($id);
            foreach ($api_permissions as $permission) {
                $permission_exist = true;
                if (isset($permissions[$permission['feature']])) {
                    $permission_exist = false;
                    foreach ($permissions[$permission['feature']] as $capability) {
                        if ($capability == $permission['capability']) {
                            $permission_exist = true;
                        }
                    }
                } else {
                    $permission_exist = false;
                }
                if (!$permission_exist) {
                    $this->remove_permissions($id, $permission['feature'], $permission['capability']);
                }
            }
        }
    }
    
    public function add_permissions($id = '', $feature = '', $capability = '')
    {
        $permissions = [];
        if ('' != $id) {
            if ('' != $feature) {
                $api_permissions = get_available_api_permissions();
                foreach ($api_permissions as $api_feature => $api_permission) {
                    if ($api_feature == $feature) {
                        foreach ($api_permission['capabilities'] as $api_capability => $name) {
                            if ('' != $capability) {
                                if ($api_capability == $capability) {
                                    $permissions[] = [
                                        'key_id' => $id,
                                        'feature' => $feature,
                                        'capability' => $api_capability,
                                    ];
                                }
                            } else {
                                $permissions[] = [
                                    'key_id' => $id,
                                    'feature' => $feature,
                                    'capability' => $api_capability,
                                ];
                            }
                        }
                    }
                }
            }
        }

        foreach ($permissions as $permission) {
            $this->db->insert(db_prefix() . $this->config->item('rest_key_permissions_table'), $permission);
            if ($this->db->affected_rows() > 0) {
                log_activity('New API Permssion Added [API ID: ' . $permission['key_id'] . ', Feature: ' . $permission['feature'] . ', Capability: ' . $permission['capability'] . ']');
            }
        }
    }

    public function remove_permissions($id = '', $feature = '', $capability = '')
    {
        if ('' != $id) {
            $this->db->where('key_id', $id);
            if ('' != $feature) {
                $this->db->where('feature', $feature);
            }
            if ('' != $capability) {
                $this->db->where('capability', $capability);
            }
    
            $this->db->delete(db_prefix() . $this->config->item('rest_key_permissions_table'));
            if ($this->db->affected_rows() > 0) {
                log_activity('API Permssion Deleted [API ID: ' . $id . ', Feature: ' . $feature . ', Capability: ' . $capability . ']');
    
                return true;
            }
        }

        return false;
    }

    public function get_user($id = '')
    {
        $this->db->select('*');
        if ('' != $id) {
            $this->db->where('id', $id);
        }

        return $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->result_array();
    }

    public function add_user($data)
    {
        $permissions = isset($data['permissions']) ? $data['permissions'] : [];
        unset($data['permissions']);

        $payload = [
            'user' => $data['user'],
            'name' => $data['name'],
        ];
        // Load Authorization Library or Load in autoload config file
        // generate a token
        $data['token'] = $this->generateToken($payload);
        $today         = date('Y-m-d H:i:s');

        $data['expiration_date'] = to_sql_date($data['expiration_date'], true);
        $data['permission_enable'] = 1;
        $this->db->insert(db_prefix() . $this->config->item('rest_keys_table'), $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New User Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        $this->set_permissions($insert_id, $permissions);

        return $insert_id;
    }

    public function update_user($data, $id)
    {
        $permissions = isset($data['permissions']) ? $data['permissions'] : [];
        unset($data['permissions']);

        $data['expiration_date'] = to_sql_date($data['expiration_date'], true);
        $data['permission_enable'] = 1;

        $result = false;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->config->item('rest_keys_table'), $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Ticket User Updated [ID: ' . $id . ' Name: ' . $data['name'] . ']');
            $result = true;
        }
        
        $this->set_permissions($id, $permissions);

        return $result;
    }

    public function delete_user($id)
    {
        $this->remove_permissions($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->config->item('rest_keys_table'));
        if ($this->db->affected_rows() > 0) {
            log_activity('User Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Validate Token with Header
     * @return : user informations
     */
    public function validateToken()
    {
        /**
         * Request All Headers
         */
        $headers = $this->input->request_headers();
        
        /**
         * Authorization Header Exists
         */
        $token_data = $this->tokenIsExist($headers);
        if ($token_data['status'] === TRUE)
        {
            try
            {
                /**
                 * Token Decode
                 */
                try {
                    $token_decode = Api_JWT::decode($token_data['token'], new Api_Key($this->token_key, $this->token_algorithm));
                } catch(Exception $e) {
                    return ['status' => FALSE, 'message' => $e->getMessage()];
                }

                if(!empty($token_decode) AND is_object($token_decode))
                {
                    // Check Token API Time [API_TIME]
                    if (empty($token_decode->API_TIME OR !is_numeric($token_decode->API_TIME))) {                        
                        return ['status' => FALSE, 'message' => 'Token Time Not Define!'];
                    } else {
                        /**
                         * Check Token Time Valid 
                         */
                        $time_difference = strtotime('now') - $token_decode->API_TIME;
                        if ( $time_difference >= $this->token_expire_time )
                        {
                            return ['status' => FALSE, 'message' => 'Token Time Expire.'];

                        } else {
                            /**
                             * All Validation False Return Data
                             */
                            return ['status' => TRUE, 'data' => $token_decode];
                        }
                    }                    
                } else {
                    return ['status' => FALSE, 'message' => 'Forbidden'];
                }
            } catch(Exception $e) {
                return ['status' => FALSE, 'message' => $e->getMessage()];
            }
        } else {
            // Authorization Header Not Found!
            return ['status' => FALSE, 'message' => $token_data['message'] ];
        }
    }

    /**
     * Token Header Check
     * @param: request headers
     */
    private function tokenIsExist($headers)
    {
        if (!empty($headers) AND is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                if (strtolower(trim($header_name)) == strtolower(trim($this->token_header))) {
                    return ['status' => TRUE, 'token' => $header_value];
                }
            }
        }
        return ['status' => FALSE, 'message' => 'Token is not defined.'];
    }

    private function token()
    {        
        $headers = $this->input->request_headers();
        if (!empty($headers) AND is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                if (strtolower(trim($header_name)) == strtolower(trim($this->token_header))) {
                    return $header_value;
                }
            }
        }
        return 'Token is not defined.';
    }

    public function get_token()
    {
        $token = $this->token();
        $this->db->where('token', $token);
        $user = $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->row();
        if (isset($user)) {
            return true;
        }

        return false;
    }

    public function check_token()
    {
        $token = $this->token();
        $this->db->where('token', $token);
        $user = $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->row();
        if (isset($user)) {
            return true;
        }

        return false;
    }

    public function check_token_permission($feature = '', $capability = '')
    {
        $token = $this->token();
        $this->db->where('token', $token);
        $user = $this->db->get(db_prefix() . $this->config->item('rest_keys_table'))->row();
        if (isset($user)) {
            if ($user->permission_enable) {
                $this->db->where('key_id', $user->id);
                $this->db->where('feature', $feature);
                $this->db->where('capability', $capability);
                $permission = $this->db->get(db_prefix() . $this->config->item('rest_key_permissions_table'))->row();
    
                if (isset($permission)) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }
}