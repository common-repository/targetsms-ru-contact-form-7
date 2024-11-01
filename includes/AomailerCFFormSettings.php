<?php
class AomailerCFFormSettings
{
	private $form_id;
	private $error_messages;
	
	public $admin_enable;
	public $admin_message;
	public $admin_number;
	public $admin_used_translit;
	public $admin_from_name;
	
	public $client_enable;
	public $client_message;
	public $client_number;
	public $client_used_translit;
	public $client_from_name;
	
	/**
     * tableName()
     */
    public function tableName()
    {
        return 'aomailer_cf_form_settings';
    }
	
	/**
	 * rules() 
	 */
	public function rules() 
	{	
		
		$rules = 
		[
			['admin_enable, client_enable, admin_used_translit, client_used_translit', 'BooleanHtml'],
			
			['admin_message, client_message', 'function', 'cleaningTextSms'],
			
			['admin_from_name, client_from_name', 'Pattern', '/[a-z 0-9 \%\/]/i'],
			
			//~ ['admin_from_name, client_from_name', 'function', 'patternCheck'],
			
			['admin_number', 'function', 'cleaningNumberArray'],
			
			['client_number', 'safe'],
	
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
			$this->resetProperties($rules);
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
								if ($method == 'validateBooleanHtml') {
									$this->$key = 1;
								} else {
									$this->$key = $value;
								}
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
					`form_id` int(11) NOT NULL,
					`form_settings` LONGTEXT NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY (`form_id`)
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
	 * insert()
	 */
	 
	 // потом переделать на циклы.
	public function insert()
	{
		if (empty($this->form_id)) {
			return false;
		} else {
			
			$data = self::getDataProperties();
			
			if (!empty($data['admin_number'])) {
				foreach ($data['admin_number'] as $key => $value) {
					$data['admin_number'][$key] = serialize($value); // phone mask 919 000 99 99
				}
			}
			if (!empty($data['client_number'])) {
				foreach ($data['client_number'] as $key => $value) {
					$data['client_number'][$key]= serialize($value); // phone slug wpcf7 [tel-123]
					break; // only one number for user
				}
			}

			if (empty($data)) {
				return false;
			}
			
			if (!self::insert_table(['form_id'=>$this->form_id, 'form_settings'=>serialize($data)])) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * update()
	 */
	
	public function update()
	{
		if (empty($this->form_id)) {
			return false;
		} else {
			
			$data = self::getDataProperties();
			
			if (!empty($data['admin_number'])) {
				foreach ($data['admin_number'] as $key => $value) {
					$data['admin_number'][$key] = serialize($value); // phone mask 919 000 99 99
				}
			}
			if (!empty($data['client_number'])) {
				foreach ($data['client_number'] as $key => $value) {
					$data['client_number'][$key]= serialize($value); // phone slug wpcf7 [tel-123]
					break; // only one number for user
				}
			}
			
			if (!empty($data)) {
				$data = serialize($data);
			}
			
			if (!self::update_table(['form_settings'=>$data], ['form_id'=>$this->form_id])) {
				return false;
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
			return false;
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

			$result = $wpdb->get_results($sql, 'ARRAY_A');
			if (!empty($result)) {
				return $result[0];
			}
		}
		
		return false;
	}

	public function loadFormSettings($condition=[])
	{
		$db_data = self::aomp()->select_option($condition);
		if (!empty($db_data)) {
			foreach($db_data as $column => $value) {
				if (property_exists($this, $column)) {
					$this->$column = $value;
				} 
				if($column == 'form_settings') {
					if (!empty($value)) {
						foreach(@unserialize($value) as $k => $v) {
							if(($k == 'admin_number' || $k == 'client_number') && is_array($v)) {
								if ($k == 'admin_number') {
									foreach ($v as $i => $number) {
										$this->admin_number[$i] = @unserialize($number); 
									}
								}
								if ($k == 'client_number') {
									foreach ($v as $i => $number) {
										$this->client_number[$i] = @unserialize($number); 
										break; // only one number for user
									}
								}
							} else {
								$this->$k = $v;
							}
					    }
					}
				}
			}
		}
		return $this;
	}
	
	
	/**
	 * addError($error)
	 */
	private function addError($error='')
	{
		if (!empty($error)) {

			if (empty($this->error_messages['error'])) {
				
				$this->error_messages['error'] = $error.'<br>';

			} else {
		
				if (!preg_match('/('.$error.')/i', $this->error_messages['error'])) {
					$this->error_messages['error'] .= $error.'<br>';
				}
			}
		}
		
		return $this->error_messages['error'];
	}
	
	/**
	 * cleaningTextSms()
	 */
	private function cleaningTextSms($str='')
	{
		if (empty($str) || !is_string($str)) {
			return '';
		}
		return sanitize_text_field($str);
	}
	
	/**
	 * cleaningNumberArray($array=[])
	 */
	private function cleaningNumberArray($array=[])
	{
		$data = [];
		if (empty($array) || !is_array($array)) {
			return $data;
		}
		
		foreach ($array as $number) {
			if (empty($number)) {
				continue;
			}
				
			if (preg_match('/^[0-9 \+\-\(\)]{1,}$/i', $number)) {
				
				$number = preg_replace('/[\+\-\(\)\s]/i', '', $number);
				if (strlen($number)>15 || strlen($number)<9) {
					continue;
				}

			} elseif (preg_match('/^[0-9a-z \+\-\(\)]{1,}$/i', $number)) {	
				
				$number = preg_replace('/[\+\-\(\)\s]/i', '', $number);
				if (strlen($number)>11 || strlen($number)<5) {
					continue;
				}
				
			} else {
				continue;
			}
	
			$data[] = $number;
		}


		return $data;
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
	
	public function set_form_id($id) {
		if (AomailerCFValidate::validateInteger($id)) {
			$this->form_id = (int)$id;
		}
	}
	
	public function getErrors() {
		return $this->error_messages;
	}
	
	private function resetProperties($rules) {
		$properties = [];
		$i = count($rules);
		foreach($rules as $rule) {
			$rule[0] = preg_replace('/[\s\r\n]/i', '', $rule[0]);	
			if ($i > 1) {
				$properties[0] .= $rule[0] . ',';
				$i--;
			} else {
				$properties[0] .= $rule[0];
			}
		}
		$properties = explode(',', $properties[0]);
		foreach($properties as $key) {
			$this->$key = false;
		}
		unset($properties);
	}
	
	private function getDataProperties() {
		$rules = self::rules();
		if (empty($rules)) {
			return false;
		}
		$properties = "";
		$i = count($rules);
		foreach($rules as $rule) {
			$rule[0] = preg_replace('/[\s\r\n]/i', '', $rule[0]);	
			if ($i > 1) {
				$properties .= $rule[0] . ',';
				$i--;
			} else {
				$properties .= $rule[0];
			}
		}
		$properties = explode(',', $properties);
		$data = [];
		foreach($properties as $key) {
			if (!empty($this->$key)) {
				$data[$key] = $this->$key;
			}
		}
		unset($properties);
		return $data;
	}
	
	public function is_new() {
		if (empty($this->form_id)) {
			return true;
		} else {
			return false;
		}
	}
	
		/**
	 * patternCheck()
	 */
	private function patternCheck($str = '')
	{
		if (empty($str) || !is_string($str)) {
			return false;
		}
		
		if (preg_match('/[a-z 0-9 \%\/]/i', $str)) {
			return $str;
		} else {
			return false;
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
