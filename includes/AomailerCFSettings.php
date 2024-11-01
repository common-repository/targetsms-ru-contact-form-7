<?php
class AomailerCFSettings
{
	/**
	 * Settings attributes
	 */
	public $login;
	public $passwd;
	public $from_name;
			
	/**
     * tableName()
     */
    public function tableName()
    {
        return 'aomailer_cf_settings';
    }
	
	/**
	 * rules() 
	 */
	public function rules() 
	{	
		$rules = 
		[

			['login, passwd, from_name', 'Pattern', '/[a-z 0-9 \%\/]/i'],
	
		];
		
		return $rules;
	}
	
	/**
	 * attributes($array=[]) 
	 */
	public function attributes($array=[]) 
	{
		if (!empty($array)) {
			
			$rules = self::rules();
			if (empty($rules) || !is_array($rules)) {
				return false;
			}

			foreach ($array as $key=>$value) {
				
				if (empty($key) || !is_string($key) || !property_exists($this, $key)) {
					continue;
				}

				foreach ($rules as $rule) {

					if (empty($rule[0])) {
						continue;
					}

					$rule[0] = preg_replace('/[\s\r\n]/i', '', $rule[0]);
					$properties = explode(',', $rule[0]);
					
					if (
						empty($properties) || 
						!is_array($properties) || 
						empty($rule[1]) ||
						!is_string($rule[1])
					) {
						continue;
					}

					if (in_array($key, $properties)) {
						if ($rule[1] === 'safe') {
							$this->$key = $value;
						} else if (
							$rule[1]==='function' && 
							!empty($rule[2]) && 
							is_string($rule[2]) && 
							method_exists($this, $rule[2])
						) {
							$method = $rule[2];
							$this->$key = $this->$method($value);
						} elseif (method_exists('AomailerCFValidate', 'validate'.$rule[1])) {
							$method = 'validate'.$rule[1];
							$param = '';
							if (!empty($rule[2]) && is_string($rule[2])) {
								$param = $rule[2];
							}
							
							if (AomailerCFValidate::$method($value, $param)) {
								$this->$key = $value;
							} else {
								$this->$key = false;
							}
						}
						break;
					}
				}
			}
		}
	}
	
	/**
	 * create_table()
	 */
	public function create_table()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();

		if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		   
			$sql = "
				CREATE TABLE IF NOT EXISTS ".$table_name." (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`param_name` VARCHAR(255) NOT NULL,
					`param_value` LONGTEXT NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY (`param_name`)
				)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
			";
		   
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta($sql);
		}
	}
	
	/**
	 * delete_table()
	 */
	public function delete_table()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();

		if ($wpdb->get_var( "show tables like '$table_name'") == $table_name) {
			$result = $wpdb->query('DROP TABLE IF EXISTS '.$table_name);
			if (!empty($result)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * add_data()
	 */
	public function add_data()
	{
		foreach ($this as $key => $value) {
			
			if (!isset($value)) {
				continue;
			}

			$option = self::select_option(['param_name'=>$key]);
			if (!empty($option)) {
				if (!self::update_table(['param_name'=>$key, 'param_value'=>$value], ['param_name'=>$key])) {
					return false;
				}
			} else {
				if (!self::insert_table(['param_name'=>$key, 'param_value'=>$value])) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * update_table()
	 */
	public function update_table($data=[], $condition=[])
	{
		if (empty($data) || !is_array($data)) {
			return false;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();
		
		foreach ($data as $key=>$value) {
			if ($value==='false') {
				$value = 0;
			}
			
			if ($value==='true') {
				$value = 1;
			}

			if (is_string($value)) {
				$params[$key] = wp_unslash($value);
			} else {
				$params[$key] = $value;
			}
		}

		$wpdb->update($table_name, $params, $condition);
		
		if ($wpdb->last_error) {
			return false;  
		} else {
			return true;
		}
	}
	
	/**
	 * insert_table()
	 */
	public function insert_table($data=[])
	{
		if (empty($data) || !is_array($data)) {
			return false;
		}
		
		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();

		foreach ($data as $key=>$value) {
			if ($value==='false') {
				$value = 0;
			}
			
			if ($value==='true') {
				$value = 1;
			}
			
			if (is_string($value)) {
				$params[$key] = wp_unslash($value);
			} else {
				$params[$key] = $value;
			}
		}
			
		$wpdb->insert($table_name, $params);
		
		if ($wpdb->last_error) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * trincate_table()
	 */
	public function trincate_table()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();
		
		if ($wpdb->get_var( "show tables like '$table_name'") == $table_name) {
			$result = $wpdb->query('TRUNCATE TABLE '.$table_name);
			if (!empty($result)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * select_all()
	 */
	public function select_all()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();
		
		if ($wpdb->get_var( "show tables like '$table_name'") == $table_name) {
			$result = $wpdb->get_results('SELECT * FROM '.$table_name);
			if (!empty($result)) {
				return $result;
			}
		}
		
		return false;
	}
	
	/**
	 * select_option()
	 */
	public function select_option($condition=[])
	{
		if (empty($condition)) {
			return self::select_all();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();
		
		if ($wpdb->get_var( "show tables like '$table_name'") == $table_name) {
			
			$i=0;
			$sql = 'SELECT * FROM `'.$table_name.'` WHERE ';
			foreach ($condition as $key=>$value) {
				if (empty($i)) {
					$sql .= '
						'.$key.' = "'.$value.'"
					';
				} else {
					$sql .= '
						AND '.$key.' = "'.$value.'"
					';
				}
				$i++;
			}

			$result = $wpdb->get_results($sql);
			if (!empty($result)) {
				return $result;
			}
		}
		
		return false;
	}
	
	/**
	 * loadSettings()
	 */
	public function loadSettings($type='sms')
	{
		$config_path = realpath(AOMP_AOMAILER_CF_DIR) . '/config.php';
		if (file_exists($config_path)) {
			$settings= require($config_path);
		}
		
		$settings['login'] = '';
		$settings['passwd'] = '';
		
		$settings['logo'] = AOMP_AOMAILER_CF_URL . 'assets/img/logo.png';

		$db_data = self::aomp()->select_all();
		
		if (!empty($db_data)) {
			foreach ($db_data as $obj) {
				$settings[$obj->param_name] = $obj->param_value;
			}
		}
		

		if (!empty($settings['login']) && !empty($settings['passwd']) && $type=='sms') {

			$balance = AomailerCFSMSApi::aomp()->getBalance($settings['login'], $settings['passwd']);
			if (empty($balance['error'])) {
				$settings['balance'] = $balance['balance'];
				$settings['connect'] = true;
				$settings['currency'] = $balance['currency'];
			} else {
				$settings['error'] = self::addError($balance['error']);
			}

			$from_name = AomailerCFSMSApi::aomp()->getFromName($settings['login'], $settings['passwd']);
			if (empty($from_name['error'])) {
				$settings['array_from_name'] = $from_name['from_name'];
			} else {
				$settings['error'] = self::addError($from_name['error']);
			}
			
		}
		
		return $settings;
	}
	
	/**
	 * addError($error)
	 */
	private function addError($error='')
	{
		if (!empty($error)) {

			if (empty($this->settings['error'])) {
				
				$this->settings['error'] = $error.'. ';

			} else {
		
				if (!preg_match('/('.$error.')/i', $this->settings['error'])) {
					$this->settings['error'] .= $error.'. ';
				}
			}
		}
		
		return $this->settings['error'];
	}


	public function delete_option($data=[]){
		
		if (empty($data) || !is_array($data)) {
			return false;
		}
		
		global $wpdb;
		$table_name = $wpdb->prefix . $this->tableName();

		foreach ($data as $key=>$value) {
			
			if ($value==='false') {
				$value = 0;
			}
			
			if ($value==='true') {
				$value = 1;
			}
			
			if (is_string($value)) {
				$params[$key] = wp_unsplash($value);
			} else {
				$params[$key] = $value;
			}
		}
			
		$wpdb->insert($table_name, $params);
		
		if ($wpdb->last_error) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * aomp($className=__CLASS__)
	 */ 
	public static function aomp($className=__CLASS__)
	{
		return new $className;
	}
}
